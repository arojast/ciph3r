<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductPrice\StoreProductPriceRequest;
use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductPriceController extends Controller
{
    public function index(int $productId): JsonResponse
    {
        $product = Product::where('state', 1)
            ->findOrFail($productId);

        $prices = $product->prices()
            ->with('currency')
            ->where('state', 1)
            ->whereHas('currency', function ($query) {
                $query->where('state', 1);
            })
            ->get();

        return response()->json([
            'product_id' => $product->id,
            'data' => $prices,
        ]);
    }

    public function store(StoreProductPriceRequest $request,int $productId): JsonResponse {
        $product = Product::where('state', 1)
            ->findOrFail($productId);

        $data = $request->validated();

        $price = DB::transaction(function () use (
            $product,
            $data,
            $request
        ) {
            $existing = ProductPrice::where(
                'product_id',
                $product->id
            )
                ->where('currency_id', $data['currency_id'])
                ->lockForUpdate()
                ->first();

            if ($existing) {
                $existing->update([
                    'price' => $data['price'],
                    'state' => 1,
                    'updated_by' => $request->user()->id,
                ]);

                return $existing;
            }

            return ProductPrice::create([
                'product_id' => $product->id,
                'currency_id' => $data['currency_id'],
                'price' => $data['price'],
                'created_by' => $request->user()->id,
                'state' => 1,
            ]);
        });

        return response()->json([
            'message' => 'Precio de producto guardado exitosamente.',
            'data' => $price->load('currency'),
        ], $price->wasRecentlyCreated ? 201 : 200);
    }
}
