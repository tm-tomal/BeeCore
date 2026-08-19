<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::updateOrCreate([
            'email' => 'admin@beecore.test',
        ], [
            'name' => 'BeeCore Admin',
            'password' => Hash::make('password123'),
            'tenant_id' => null,
            'role' => User::ROLE_SUPER_ADMIN,
        ]);
        
        User::updateOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'password' => Hash::make('password'),
            'tenant_id' => null,
            'role' => User::ROLE_SUPER_ADMIN,
        ]);
    }
}
