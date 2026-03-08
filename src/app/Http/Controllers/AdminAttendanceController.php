<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use Carbon\CarbonPeriod;

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

    public function show(User $user, $date)
    {
        $attendance = Attendance::with(['attendanceBreaks', 'applications'])
        ->where('user_id', $user->id)
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
                $isEditable       = true;
            }
        }

        return view('admin.attendance.show', compact(
            'user',
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

    public function exportCsv(Request $request, $id)
    {
        $currentMonth = $request->input('month')
        ? Carbon::createFromFormat('Y-m', $request->month) : Carbon::now();

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $period = CarbonPeriod::create(
            $startOfMonth,
            $endOfMonth
        );

        $user = User::findOrFail($id);

        $attendances = Attendance::where('user_id', $id)
        ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
        ->with('attendanceBreaks')
        ->get()
        ->keyBy(function ($item) {
            return $item->work_date->format('Y-m-d');
        });

        $filename = "attendance_{$id}_{$currentMonth->format('Y-m')}.csv";

        return response()->streamDownload(function () use ($attendances, $period, $user) {

            $file = fopen('php://output', 'w');

            $nameRow = [$user->name . ' さんの勤怠一覧'];
            mb_convert_variables('SJIS-win', 'UTF-8', $nameRow);
            fputcsv($file, $nameRow);

            fputcsv($file, []);

            $csvHeader = ['日付', '出勤', '退勤', '休憩', '合計'];
            mb_convert_variables('SJIS-win', 'UTF-8', $csvHeader);
            fputcsv($file, $csvHeader);

            foreach ($period as $date) {

                $formattedDate = $date->format('Y-m-d');

                $attendance = $attendances->get($formattedDate);

                if ($attendance) {
                    $breakMinutes = $attendance->calculateTotalBreakTime();
                    $workMinutes  = $attendance->calculateTotalWorkTime();

                    $breakHours = floor($breakMinutes / 60);
                    $breakRemain = $breakMinutes % 60;

                    $workHours = floor($workMinutes / 60);
                    $workRemain = $workMinutes % 60;

                    $row = [
                        $formattedDate,
                        $attendance->start_time?->format('H:i'),
                        $attendance->end_time?->format('H:i'),
                        sprintf('%02d：%02d', $breakHours, $breakRemain),
                        sprintf('%02d：%02d', $workHours, $workRemain),
                    ];
                } else {
                    $row = [
                        $formattedDate,
                        '',
                        '',
                        '',
                        '',
                    ];
                }

                mb_convert_variables('SJIS-win', 'UTF-8', $row);
                fputcsv($file, $row);
            }

            fclose($file);
        }, $filename);

    }
}