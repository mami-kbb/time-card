<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Application;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        if (auth('admin')->check()) {
            $tab = $request->query('tab', 'pending');
            $query = Application::with(['user', 'attendance']);

            if ($tab === 'approved') {
                $query->where('approval_status', Application::STATUS_APPROVED);
            } else {
                $query->where('approval_status', Application::STATUS_PENDING);
            }

            $applications = $query->get();

            return view('admin.requests.index', compact('applications', 'tab'));

        } elseif (auth()->check()) {
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

    public function show($id)
    {
        $application = Application::with([
            'attendance.user',
            'applicationBreaks'
        ])->findOrFail($id);

        $attendance = $application->attendance;
        $workDate = Carbon::parse($attendance->work_date);

        $isPending = $application->approval_status === 0;

        $displayBreaks = $application->applicationBreaks ?? collect();

        return view('admin.requests.approve', compact(
            'attendance',
            'workDate',
            'application',
            'isPending',
            'displayBreaks'
        ));
    }

    public function approve($id)
    {
        DB::transaction(
            function () use ($id) {
            $application = Application::with('applicationBreaks')->findOrFail($id);

            $attendance = $application->attendance;

            $attendance->update([
                'start_time' => $application->new_start_time,
                'end_time' => $application->new_end_time,
            ]);

            $attendance->attendanceBreaks()->delete();

            foreach ($application->applicationBreaks as $break) {
                $attendance->attendanceBreaks()->create([
                    'break_start_time' => $break->new_break_start_time,
                    'break_end_time' => $break->new_break_end_time,
                ]);
            }

            $application->update([
                'approval_status' => 1
            ]);
            }
        );

        return redirect()->back();
    }
}
