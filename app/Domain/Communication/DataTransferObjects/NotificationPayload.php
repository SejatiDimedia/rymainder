<?php

namespace App\Domain\Communication\DataTransferObjects;

use App\Domain\Sponsor\Models\Sponsor;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class NotificationPayload
{
    public function __construct(
        public readonly string $sponsorName,
        public readonly ?string $orphanName,
        public readonly CarbonInterface $dueDate,
        public readonly float $amount,
        public readonly string $formattedAmount,
        public readonly string $waveLabel,
        public readonly int $daysDifference,
        public readonly string $paymentInstructions,
        public readonly string $subject,
        public readonly string $messageBody,
    ) {
    }

    public static function make(
        Sponsor $sponsor,
        CarbonInterface $dueDate,
        string $waveLabel,
        int $daysDifference
    ): self {
        $formattedDate = Carbon::parse($dueDate)->translatedFormat('d F Y');
        $formattedAmount = 'Rp ' . number_format($sponsor->amount, 0, ',', '.');
        $orphanText = $sponsor->orphan_name ? " untuk anak asuh tercinta {$sponsor->orphan_name}" : "";

        if ($daysDifference > 0) {
            $timeStatus = "akan jatuh tempo dalam {$daysDifference} hari ke depan (pada tanggal {$formattedDate})";
            $headline = "Pengingat Jadwal Donasi Sponsor";
        } elseif ($daysDifference === 0) {
            $timeStatus = "jatuh tempo pada hari ini ({$formattedDate})";
            $headline = "Pengingat Hari-H Jadwal Donasi Sponsor";
        } else {
            $absDays = abs($daysDifference);
            $timeStatus = "telah melewati jatuh tempo sejak {$absDays} hari yang lalu ({$formattedDate})";
            $headline = "Pemberitahuan Keterlambatan Donasi Sponsor";
        }

        $subject = "[{$headline}] Komitmen Donasi - {$sponsor->name}";

        $messageBody = "Assalamu'alaikum Wr. Wb. / Salam Sejahtera,\n\n"
            . "Yth. Bpk/Ibu {$sponsor->name},\n\n"
            . "Semoga Bpk/Ibu senantiasa dalam keadaan sehat dan penuh berkah. "
            . "Kami dari pengurus yayasan ingin menginformasikan bahwa komitmen donasi sponsor rutin{$orphanText} "
            . "sebesar *{$formattedAmount}* {$timeStatus}.\n\n"
            . "Pembayaran dapat disalurkan melalui rekening resmi yayasan:\n"
            . "• Bank Syariah Indonesia (BSI): 123-456-7890 a.n. Yayasan Peduli Anak\n"
            . "• Bank Mandiri: 987-654-3210 a.n. Yayasan Peduli Anak\n\n"
            . "Setelah melakukan transfer, mohon konfirmasi bukti transfer melalui nomor ini atau email kami.\n\n"
            . "Jazakumullah khairan katsiran atas ketulusan dan kepedulian Bpk/Ibu dalam mendukung masa depan anak-anak asuh kami.\n\n"
            . "Salam hangat,\n"
            . "Pengurus Yayasan";

        return new self(
            sponsorName: $sponsor->name,
            orphanName: $sponsor->orphan_name,
            dueDate: $dueDate,
            amount: (float) $sponsor->amount,
            formattedAmount: $formattedAmount,
            waveLabel: $waveLabel,
            daysDifference: $daysDifference,
            paymentInstructions: "Bank BSI: 123-456-7890 / Mandiri: 987-654-3210 a.n. Yayasan Peduli Anak",
            subject: $subject,
            messageBody: $messageBody,
        );
    }
}
