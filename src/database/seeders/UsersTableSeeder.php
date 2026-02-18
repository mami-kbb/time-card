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
                'name' => '管理者',
                'email' => 'admin@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('test1234'),
                'role' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '西 伶菜',
                'email' => 'reina.n@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('test1234'),
                'role' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '山田 太郎',
                'email' => 'taro.y@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('test1234'),
                'role' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '増田 一世',
                'email' => 'issei.m@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('test1234'),
                'role' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '山本 敬吉',
                'email' => 'keikichi.y@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('test1234'),
                'role' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '秋田 朋美',
                'email' => 'tomomi.a@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('test1234'),
                'role' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '中西 教夫',
                'email' => 'norio.n@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('test1234'),
                'role' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
