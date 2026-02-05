<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceBreaksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('attendance_breaks')->insert([
            [
                'attendance_id' => 1,
                'break_start_time' => '12:00',
                'break_end_time' => '13:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'attendance_id' => 1,
                'break_start_time' => '15:00',
                'break_end_time' => '15:20',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'attendance_id' => 2,
                'break_start_time' => '12:00',
                'break_end_time' => '13:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'attendance_id' => 3,
                'break_start_time' => '12:00',
                'break_end_time' => '13:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'attendance_id' => 4,
                'break_start_time' => '12:00',
                'break_end_time' => '13:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'attendance_id' => 5,
                'break_start_time' => '12:00',
                'break_end_time' => '13:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'attendance_id' => 6,
                'break_start_time' => '12:00',
                'break_end_time' => '13:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'attendance_id' => 6,
                'break_start_time' => '16:00',
                'break_end_time' => '16:30',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
