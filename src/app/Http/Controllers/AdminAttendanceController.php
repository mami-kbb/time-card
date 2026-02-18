<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use app\Models\User;
use app\Models\Attendance;

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
}
