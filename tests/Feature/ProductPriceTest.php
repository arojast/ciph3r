<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductPriceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_product_price(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $currency = Currency::create([
            'name' => 'USD',
            'symbol' => '$',
            'exchange_rate' => 1,
            'created_by' => $user->id,
            'state' => 1,
        ]);

        $product = Product::create([
            'name' => 'Laptop',
            'description' => 'Development laptop',
            'price' => 1000,
            'currency_id' => $currency->id,
            'tax_cost' => 160,
            'manufacturing_cost' => 600,
            'created_by' => $user->id,
            'state' => 1,
        ]);

        $response = $this->postJson(
            "/api/products/{$product->id}/prices",
            [
                'currency_id' => $currency->id,
                'price' => 1200,
            ]
        );

        $response->assertCreated();

        $this->assertDatabaseHas('product_prices', [
            'product_id' => $product->id,
            'currency_id' => $currency->id,
            'created_by' => $user->id,
            'state' => 1,
        ]);
    }
}
