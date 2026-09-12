<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReminderSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'days_before_due' => ['required', 'integer'],
            'channels' => ['required', 'array', 'min:1'],
            'channels.*' => ['string', 'in:email,whatsapp,telegram,sms'],
            'is_active' => ['boolean'],
            'schedule_frequency' => ['sometimes', 'string', 'in:daily,weekly'],
            'schedule_day' => ['nullable', 'integer', 'min:1', 'max:7'],
            'dispatch_time' => ['sometimes', 'string', 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'message_template' => ['nullable', 'string', 'max:5000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'schedule_frequency' => $this->input('schedule_frequency', 'daily') ?: 'daily',
            'dispatch_time' => $this->input('dispatch_time', '07:00') ?: '07:00',
        ]);
    }
}
