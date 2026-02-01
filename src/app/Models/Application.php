<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'approval_status',
        'application_date',
        'new_start_time',
        'new_end_time',
        'comment'
    ];

    protected $casts = [
        'application_date' => 'date',
        'new_start_time' => 'datetime:H:i',
        'new_end_time' => 'datetime:H:i',
    ];

    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;

    public function applicationBreaks()
    {
        return $this->hasMany(ApplicationBreak::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
