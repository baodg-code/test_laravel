<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->delete();

        User::create([
            'name' => 'Admin',
            'email' => 'admin@cafe.local',
            'password' => Hash::make('admin123'),
            'is_admin' => true,
        ]);

        User::create([
            'name' => 'User',
            'email' => 'user@cafe.local',
            'password' => Hash::make('user123'),
            'is_admin' => false,
        ]);

        $this->call([
            ProductSeeder::class,
        ]);
    }
}
