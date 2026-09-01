<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CatalogIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'limit' => ['sometimes', 'integer', 'min:1', 'max:'.(int) config('catalog.storefront_max', 100)],
        ];
    }

    public function limit(): int
    {
        return (int) ($this->validated('limit') ?? config('catalog.storefront_limit', 50));
    }
}
