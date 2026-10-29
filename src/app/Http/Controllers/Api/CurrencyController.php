<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Currency\StoreCurrencyRequest;
use App\Http\Requests\Currency\UpdateCurrencyRequest;
use App\Models\Currency;
use Illuminate\Http\JsonResponse;

class CurrencyController extends Controller
{
    public function index(): JsonResponse
    {
        $currencies = Currency::where('state', 1)
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $currencies,
        ]);
    }

    public function store(StoreCurrencyRequest $request): JsonResponse
    {
        $currency = Currency::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
            'state' => 1,
        ]);

        return response()->json([
            'message' => 'Moneda creada exitosamente.',
            'data' => $currency,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $currency = Currency::where('state', 1)
            ->findOrFail($id);

        return response()->json([
            'data' => $currency,
        ]);
    }

    public function update(
        UpdateCurrencyRequest $request,
        int $id
    ): JsonResponse {
        $currency = Currency::where('state', 1)
            ->findOrFail($id);

        $currency->update([
            ...$request->validated(),
            'updated_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Moneda actualizada exitosamente.',
            'data' => $currency,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $currency = Currency::where('state', 1)
            ->findOrFail($id);

        // Prevent deactivation while active products or prices use it.
        $inUse = $currency->products()
            ->where('state', 1)
            ->exists()
            || $currency->productPrices()
                ->where('state', 1)
                ->exists();

        if ($inUse) {
            return response()->json([
                'message' => 'No se puede desactivar una moneda en uso.',
            ], 409);
        }

        $currency->update([
            'state' => 0,
            'updated_by' => request()->user()->id,
        ]);

        return response()->json([
            'message' => 'Moneda desactivada exitosamente.',
        ]);
    }
}
