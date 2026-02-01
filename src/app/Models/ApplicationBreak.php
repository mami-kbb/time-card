<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'new_break_start_time',
        'new_break_end_time'
    ];

    protected $casts = [
        'new_break_start_time' => 'datetime:H:i',
        'new_break_end_time' => 'datetime:H:i',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
