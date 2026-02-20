<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $currentDate = $request->input('date')
            ? Carbon::createFromFormat('Y-m-d', $request->date)
            : Carbon::today();

        $users = User::whereHas('attendances', function ($query) use ($currentDate) {
            $query->whereDate('work_date', $currentDate)
            ->whereNotNull('start_time');
        })
        ->with(['attendances' =>function ($query) use ($currentDate) {
            $query->whereDate('work_date', $currentDate);
        }])
        ->get();

        return view('admin.attendance.index', compact('users', 'currentDate'));
    }

    public function show($id)
    {
        $attendance = Attendance::with(['attendanceBreaks', 'applications'])
            ->findOrFail($id);

        $workDate = Carbon::parse($attendance->work_date);

        $application = $attendance->applications()->latest()->first();
        $isPending = $application?->approval_status === 0;

        $displayStartTime = null;
        $displayEndTime   = null;
        $displayBreaks    = collect();
        $isEditable       = true;

        if ($isPending) {
            $displayStartTime = $application->new_start_time;
            $displayEndTime   = $application->new_end_time;
            $displayBreaks    = $application->applicationBreaks ?? collect();
            $isEditable       = false;
        } else {
            $displayStartTime = $attendance->start_time;
            $displayEndTime   = $attendance->end_time;
            $displayBreaks    = $attendance->attendanceBreaks ?? collect();
            $isEditable       = true;
        }

        return view('admin.attendance.show', compact(
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

    public function staff(Request $request)
    {
        $users = User::where('role', 0)->get();
        return view('admin.attendance.staff', compact('users'));
    }

    public function staffIndex(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $currentMonth = $request->input('month')
            ? Carbon::createFromFormat('Y-m', $request->month)
            : Carbon::now();

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $id)
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

        return view('admin.staff.index', compact('dates', 'currentMonth', 'user'));
    }
}
