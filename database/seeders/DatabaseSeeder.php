<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'یاسین حمیدی',
            'email' => 'admin@admin.com',
            'balance' => 0,
            'role' => UserType::Manager,
            'address' => '---',
            'postal_code' => 111,
            'state' => 'کرمان',
            'city' => 'کرمان',
            'phone' => '09217805272',
            'password' => Hash::make('password'),
            'code' => 1
        ]);
        User::factory()->create([
            'name' => 'Employee User',
            'email' => 'employee@employee.com',
            'balance' => 0,
            'role' => UserType::Employee,
            'address' => 'askldfj;lasdj',
            'postal_code' => 432343,
            'state' => 'state',
            'city' => 'city',
            'phone' => '09338334375',
            'password' => Hash::make('password'),
            'code' => 2
        ]);
        User::factory()->create([
            'name' => 'Author User',
            'email' => 'author@author.com',
            'balance' => 0,
            'role' => UserType::Author,
            'address' => 'askldfj;lasdj',
            'postal_code' => 432343,
            'state' => 'state',
            'city' => 'city',
            'phone' => '09338334376',
            'password' => Hash::make('password'),
            'code' => 3
        ]);
        User::factory()->create([
            'name' => 'User User',
            'email' => 'user@user.com',
            'balance' => 0,
            'role' => UserType::User,
            'address' => 'askldfj;lasdj',
            'postal_code' => 432343,
            'state' => 'state',
            'city' => 'city',
            'phone' => '09338334377',
            'password' => Hash::make('password'),
            'code' => 4
        ]);
    }
}
