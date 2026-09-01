<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StubIssueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'request_id' => ['required', 'string', 'max:64'],
            'sku' => ['required', 'string', 'max:64'],
            'order_id' => ['required', 'string', 'max:40'],
        ];
    }
}
