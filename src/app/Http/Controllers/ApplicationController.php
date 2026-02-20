<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'pending');
        $query = Application::with(['user', 'attendance']);

        if ($tab === 'approved') {
            $query->where('approval_status', Application::STATUS_APPROVED);
        } else {
            $query->where('approval_status', Application::STATUS_PENDING);
        }

        $applications = $query->get();

        return view('admin.requests.index', compact('applications', 'tab'));
    }
}
