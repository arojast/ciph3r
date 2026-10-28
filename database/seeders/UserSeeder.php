<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('services.seed_user.email');
        $password = config('services.seed_user.password');

        if (! $email || ! $password) {
            throw new RuntimeException(
                'SEED_USER_EMAIL and SEED_USER_PASSWORD are required.'
            );
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'API Developer',
                'password' => Hash::make($password),
            ]
        );
    }
}
