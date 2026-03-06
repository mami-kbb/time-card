<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Application;
use App\Models\ApplicationBreak;

class ApplicationBreakFactory extends Factory
{
    protected $model = ApplicationBreak::class;

    public function definition()
    {
        return [
            'application_id' => Application::factory(),
            'new_break_start_time' => '12:15:00',
            'new_break_end_time' => '13:15:00',
        ];
    }
}
