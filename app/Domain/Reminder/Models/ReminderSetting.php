<?php

namespace App\Domain\Reminder\Models;

use App\Domain\Reminder\Enums\ReminderChannel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReminderSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'days_before_due',
        'channels',
        'label',
        'message_template',
        'is_active',
        'schedule_frequency',
        'schedule_day',
        'dispatch_time',
    ];

    protected $casts = [
        'days_before_due' => 'integer',
        'channels' => 'array',
        'is_active' => 'boolean',
        'schedule_day' => 'integer',
    ];

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
        $time = $this->dispatch_time ?: '07:00';
        if ($this->schedule_frequency === 'weekly') {
            $days = self::daysOfWeek();
            $dayName = $days[$this->schedule_day] ?? 'Monday';
            return "Every {$dayName} at {$time}";
        }

        return "Daily at {$time}";
    }

    public function reminderLogs(): HasMany
    {
        return $this->hasMany(ReminderLog::class);
    }

    /**
     * Check if this wave has a specific channel enabled.
     */
    public function hasChannel(ReminderChannel $channel): bool
    {
        return in_array($channel->value, $this->channels ?? [], true);
    }

    /**
     * Get array of ReminderChannel enum instances.
     *
     * @return array<ReminderChannel>
     */
    public function getChannelEnums(): array
    {
        return array_values(array_filter(
            array_map(fn ($ch) => ReminderChannel::tryFrom($ch), $this->channels ?? []),
            fn ($enum) => $enum !== null
        ));
    }
}
