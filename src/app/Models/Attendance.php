<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'start_time',
        'end_time',
        'total_time',
        'total_break_time',
        'comment'
    ];

    protected $casts = [
        'work_date' => 'date',
        'start_time' => 'string',
        'end_time' => 'string',
    ];

    public function attendanceBreaks()
    {
        return $this->hasMany(AttendanceBreak::class)->orderBy('break_start_time');
    }

    public function applications()
    {
        return $this->hasMany(Application::class)
        ->latest();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function calculateTotalBreakTime(): int
    {
        return $this->attendanceBreaks()
        ->whereNotNull('break_end_time')
        ->get()
        ->sum(function ($break) {
            return Carbon::parse($break->break_start_time)
                ->diffInMinutes(Carbon::parse($break->break_end_time));
        });
    }

    public function calculateTotalWorkTime(): int
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }

        $workMinutes = Carbon::parse($this->start_time)
        ->diffInMinutes(Carbon::parse($this->end_time));

        return $workMinutes - $this->calculateTotalBreakTime();
    }
}
