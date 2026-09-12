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
            'message_template' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
