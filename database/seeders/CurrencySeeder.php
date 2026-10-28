<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::orderBy('id')->first();

        if (! $user) {
            throw new RuntimeException(
                'No users found. Run UserSeeder first.'
            );
        }

        $currencies = [
            [
                'name' => 'VES',
                'symbol' => 'Bs.',
                'exchange_rate' => 1.000000,
            ],
            [
                'name' => 'USD',
                'symbol' => '$',
                'exchange_rate' => 1.000000,
            ],
            [
                'name' => 'EUR',
                'symbol' => '€',
                'exchange_rate' => 1.000000,
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::firstOrCreate(
                ['name' => $currency['name']],
                [
                    'symbol' => $currency['symbol'],
                    'exchange_rate' => $currency['exchange_rate'],
                    'created_by' => $user->id,
                    'state' => 1,
                ]
            );
        }
    }
}
