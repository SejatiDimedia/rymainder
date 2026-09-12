<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomReminderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'channels' => ['required', 'array', 'min:1'],
            'channels.*' => ['required', 'string', 'in:email,whatsapp,telegram'],
            'target_type' => ['required', 'string', 'in:all_active,overdue,selected'],
            'sponsor_ids' => ['required_if:target_type,selected', 'array'],
            'sponsor_ids.*' => ['integer', 'exists:sponsors,id'],
            'schedule_type' => ['required', 'string', 'in:daily,multiple_daily,interval_hours,weekly,once'],
            'schedule_time' => ['nullable', 'string', 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'schedule_times' => ['nullable', 'array'],
            'schedule_times.*' => ['nullable', 'string', 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'interval_hours' => ['nullable', 'integer', 'min:1', 'max:168'],
            'schedule_day' => ['nullable', 'integer', 'between:1,7'],
            'scheduled_at' => ['nullable', 'date'],
            'is_active' => ['nullable'],
        ];
    }
}
