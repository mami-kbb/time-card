<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\AttendanceBreak;

class AttendanceBreakFactory extends Factory
{
    protected $model = AttendanceBreak::class;

    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(),
            'break_start_time' => '12:00:00',
            'break_end_time' => '13:00:00',
        ];
    }
}
