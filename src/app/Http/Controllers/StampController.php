<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;
use App\Models\Application;
use App\Models\User;
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

                $application->applicationBreaks()->create([
                    'new_break_start_time' => $break['new_break_start_time'],
                    'new_break_end_time' => $break['new_break_end_time'],
                ]);
            }
        });

        return redirect("/attendance/detail/" . $date);
    }

    public function correct(ApplicationRequest $request, User $user, $date)
    {
        $attendance = Attendance::where('user_id', $user->id)
        ->whereDate('work_date', $date)
        ->firstOrFail();

        DB::transaction(function () use ($request, $attendance, $user) {
            $application = Application::create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'new_start_time' => $request->new_start_time,
                'new_end_time' => $request->new_end_time,
                'comment' => $request->comment,
                'approval_status' => Application::STATUS_APPROVED,
            ]);

            foreach ($request->breaks ?? [] as $break) {
                if (
                    empty($break['new_break_start_time']) &&
                    empty($break['new_break_end_time'])
                ) {
                    continue;
                }

                $application->applicationBreaks()->create([
                    'new_break_start_time' => $break['new_break_start_time'],
                    'new_break_end_time'   => $break['new_break_end_time'],
                ]);
            }

            $attendance->update([
                'start_time' => $request->new_start_time,
                'end_time'   => $request->new_end_time,
            ]);

            $attendance->attendanceBreaks()->delete();

            foreach ($request->breaks ?? [] as $break) {

                if (
                    empty($break['new_break_start_time']) &&
                    empty($break['new_break_end_time'])
                ) {
                    continue;
                }
                $attendance->attendanceBreaks()->create([
                    'break_start_time' => $break['new_break_start_time'],
                    'break_end_time'   => $break['new_break_end_time'],
                ]);
            }
        });

        return redirect("/admin/attendance/" . $user->id . "/" . $date)->with('message', '※修正しました');
    }
}
