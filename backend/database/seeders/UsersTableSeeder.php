<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Первый тестовый пользователь
        User::create([
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => Hash::make('password'),
            'balance' => 1000.00,
            'api_key' => Str::random(64), // лучше 64, чтобы HMAC был надёжнее
        ]);

        // Второй тестовый пользователь
        User::create([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => Hash::make('password'),
            'balance' => 5.00,
            'api_key' => Str::random(64),
        ]);
    }
}
