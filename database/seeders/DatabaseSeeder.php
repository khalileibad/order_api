<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@tocaan.test',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'phone' => '+1234567890',
            'address' => 'Admin Address',
        ]);

        // إنشاء مستخدم عادي
        User::create([
            'name' => 'Test Customer',
            'email' => 'customer@tocaan.test',
            'password' => Hash::make('customer123'),
            'role' => 'customer',
            'phone' => '+0987654321',
            'address' => 'Customer Address',
        ]);

        // إنشاء 5 مستخدمين عشوائيين
        User::factory()->count(5)->create();
    }
}
