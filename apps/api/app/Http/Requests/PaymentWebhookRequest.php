<?php

namespace App\Http\Requests;

use App\Enums\Currency;
use App\Enums\PaymentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class PaymentWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_id' => ['required', 'string', 'max:64'],
            'order_id' => ['required', 'string', 'max:40'],
            'status' => ['required', 'string', Rule::enum(PaymentStatus::class)],
            'amount' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', Rule::enum(Currency::class)],
            'created_at' => ['required', 'date'],
        ];
    }
}
