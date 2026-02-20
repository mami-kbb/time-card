<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $startDate = Carbon::create(2025, 11, 1);
        $endDate = Carbon::create(2026, 2, 28);

        $period = CarbonPeriod::create($startDate, $endDate);

        $users = User::where('role', 0)->get();

        foreach ($users as $user) {
            foreach ($period as $date) {
                if ($date->isWeekend()) continue;

                $pattern = rand(1, 5);

                $startTime = '09:00';
                $endTime = '18:00';

                if ($pattern === 2) {
                    $startTime = '09:30';
                }

                if ($pattern === 3) {
                    $endTime = '17:00';
                }

                if ($pattern === 5) {
                    $endTime = '19:00';
                }

                $workSeconds = $endTime
                    ? \Carbon\Carbon::parse($startTime)
                    ->diffInSeconds(\Carbon\Carbon::parse($endTime))
                    : 0;

                $breakSeconds = 3600;
                if ($pattern === 4) {
                    $breakSeconds += 20 * 60;
                }

                $attendanceId = DB::table('attendances')->insertGetId([
                    'user_id' => $user->id,
                    'work_date' => $date->toDateString(),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'total_time' => $endTime ? $workSeconds - $breakSeconds : 0,
                    'total_break_time' => $breakSeconds,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('attendance_breaks')->insert([
                    'attendance_id' => $attendanceId,
                    'break_start_time' => '12:00',
                    'break_end_time' => '13:00',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($pattern === 4) {
                    DB::table('attendance_breaks')->insert([
                        'attendance_id' => $attendanceId,
                        'break_start_time' => '16:00',
                        'break_end_time' => '16:20',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
