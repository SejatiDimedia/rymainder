<?php

namespace Database\Seeders;

use App\Domain\Admin\Enums\UserRole;
use App\Domain\Reminder\Models\ReminderSetting;
use App\Domain\Sponsor\Enums\PaymentFrequency;
use App\Domain\Sponsor\Enums\SponsorStatus;
use App\Domain\Sponsor\Models\Sponsor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Users
        User::firstOrCreate(
            ['email' => 'admin@yayasan.org'],
            [
                'name' => 'Super Admin Yayasan',
                'password' => Hash::make('password'),
                'role' => UserRole::SUPER_ADMIN,
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'staf@yayasan.org'],
            [
                'name' => 'Staf Program',
                'password' => Hash::make('password'),
                'role' => UserRole::STAFF,
                'email_verified_at' => now(),
            ]
        );

        // 2. Default Reminder Waves from PRD
        $waves = [
            [
                'label' => 'Reminder Pertama (H-7)',
                'days_before_due' => 7,
                'channels' => ['email', 'telegram'],
                'is_active' => true,
            ],
            [
                'label' => 'Reminder Kedua (H-3)',
                'days_before_due' => 3,
                'channels' => ['email', 'whatsapp', 'telegram'],
                'is_active' => true,
            ],
            [
                'label' => 'Reminder Hari-H (H-0)',
                'days_before_due' => 0,
                'channels' => ['email', 'whatsapp', 'telegram'],
                'is_active' => true,
            ],
            [
                'label' => 'Follow-up Keterlambatan (H+7 Overdue)',
                'days_before_due' => -7,
                'channels' => ['email', 'whatsapp', 'telegram'],
                'is_active' => true,
            ],
        ];

        foreach ($waves as $wave) {
            ReminderSetting::firstOrCreate(
                ['days_before_due' => $wave['days_before_due']],
                $wave
            );
        }

        // 3. Demo Sponsors
        $today = now();

        $demoSponsors = [
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@example.com',
                'phone' => '+6281211112222',
                'telegram_chat_id' => '987654321',
                'telegram_onboard_code' => 'AHMAD1234567',
                'orphan_name' => 'Rizky Pratama',
                'last_donation_date' => $today->copy()->subYear()->addDays(20)->toDateString(),
                'frequency' => PaymentFrequency::ANNUAL,
                'amount' => 500000,
                'status' => SponsorStatus::ACTIVE,
                'notes' => 'Donatur rutin sejak 2022',
            ],
            [
                'name' => 'Budi Prasetyo',
                'email' => 'budi.prasetyo@example.com',
                'phone' => '+6281222223333',
                'telegram_chat_id' => null,
                'telegram_onboard_code' => 'BUDI12345678',
                'orphan_name' => 'Aisyah Putri',
                'last_donation_date' => $today->copy()->subYear()->addDays(7)->toDateString(), // Due in 7 days (H-7)
                'frequency' => PaymentFrequency::ANNUAL,
                'amount' => 600000,
                'status' => SponsorStatus::ACTIVE,
                'notes' => 'Jatuh tempo dalam 7 hari (Wave H-7)',
            ],
            [
                'name' => 'Dewi Sartika',
                'email' => 'dewi.sartika@example.com',
                'phone' => '+6281233334444',
                'telegram_chat_id' => '123456789',
                'telegram_onboard_code' => 'DEWI12345678',
                'orphan_name' => 'Muhammad Bilal',
                'last_donation_date' => $today->copy()->subMonths(6)->addDays(3)->toDateString(), // Due in 3 days (H-3)
                'frequency' => PaymentFrequency::SIX_MONTHS,
                'amount' => 350000,
                'status' => SponsorStatus::ACTIVE,
                'notes' => 'Jatuh tempo dalam 3 hari (Wave H-3)',
            ],
            [
                'name' => 'Eko Kurniawan',
                'email' => 'eko.kurniawan@example.com',
                'phone' => '+6281244445555',
                'telegram_chat_id' => null,
                'telegram_onboard_code' => 'EKO123456789',
                'orphan_name' => 'Nabila Zahra',
                'last_donation_date' => $today->copy()->subYear()->toDateString(), // Due TODAY (H-0)
                'frequency' => PaymentFrequency::ANNUAL,
                'amount' => 1000000,
                'status' => SponsorStatus::ACTIVE,
                'notes' => 'Jatuh tempo hari ini (Wave H-0)',
            ],
            [
                'name' => 'Farida Zahra',
                'email' => 'farida.zahra@example.com',
                'phone' => '+6281255556666',
                'telegram_chat_id' => '555666777',
                'telegram_onboard_code' => 'FARIDA123456',
                'orphan_name' => 'Fathan Al-Ghifari',
                'last_donation_date' => $today->copy()->subYear()->subDays(7)->toDateString(), // Overdue 7 days (H+7)
                'frequency' => PaymentFrequency::ANNUAL,
                'amount' => 450000,
                'status' => SponsorStatus::ACTIVE,
                'notes' => 'Terlambat 7 hari (Wave H+7 Overdue)',
            ],
            [
                'name' => 'Gunawan Wibowo',
                'email' => 'gunawan@example.com',
                'phone' => '+6281266667777',
                'orphan_name' => null,
                'last_donation_date' => $today->copy()->subYear()->toDateString(),
                'frequency' => PaymentFrequency::ANNUAL,
                'amount' => 500000,
                'status' => SponsorStatus::PAUSED,
                'notes' => 'Sedang cuti donasi sementara sampai bulan depan',
            ],
            [
                'name' => 'Haryono Sukamto',
                'email' => 'haryono@example.com',
                'phone' => '+6281277778888',
                'orphan_name' => null,
                'last_donation_date' => $today->copy()->subYears(2)->toDateString(),
                'frequency' => PaymentFrequency::ANNUAL,
                'amount' => 500000,
                'status' => SponsorStatus::CANCELLED,
                'notes' => 'Berhenti permanen',
            ],
        ];

        foreach ($demoSponsors as $data) {
            Sponsor::firstOrCreate(
                ['email' => $data['email']],
                $data
            );
        }
    }
}
