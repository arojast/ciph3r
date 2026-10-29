<?php

namespace App\Http\Requests\Currency;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('currency');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('currencies', 'name')->ignore($id),
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
