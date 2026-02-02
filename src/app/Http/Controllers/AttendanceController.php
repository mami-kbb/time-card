<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function clock()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        Carbon::setLocale('ja');
        $now = Carbon::now();

        $attendance = Attendance::where('user_id', $user->id)
        ->where('work_date', $today)
        ->first();

        $status = 'off';

        if ($attendance) {
            if ($attendance->end_time) {
                $status = 'finished';
            } else {
                $onBreak = AttendanceBreak::where('attendance_id', $attendance->id)
                ->whereNull('break_end_time')
                ->exists();
                $status = $onBreak ? 'break' : 'working';
            }
        }
        return view('attendance.stamp', compact('now','status', 'attendance'));
    }

    public function stamp(Request $request)
    {
        $user = Auth::user();
        $today = now()->toDateString();

        Carbon::setLocale('ja');
        $now = Carbon::now();

        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $today],
            ['start_time' => now()]
        );

        switch ($request->action) {
            case 'start';
                break;
            case 'end';
                $attendance->update([
                    'end_time' => now(),
                ]);
                break;
            case 'break_start';
                AttendanceBreak::create([
                    'attendance_id' => $attendance->id,
                    'break_start_time' => now(),
                ]);
                break;
            case 'break_end';
                AttendanceBreak::where('attendance_id', $attendance->id)
                ->latest()
                ->first()
                ->update([
                    'break_end_time' => now(),
                ]);
                break;
        }
        return redirect()->back();
    }
}