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
        'is_active',
    ];

    protected $casts = [
        'days_before_due' => 'integer',
        'channels' => 'array',
        'is_active' => 'boolean',
    ];

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
