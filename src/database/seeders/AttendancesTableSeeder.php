<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('attendances')->insert([
            [
                'user_id' => 2,
                'work_date' => '2025-11-04',
                'start_time' => '08:30',
                'end_time' => '17:30',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'work_date' => '2025-11-16',
                'start_time' => '08:30',
                'end_time' => '17:30',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'work_date' => '2025-12-08',
                'start_time' => '08:30',
                'end_time' => '17:30',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'work_date' => '2025-12-24',
                'start_time' => '08:30',
                'end_time' => '17:30',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'work_date' => '2026-01-08',
                'start_time' => '08:30',
                'end_time' => '17:30',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'work_date' => '2026-01-22',
                'start_time' => '08:30',
                'end_time' => '17:30',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
