<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_product(): void
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

        $response = $this->postJson('/api/products', [
            'name' => 'Laptop HP',
            'description' => 'Development laptop',
            'price' => 850,
            'currency_id' => $currency->id,
            'tax_cost' => 136,
            'manufacturing_cost' => 600,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Laptop HP')
            ->assertJsonPath('data.state', 1);

        $this->assertDatabaseHas('products', [
            'name' => 'Laptop HP',
            'currency_id' => $currency->id,
            'created_by' => $user->id,
            'state' => 1,
        ]);
    }

    public function test_guest_cannot_create_product(): void
    {
        $this->postJson('/api/products', [
            'name' => 'Laptop HP',
        ])->assertUnauthorized();
    }
}
