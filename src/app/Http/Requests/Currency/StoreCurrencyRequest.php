<?php

namespace App\Http\Requests\Currency;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('currencies', 'name'),
            ],
            'symbol' => [
                'required',
                'string',
                'max:10',
            ],
            'exchange_rate' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,6',
            ],
        ];
    }
}
