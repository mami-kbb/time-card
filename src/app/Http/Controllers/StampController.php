<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\Application;
use App\Models\ApplicationBreak;
use App\Http\Requests\ApplicationRequest;
use Illuminate\Support\Facades\Auth;

class StampController extends Controller
{
    public function store(ApplicationRequest $request, $date)
    {
        DB::transaction(function () use ($request, $date) {
            $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('work_date', $date)
            ->first();

            if (!$attendance) {
                $attendance = Attendance::create([
                    'user_id' => auth()->id(),
                    'work_date' => $date,
                ]);
            }

            $application = Application::create([
                'attendance_id' => $attendance->id,
                'user_id' => auth()->id(),
                'approval_status' => Application::STATUS_PENDING,
                'new_start_time' => $request->new_start_time,
                'new_end_time' => $request->new_end_time,
                'comment' => $request->comment,
            ]);

            foreach ($request->breaks ?? [] as $break) {
                if (
                    empty($break['new_break_start_time']) &&
                    empty($break['new_break_end_time'])
                ) {
                    continue;
                }

                ApplicationBreak::create([
                    'application_id' => $application->id,
                    'new_break_start_time' => $break['new_break_start_time'],
                    'new_break_end_time' => $break['new_break_end_time'],
                ]);
            }
        });

        return redirect("/attendance/detail/" . $date);
    }

    public function index(Request $request)
    {
        $tab = $request->query('tab', 'pending');
        $query = Application::with(['user', 'attendance'])->where('user_id', auth()->id());

        if ($tab === 'approved') {
            $query->where('approval_status', Application::STATUS_APPROVED);
        } else {
            $query->where('approval_status', Application::STATUS_PENDING);
        }

        $applications = $query->get();

        return view('requests.index', compact('applications', 'tab'));
    }
}
