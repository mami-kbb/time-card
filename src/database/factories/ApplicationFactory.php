<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\Application;

class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(),
            'approval_status' => 0,
            'comment' => '電車遅延のため修正',
            'new_start_time' => '09:15:00',
            'new_end_time' => '18:15:00',
        ];
    }
}
