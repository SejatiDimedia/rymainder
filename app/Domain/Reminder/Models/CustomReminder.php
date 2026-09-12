<?php

namespace App\Domain\Reminder\Models;

use App\Domain\Reminder\Enums\ReminderChannel;
use App\Domain\Sponsor\Enums\SponsorStatus;
use App\Domain\Sponsor\Models\Sponsor;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'channels',
        'target_type',
        'schedule_type',
        'schedule_times',
        'interval_hours',
        'schedule_day',
        'scheduled_at',
        'is_active',
        'last_run_at',
        'next_run_at',
        'total_sent_count',
    ];

    protected $casts = [
        'channels' => 'array',
        'schedule_times' => 'array',
        'is_active' => 'boolean',
        'interval_hours' => 'integer',
        'schedule_day' => 'integer',
        'scheduled_at' => 'datetime',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'total_sent_count' => 'integer',
    ];

    public function sponsors(): BelongsToMany
    {
        return $this->belongsToMany(Sponsor::class, 'custom_reminder_sponsor');
    }

    public function reminderLogs(): HasMany
    {
        return $this->hasMany(ReminderLog::class);
    }

    public function hasChannel(ReminderChannel $channel): bool
    {
        return in_array($channel->value, $this->channels ?? [], true);
    }

    public function getChannelEnums(): array
    {
        return array_values(array_filter(
            array_map(fn ($ch) => ReminderChannel::tryFrom($ch), $this->channels ?? []),
            fn ($enum) => $enum !== null
        ));
    }

    public static function daysOfWeek(): array
    {
        return [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
        ];
    }

    public function scheduleLabel(): string
    {
        return match ($this->schedule_type) {
            'daily' => 'Daily at ' . ($this->schedule_times[0] ?? '12:00') . ' WIB',
            'multiple_daily' => count($this->schedule_times ?? []) . 'x Daily (' . implode(', ', $this->schedule_times ?? []) . ' WIB)',
            'interval_hours' => "Every {$this->interval_hours} Hours",
            'weekly' => 'Weekly on ' . (static::daysOfWeek()[$this->schedule_day] ?? 'Monday') . ' at ' . ($this->schedule_times[0] ?? '12:00') . ' WIB',
            'once' => 'Once at ' . ($this->scheduled_at ? $this->scheduled_at->format('d M Y H:i') . ' WIB' : '-'),
            default => ucfirst($this->schedule_type ?? 'Daily'),
        };
    }

    public function targetLabel(): string
    {
        return match ($this->target_type) {
            'all_active' => 'All Active Donors',
            'overdue' => 'Overdue Donors Only',
            'selected' => 'Selected Donors (' . $this->sponsors()->count() . ')',
            default => 'All Active Donors',
        };
    }

    /**
     * Resolve target sponsors based on audience settings.
     *
     * @return Collection<int, Sponsor>
     */
    public function getTargetSponsors(): Collection
    {
        return match ($this->target_type) {
            'selected' => $this->sponsors()->where('status', SponsorStatus::ACTIVE)->get(),
            'overdue' => Sponsor::where('status', SponsorStatus::ACTIVE)
                ->get()
                ->filter(fn (Sponsor $s) => $s->isOverdue())
                ->values(),
            default => Sponsor::where('status', SponsorStatus::ACTIVE)->get(),
        };
    }

    /**
     * Calculate the next execution time for this reminder.
     */
    public function computeNextRunAt(?Carbon $now = null): ?Carbon
    {
        $now = $now ? $now->copy() : Carbon::now(config('app.timezone', 'Asia/Jakarta'));

        return match ($this->schedule_type) {
            'daily' => $this->computeNextDailyRun($now),
            'multiple_daily' => $this->computeNextMultipleDailyRun($now),
            'interval_hours' => $now->copy()->addHours($this->interval_hours ?: 2),
            'weekly' => $this->computeNextWeeklyRun($now),
            'once' => ($this->last_run_at !== null) ? null : ($this->scheduled_at ?: $now),
            default => $now->copy()->addDay(),
        };
    }

    private function computeNextDailyRun(Carbon $now): Carbon
    {
        $timeStr = $this->schedule_times[0] ?? '12:00';
        [$hour, $minute] = explode(':', $timeStr);

        $targetToday = $now->copy()->setTime((int) $hour, (int) $minute, 0);

        return $targetToday->isFuture()
            ? $targetToday
            : $targetToday->addDay();
    }

    private function computeNextMultipleDailyRun(Carbon $now): Carbon
    {
        $times = $this->schedule_times ?? ['08:00', '13:00', '18:00'];
        sort($times);

        foreach ($times as $timeStr) {
            [$hour, $minute] = explode(':', $timeStr);
            $candidate = $now->copy()->setTime((int) $hour, (int) $minute, 0);
            if ($candidate->isFuture()) {
                return $candidate;
            }
        }

        // None left today, pick the first time tomorrow
        [$firstHour, $firstMinute] = explode(':', $times[0]);
        return $now->copy()->addDay()->setTime((int) $firstHour, (int) $firstMinute, 0);
    }

    private function computeNextWeeklyRun(Carbon $now): Carbon
    {
        $dayNum = $this->schedule_day ?: 1; // 1 = Mon to 7 = Sun
        $timeStr = $this->schedule_times[0] ?? '12:00';
        [$hour, $minute] = explode(':', $timeStr);

        $next = $now->copy()->next($dayNum)->setTime((int) $hour, (int) $minute, 0);

        // If today is that day and the time is still in the future
        if ($now->dayOfWeekIso === $dayNum) {
            $todayCandidate = $now->copy()->setTime((int) $hour, (int) $minute, 0);
            if ($todayCandidate->isFuture()) {
                return $todayCandidate;
            }
        }

        return $next;
    }
}
