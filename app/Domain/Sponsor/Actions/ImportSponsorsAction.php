<?php

namespace App\Domain\Sponsor\Actions;

use App\Domain\Reminder\Enums\ReminderChannel;
use App\Domain\Sponsor\DataTransferObjects\ImportResult;
use App\Domain\Sponsor\Enums\PaymentFrequency;
use App\Domain\Sponsor\Enums\SponsorStatus;
use App\Domain\Sponsor\Models\Sponsor;
use App\Domain\Sponsor\ValueObjects\PhoneNumber;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportSponsorsAction
{
    /**
     * Import sponsors from an Excel (.xlsx, .xls) or CSV file.
     *
     * @param string|UploadedFile $file Path to file or UploadedFile instance
     * @param string $duplicateMode 'skip' or 'update'
     * @return ImportResult
     */
    public function execute(string|UploadedFile $file, string $duplicateMode = 'skip'): ImportResult
    {
        $filePath = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        if (! file_exists($filePath)) {
            return new ImportResult(errors: ['File could not be found or read on the server.']);
        }

        try {
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray(null, true, false, false);
        } catch (Exception $e) {
            Log::error('Failed to parse spreadsheet: ' . $e->getMessage());
            return new ImportResult(errors: ['Failed to read spreadsheet file: ' . $e->getMessage()]);
        }

        if (empty($rows) || count($rows) < 2) {
            return new ImportResult(errors: ['The uploaded file is empty or does not contain data rows beyond the header.']);
        }

        // 1. Resolve header mapping
        $headerRow = array_shift($rows);
        $headerMap = $this->resolveHeaderMap($headerRow);

        if (! isset($headerMap['name']) || ! isset($headerMap['email'])) {
            return new ImportResult(errors: [
                'Invalid file format: Required header columns ("name" and "email") were not found in the first row.',
            ]);
        }

        $importedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $errors = [];

        $rowNumber = 1; // 1-based, header was row 1
        foreach ($rows as $row) {
            $rowNumber++;

            // Skip completely empty rows
            if ($this->isRowEmpty($row)) {
                continue;
            }

            $extracted = $this->extractRowData($row, $headerMap);

            // Validation & Normalization
            $validationError = $this->validateRow($extracted, $rowNumber);
            if ($validationError !== null) {
                $errors[] = "Row {$rowNumber}: {$validationError}";
                continue;
            }

            try {
                $normalizedData = $this->normalizeRowData($extracted);

                $existingSponsor = Sponsor::where('email', $normalizedData['email'])->first();

                if ($existingSponsor) {
                    if ($duplicateMode === 'skip') {
                        $skippedCount++;
                        continue;
                    }

                    // Update existing
                    $existingSponsor->update($normalizedData);
                    $updatedCount++;
                } else {
                    Sponsor::create($normalizedData);
                    $importedCount++;
                }
            } catch (Exception $e) {
                Log::warning("Error importing sponsor at row {$rowNumber}: " . $e->getMessage());
                $errors[] = "Row {$rowNumber} ({$extracted['name']}): " . $e->getMessage();
            }
        }

        return new ImportResult(
            importedCount: $importedCount,
            updatedCount: $updatedCount,
            skippedCount: $skippedCount,
            errors: $errors,
        );
    }

    /**
     * Map header column names to canonical field keys.
     */
    protected function resolveHeaderMap(array $headerRow): array
    {
        $map = [];
        $aliases = [
            'name' => ['name', 'nama', 'nama_lengkap', 'sponsor_name', 'donor_name', 'full_name'],
            'email' => ['email', 'surel', 'email_address', 'e-mail'],
            'phone' => ['phone', 'telepon', 'no_hp', 'no_wa', 'whatsapp', 'phone_number', 'mobile', 'telp'],
            'orphan_name' => ['orphan_name', 'anak_asuh', 'nama_anak', 'orphan', 'beneficiary', 'child_name'],
            'amount' => ['amount', 'nominal', 'jumlah', 'donasi', 'commitment_amount', 'pledge', 'nominal_donasi'],
            'frequency' => ['frequency', 'frekuensi', 'periode', 'payment_frequency', 'cycle'],
            'last_donation_date' => ['last_donation_date', 'tanggal_donasi', 'tanggal_terakhir', 'donation_date', 'last_donation', 'tgl_donasi'],
            'status' => ['status', 'kondisi', 'sponsor_status'],
            'telegram_chat_id' => ['telegram_chat_id', 'chat_id', 'telegram', 'telegram_id', 'id_telegram'],
            'channels' => ['channels', 'channel_preferences', 'saluran', 'channel', 'preferensi_channel'],
            'notes' => ['notes', 'catatan', 'keterangan', 'note'],
        ];

        foreach ($headerRow as $index => $colName) {
            if ($colName === null) {
                continue;
            }
            $cleanName = strtolower(trim((string) $colName));
            $cleanName = str_replace([' ', '-', '.'], '_', $cleanName);

            foreach ($aliases as $canonicalKey => $variations) {
                if (in_array($cleanName, $variations, true)) {
                    $map[$canonicalKey] = $index;
                    break;
                }
            }
        }

        return $map;
    }

    protected function extractRowData(array $row, array $headerMap): array
    {
        $data = [];
        foreach ($headerMap as $key => $index) {
            $data[$key] = isset($row[$index]) ? trim((string) $row[$index]) : null;
        }

        return $data;
    }

    protected function isRowEmpty(array $row): bool
    {
        foreach ($row as $val) {
            if ($val !== null && trim((string) $val) !== '') {
                return false;
            }
        }
        return true;
    }

    protected function validateRow(array $data, int $rowNumber): ?string
    {
        if (empty($data['name'])) {
            return "Sponsor name is required.";
        }

        if (empty($data['email']) || ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return "A valid email address is required (got '{$data['email']}').";
        }

        if (empty($data['phone'])) {
            return "Phone number / WhatsApp is required.";
        }

        try {
            $normalizedPhone = PhoneNumber::normalize($data['phone']);
            if (! PhoneNumber::isValid($normalizedPhone)) {
                return "Invalid phone number format '{$data['phone']}'. Please use standard Indonesian (+62 / 08...) or international format.";
            }
        } catch (Exception $e) {
            return "Invalid phone number '{$data['phone']}': " . $e->getMessage();
        }

        if (empty($data['amount'])) {
            return "Commitment amount is required.";
        }

        $cleanedAmount = $this->cleanAmount($data['amount']);
        if ($cleanedAmount <= 0) {
            return "Commitment amount must be greater than zero (got '{$data['amount']}').";
        }

        if (empty($data['last_donation_date'])) {
            return "Last donation date is required.";
        }

        $parsedDate = $this->parseDate($data['last_donation_date']);
        if (! $parsedDate) {
            return "Invalid donation date '{$data['last_donation_date']}'. Accepted formats: YYYY-MM-DD, DD/MM/YYYY, or valid Excel date.";
        }

        if ($parsedDate->year < 1970 || $parsedDate->year > 2099) {
            $formatted = $parsedDate->toDateString();
            return "Donation date '{$formatted}' is outside the valid range (1970 - 2099).";
        }

        return null;
    }

    protected function normalizeRowData(array $data): array
    {
        $normalizedPhone = PhoneNumber::normalize($data['phone']);
        $amount = $this->cleanAmount($data['amount']);
        $date = $this->parseDate($data['last_donation_date'])->toDateString();
        $frequency = $this->parseFrequency($data['frequency'] ?? null);
        $status = $this->parseStatus($data['status'] ?? null);
        $channels = $this->parseChannels($data['channels'] ?? null);

        return [
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'phone' => $normalizedPhone,
            'orphan_name' => ! empty($data['orphan_name']) ? trim($data['orphan_name']) : null,
            'amount' => $amount,
            'frequency' => $frequency,
            'last_donation_date' => $date,
            'status' => $status,
            'telegram_chat_id' => ! empty($data['telegram_chat_id']) ? trim($data['telegram_chat_id']) : null,
            'channel_preferences' => $channels,
            'notes' => ! empty($data['notes']) ? trim($data['notes']) : null,
        ];
    }

    protected function cleanAmount(mixed $amount): float
    {
        if (is_numeric($amount)) {
            return (float) $amount;
        }

        $clean = preg_replace('/[^0-9.]/', '', str_replace(',', '.', (string) $amount));
        return (float) $clean;
    }

    protected function parseDate(mixed $dateVal): ?Carbon
    {
        if (empty($dateVal)) {
            return null;
        }

        // If Excel numeric date serial
        if (is_numeric($dateVal) && (float) $dateVal > 20000 && (float) $dateVal < 70000) {
            try {
                $dateTime = ExcelDate::excelToDateTimeObject((float) $dateVal);
                return Carbon::instance($dateTime);
            } catch (Exception $e) {
                // Fallback to string parsing
            }
        }

        $dateStr = trim((string) $dateVal);

        // Try standard formats: Y-m-d, d/m/Y, d-m-Y, Y/m/d
        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d', 'm/d/Y'];
        foreach ($formats as $format) {
            try {
                $d = Carbon::createFromFormat($format, $dateStr);
                if ($d && $d->format($format) === $dateStr) {
                    return $d;
                }
            } catch (Exception $e) {
                continue;
            }
        }

        try {
            return Carbon::parse($dateStr);
        } catch (Exception $e) {
            return null;
        }
    }

    protected function parseFrequency(?string $frequency): PaymentFrequency
    {
        if (empty($frequency)) {
            return PaymentFrequency::ANNUAL;
        }

        $clean = strtolower(trim($frequency));

        if (in_array($clean, ['6_months', '6 months', '6', 'semi_annual', 'semi-annual', '6 bulanan', 'enam bulan'], true)) {
            return PaymentFrequency::SIX_MONTHS;
        }

        return PaymentFrequency::ANNUAL;
    }

    protected function parseStatus(?string $status): SponsorStatus
    {
        if (empty($status)) {
            return SponsorStatus::ACTIVE;
        }

        $clean = strtolower(trim($status));

        return match ($clean) {
            'paused', 'nonaktif', 'jeda' => SponsorStatus::PAUSED,
            'cancelled', 'batal', 'non_aktif' => SponsorStatus::CANCELLED,
            default => SponsorStatus::ACTIVE,
        };
    }

    protected function parseChannels(?string $channels): array
    {
        if (empty($channels)) {
            return [
                ReminderChannel::EMAIL->value,
                ReminderChannel::WHATSAPP->value,
                ReminderChannel::TELEGRAM->value,
            ];
        }

        $items = array_map('trim', explode(',', strtolower($channels)));
        $valid = [
            ReminderChannel::EMAIL->value,
            ReminderChannel::WHATSAPP->value,
            ReminderChannel::TELEGRAM->value,
        ];

        $resolved = array_values(array_intersect($items, $valid));

        return ! empty($resolved) ? $resolved : $valid;
    }
}
