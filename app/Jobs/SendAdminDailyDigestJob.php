<?php

namespace App\Jobs;

use App\Domain\Reminder\Enums\DeliveryStatus;
use App\Domain\Reminder\Models\ReminderLog;
use App\Mail\AdminDailyDigestMail;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAdminDailyDigestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $adminEmail = config('rymainder.admin_digest_email');
        if (empty($adminEmail)) {
            Log::info("Admin digest skipped: ADMIN_DIGEST_EMAIL not configured.");
            return;
        }

        $today = now()->startOfDay();

        $sentCount = ReminderLog::where('status', DeliveryStatus::SENT)
            ->whereDate('updated_at', $today)
            ->count();

        $failedLogs = ReminderLog::with('sponsor')
            ->where('status', DeliveryStatus::FAILED)
            ->whereDate('updated_at', $today)
            ->get();

        $skippedCount = ReminderLog::where('status', DeliveryStatus::SKIPPED)
            ->whereDate('updated_at', $today)
            ->count();

        try {
            Mail::to($adminEmail)->send(new AdminDailyDigestMail(
                sentCount: $sentCount,
                failedCount: $failedLogs->count(),
                skippedCount: $skippedCount,
                failedLogs: $failedLogs
            ));

            Log::info("Admin daily digest successfully sent to {$adminEmail}.");
        } catch (Exception $e) {
            Log::error("Failed to send admin daily digest: " . $e->getMessage());
        }
    }
}
