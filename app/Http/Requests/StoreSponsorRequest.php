<?php

namespace App\Http\Requests;

use App\Domain\Sponsor\Enums\PaymentFrequency;
use App\Domain\Sponsor\Enums\SponsorStatus;
use App\Domain\Sponsor\ValueObjects\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSponsorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge([
                'phone' => PhoneNumber::normalize($this->input('phone')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:sponsors,email'],
            'phone' => ['required', 'string', 'max:20', function ($attribute, $value, $fail) {
                if (! PhoneNumber::isValid($value)) {
                    $fail("Format nomor WhatsApp tidak valid. Gunakan format internasional (contoh: +6281234567890).");
                }
            }],
            'orphan_name' => ['nullable', 'string', 'max:255'],
            'last_donation_date' => ['required', 'date', 'before_or_equal:today'],
            'frequency' => ['required', Rule::enum(PaymentFrequency::class)],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::enum(SponsorStatus::class)],
            'channel_preferences' => ['nullable', 'array'],
            'channel_preferences.*' => ['string', 'in:email,whatsapp,telegram,sms'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama sponsor wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar untuk sponsor lain.',
            'phone.required' => 'Nomor telepon/WhatsApp wajib diisi.',
            'last_donation_date.required' => 'Tanggal donasi terakhir wajib diisi.',
            'last_donation_date.before_or_equal' => 'Tanggal donasi terakhir tidak boleh di masa depan.',
            'amount.required' => 'Nominal komitmen donasi wajib diisi.',
        ];
    }
}
