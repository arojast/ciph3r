<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::with('currency')
            ->where('state', 1)
            ->orderByDesc('id')
            ->paginate(15);

        return response()->json($products);
    }

    public function store(StoreProductRequest $request): JsonResponse {
        $product = Product::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
            'state' => 1,
        ]);

        return response()->json([
            'message' => 'Producto creado exitosamente.',
            'data' => $product->load('currency'),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $product = Product::with('currency')
            ->where('state', 1)
            ->findOrFail($id);

        return response()->json([
            'data' => $product,
        ]);
    }

    public function update(UpdateProductRequest $request, int $id): JsonResponse {
        $product = Product::where('state', 1)
            ->findOrFail($id);

        $product->update([
            ...$request->validated(),
            'updated_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Producto actualizado exitosamente.',
            'data' => $product->load('currency'),
        ]);
    }

    public function destroy(Request $request,int $id): JsonResponse {
        $product = Product::where('state', 1)
            ->findOrFail($id);

        $product->update([
            'state' => 0,
            'updated_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Producto eliminado exitosamente.',
        ]);
    }
}
