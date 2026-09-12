<?php

namespace App\Domain\Sponsor\Models;

use App\Domain\Reminder\Enums\ReminderChannel;
use App\Domain\Reminder\Models\ReminderLog;
use App\Domain\Reminder\Services\DueDateCalculator;
use App\Domain\Sponsor\Enums\PaymentFrequency;
use App\Domain\Sponsor\Enums\SponsorStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Sponsor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'telegram_chat_id',
        'telegram_onboard_code',
        'orphan_name',
        'last_donation_date',
        'frequency',
        'amount',
        'status',
        'channel_preferences',
        'notes',
    ];

    protected $casts = [
        'last_donation_date' => 'date',
        'frequency' => PaymentFrequency::class,
        'status' => SponsorStatus::class,
        'amount' => 'decimal:2',
        'channel_preferences' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Sponsor $sponsor) {
            if (empty($sponsor->telegram_onboard_code)) {
                $sponsor->telegram_onboard_code = strtoupper(Str::random(12));
            }
            if (empty($sponsor->channel_preferences)) {
                $sponsor->channel_preferences = [
                    ReminderChannel::EMAIL->value,
                    ReminderChannel::WHATSAPP->value,
                    ReminderChannel::TELEGRAM->value,
                ];
            }
        });
    }

    public function reminderLogs(): HasMany
    {
        return $this->hasMany(ReminderLog::class)->orderByDesc('created_at');
    }

    public function customReminders(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Domain\Reminder\Models\CustomReminder::class, 'custom_reminder_sponsor');
    }

    public function isOverdue(?\Carbon\CarbonInterface $referenceDate = null): bool
    {
        return $this->getDueStatus($referenceDate) === 'overdue';
    }

    /**
     * Check if a specific channel is enabled for this sponsor.
     */
    public function isChannelEnabled(ReminderChannel $channel): bool
    {
        if (empty($this->channel_preferences)) {
            return true;
        }

        return in_array($channel->value, $this->channel_preferences, true);
    }

    /**
     * Calculate next due date using the decoupled DueDateCalculator.
     */
    public function getNextDueDate(?CarbonInterface $referenceDate = null): Carbon
    {
        $calculator = app(DueDateCalculator::class);
        return $calculator->calculateNextDueDate(
            $this->last_donation_date,
            $this->frequency,
            $referenceDate ?? now()
        );
    }

    /**
     * Determine due status: 'overdue', 'due_soon' (<= 7 days), 'normal' (> 7 days).
     */
    public function getDueStatus(?CarbonInterface $referenceDate = null): string
    {
        $today = ($referenceDate ? Carbon::parse($referenceDate) : now())->startOfDay();
        $nextDue = $this->getNextDueDate($today)->startOfDay();

        $daysDiff = $today->diffInDays($nextDue, false);

        if ($daysDiff < 0) {
            return 'overdue';
        }

        if ($daysDiff <= 7) {
            return 'due_soon';
        }

        return 'normal';
    }

    /**
     * Formatted amount in Indonesian Rupiah.
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Telegram onboarding deep-link URL.
     */
    public function getTelegramOnboardingUrl(): string
    {
        $botUsername = config('rymainder.channels.telegram.bot_username');
        if (empty($botUsername)) {
            return '#';
        }

        return "https://t.me/{$botUsername}?start={$this->telegram_onboard_code}";
    }

    public function hasConnectedTelegram(): bool
    {
        return ! empty($this->telegram_chat_id);
    }
}
