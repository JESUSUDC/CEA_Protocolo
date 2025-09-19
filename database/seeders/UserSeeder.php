<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Infrastructure\Adapters\Database\Eloquent\Model\UserModel;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        UserModel::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Admin User',
            'role' => 'admin',
            'email' => 'admin@example.com',
            'username' => 'admin',
            'password_hash' => Hash::make('password123'),
            'active' => true,
        ]);

        UserModel::create([
            'id' => Str::uuid()->toString(),
            'name' => 'John Doe',
            'role' => 'user',
            'email' => 'john@example.com',
            'username' => 'johndoe',
            'password_hash' => Hash::make('secret123'),
            'active' => true,
        ]);
    }
}
