<?php

namespace App\Http\Requests\Product;

class UpdateProductRequest extends StoreProductRequest
{
    public function rules(): array
    {
        return parent::rules();
    }
}
