<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(): User
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        return $user;
    }

    public function test_user_can_create_currency(): void
    {
        $user = $this->authenticate();

        $response = $this->postJson('/api/currencies', [
            'name' => 'Peso mexicano',
            'symbol' => 'MX$',
            'exchange_rate' => 1,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Peso mexicano');

        $this->assertDatabaseHas('currencies', [
            'name' => 'Peso mexicano',
            'created_by' => $user->id,
            'state' => 1,
        ]);
    }

    public function test_user_can_list_currencies(): void
    {
        $this->authenticate();

        $this->getJson('/api/currencies')
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_currency_name_must_be_unique(): void
    {
        $user = $this->authenticate();

        Currency::create([
            'name' => 'USD',
            'symbol' => '$',
            'exchange_rate' => 1,
            'created_by' => $user->id,
            'state' => 1,
        ]);

        $this->postJson('/api/currencies', [
            'name' => 'USD',
            'symbol' => '$',
            'exchange_rate' => 1,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_user_can_update_currency(): void
    {
        $user = $this->authenticate();

        $currency = Currency::create([
            'name' => 'Peso mexicano',
            'symbol' => 'MX$',
            'exchange_rate' => 1,
            'created_by' => $user->id,
            'state' => 1,
        ]);

        $this->putJson("/api/currencies/{$currency->id}", [
            'name' => 'Peso mexicano',
            'symbol' => 'MXN',
            'exchange_rate' => 1.5,
        ])
            ->assertOk()
            ->assertJsonPath('data.symbol', 'MXN');

        $this->assertDatabaseHas('currencies', [
            'id' => $currency->id,
            'symbol' => 'MXN',
            'updated_by' => $user->id,
        ]);
    }

    public function test_user_can_deactivate_unused_currency(): void
    {
        $user = $this->authenticate();

        $currency = Currency::create([
            'name' => 'Test Currency',
            'symbol' => 'TST',
            'exchange_rate' => 1,
            'created_by' => $user->id,
            'state' => 1,
        ]);

        $this->deleteJson("/api/currencies/{$currency->id}")
            ->assertOk();

        $this->assertDatabaseHas('currencies', [
            'id' => $currency->id,
            'state' => 0,
            'updated_by' => $user->id,
        ]);

        $this->getJson("/api/currencies/{$currency->id}")
            ->assertNotFound();
    }

    public function test_guest_cannot_create_currency(): void
    {
        $this->postJson('/api/currencies', [
            'name' => 'Euro',
            'symbol' => '€',
            'exchange_rate' => 1,
        ])->assertUnauthorized();
    }
}
