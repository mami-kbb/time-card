<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'break_start_time',
        'break_end_time'
    ];

    protected $casts = [
        'break_start_time' => 'datetime:H:i',
        'break_end_time' => 'datetime:H:i',
    ];
    
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
