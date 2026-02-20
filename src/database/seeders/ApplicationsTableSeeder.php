<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;

class ApplicationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attendances = Attendance::whereNotNull('end_time')->inRandomOrder()->limit(6)->get();

        foreach ($attendances as $index => $attendance) {
            $isApproved = $index >= 3;

            $newStart = '09:30';
            $newEnd   = '18:30';

            $applicationId = DB::table('applications')->insertGetId([
                'attendance_id' => $attendance->id,
                'user_id' => $attendance->user_id,
                'approval_status' => $isApproved ? 1 : 0,
                'approval_at' => $isApproved ? now() : null,
                'new_start_time' => $newStart,
                'new_end_time' => $newEnd,
                'comment' => $isApproved
                    ? '電車遅延のため時間変更（承認済み）'
                    : '私用のため時間修正申請',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('application_breaks')->insert([
                'application_id' => $applicationId,
                'new_break_start_time' => '12:30',
                'new_break_end_time' => '13:30',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($isApproved) {

                $workSeconds = \Carbon\Carbon::parse($newStart)
                    ->diffInSeconds(\Carbon\Carbon::parse($newEnd));

                $breakSeconds = 3600;

                DB::table('attendances')
                    ->where('id', $attendance->id)
                    ->update([
                        'start_time' => $newStart,
                        'end_time' => $newEnd,
                        'total_time' => $workSeconds - $breakSeconds,
                        'total_break_time' => $breakSeconds,
                        'updated_at' => now(),
                    ]);
            }
        }
    }
}
