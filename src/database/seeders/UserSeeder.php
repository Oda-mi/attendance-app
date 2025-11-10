<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'is_admin' => 1,
        ]);

        User::create([
            'name' => '一般ユーザー',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => 0,
        ]);

        User::create([
            'name' => '一般ユーザー2',
            'email' => 'user2@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => 0,
        ]);

        User::create([
            'name' => '一般ユーザー3',
            'email' => 'user3@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => 0,
        ]);
    }
}
