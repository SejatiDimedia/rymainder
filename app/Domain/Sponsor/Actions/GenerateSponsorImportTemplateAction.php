<?php

namespace App\Domain\Sponsor\Actions;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GenerateSponsorImportTemplateAction
{
    /**
     * Generate and download sample template file.
     *
     * @param string $format 'xlsx' or 'csv'
     * @return BinaryFileResponse
     */
    public function execute(string $format = 'xlsx'): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sponsors Import Template');

        $headers = [
            'name',
            'email',
            'phone',
            'orphan_name',
            'amount',
            'frequency',
            'last_donation_date',
            'status',
            'telegram_chat_id',
            'channels',
            'notes',
        ];

        $sampleRows = [
            [
                'H. Ahmad Dahlan',
                'ahmad.dahlan@example.org',
                '081234567890',
                'Fatimah Zahra',
                500000,
                'annual',
                '2026-08-15',
                'active',
                '12345678',
                'email, whatsapp, telegram',
                'Annual pledge for orphan education',
            ],
            [
                'Hj. Siti Rahmah',
                'siti.rahmah@example.org',
                '+6281987654321',
                'Muhammad Yusuf',
                300000,
                '6_months',
                '2026-07-10',
                'active',
                '',
                'email, whatsapp',
                '6-month recurring sponsor',
            ],
        ];

        // 1. Write Header Row
        foreach ($headers as $colIndex => $header) {
            $colLetter = chr(65 + $colIndex); // A, B, C...
            $cell = "{$colLetter}1";
            $sheet->setCellValue($cell, $header);
        }

        // 2. Write Sample Rows
        foreach ($sampleRows as $rowIndex => $row) {
            $rowNumber = $rowIndex + 2;
            foreach ($row as $colIndex => $value) {
                $colLetter = chr(65 + $colIndex);
                $cell = "{$colLetter}{$rowNumber}";
                $sheet->setCellValueExplicit($cell, (string) $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }
        }

        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        if (strtolower($format) === 'csv') {
            $fileName = 'rymainder_sponsors_import_template.csv';
            $filePath = "{$tempDir}/{$fileName}";

            $writer = new Csv($spreadsheet);
            $writer->setDelimiter(',');
            $writer->setEnclosure('"');
            $writer->setLineEnding("\r\n");
            $writer->setSheetIndex(0);
            $writer->save($filePath);

            return response()->download($filePath, $fileName, [
                'Content-Type' => 'text/csv',
            ])->deleteFileAfterSend(true);
        }

        // Format XLSX nicely
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F172A'], // Slate 900
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '334155'],
                ],
            ],
        ];

        $sheet->getStyle('A1:K1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Auto-fit column widths
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'rymainder_sponsors_import_template.xlsx';
        $filePath = "{$tempDir}/{$fileName}";

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return response()->download($filePath, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
