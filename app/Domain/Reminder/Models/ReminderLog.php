<?php

namespace App\Domain\Reminder\Models;

use App\Domain\Reminder\Enums\DeliveryStatus;
use App\Domain\Reminder\Enums\ReminderChannel;
use App\Domain\Sponsor\Models\Sponsor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'sponsor_id',
        'due_date',
        'reminder_setting_id',
        'channel',
        'is_manual',
        'status',
        'error_message',
        'custom_message',
        'sent_at',
    ];

    protected $casts = [
        'due_date' => 'date:Y-m-d',
        'channel' => ReminderChannel::class,
        'status' => DeliveryStatus::class,
        'is_manual' => 'boolean',
        'sent_at' => 'datetime',
    ];

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(Sponsor::class);
    }

    public function reminderSetting(): BelongsTo
    {
        return $this->belongsTo(ReminderSetting::class);
    }
}
