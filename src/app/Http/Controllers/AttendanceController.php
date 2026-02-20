<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\Application;
use Illuminate\Bus\PendingBatch;
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
                if ($attendance->end_time) {
                    return redirect()->back();
                }

                $attendance->update([
                    'end_time' => now(),
                ]);

                $attendance->update([
                    'total_break_time' => $attendance->calculateTotalBreakTime(),
                    'total_time' => $attendance->calculateTotalWorkTime(),
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

    public function index(Request $request)
    {
        $userId = Auth::id();

        $currentMonth = $request->input('month')
        ? Carbon::createFromFormat('Y-m', $request->month)
        : Carbon::now();

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $userId)
        ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
        ->get()
        ->keyBy(function ($item) {
            return $item->work_date->format('Y-m-d');
        });

        $dates = [];

        for ($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {

            $attendance = $attendances[$date->format('Y-m-d')] ?? null;

            $dates[] = (object) [
                'date' => $date->copy(),
                'attendance' => $attendance,
            ];

        }

        return view('attendance.index', compact('dates', 'currentMonth'));
    }

    public function show($date)
    {
        $attendance = Attendance::with(['attendanceBreaks', 'applications'])
            ->where('user_id', auth()->id())
            ->whereDate('work_date', $date)
            ->first();

        $workDate = Carbon::parse($date);

        $application = $attendance?->applications()?->latest()->first();
        $isPending = $application?->approval_status === 0;

        $displayStartTime = null;
        $displayEndTime   = null;
        $displayBreaks    = collect();
        $isEditable       = true;

        if ($attendance) {
            if ($isPending) {
                $displayStartTime = $application->new_start_time;
                $displayEndTime   = $application->new_end_time;
                $displayBreaks    = $application->applicationBreaks ?? collect();
                $isEditable       = false;
            } else {
                $displayStartTime = $attendance->start_time;
                $displayEndTime   = $attendance->end_time;
                $displayBreaks    = $attendance->attendanceBreaks ?? collect();
            }
        }

        return view('attendance.show', compact(
            'workDate',
            'attendance',
            'application',
            'displayStartTime',
            'displayEndTime',
            'displayBreaks',
            'isEditable',
            'isPending'
        ));
    }
}