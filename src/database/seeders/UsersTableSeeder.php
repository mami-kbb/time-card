<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => '一般ユーザー',
                'email' => 'user@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('test1234'),
                'role' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '管理者',
                'email' => 'admin@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('test1234'),
                'role' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
