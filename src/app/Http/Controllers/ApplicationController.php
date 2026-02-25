<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
}
