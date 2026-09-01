<?php

namespace App\Http\Requests;

use App\Contracts\TestCatalogInterface;
use App\Services\Testing\KnownTestSuites;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class TestRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('case') && $this->filled('class') && $this->filled('method')) {
            $this->merge([
                'case' => $this->string('class')->toString().'::'.$this->string('method')->toString(),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'suite' => ['required_without_all:case,class', 'nullable', 'string', Rule::in(KnownTestSuites::names())],
            'case' => ['required_without_all:suite,class', 'nullable', 'string', 'max:255', $this->knownCase()],
            'class' => ['required_with:method', 'nullable', 'string', 'max:255'],
            'method' => ['required_with:class', 'nullable', 'string', 'max:128'],
        ];
    }

    private function knownCase(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (! is_string($value) || $value === '') {
                return;
            }

            if (! $this->container->make(TestCatalogInterface::class)->contains($value)) {
                $fail('unknown case');
            }
        };
    }
}
