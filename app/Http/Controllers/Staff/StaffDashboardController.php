<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffDashboardController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $staff = $user->staffProfile;
        
        return view('staff.dashboard', compact('user', 'staff'));
    }

    public function attendance()
    {
        $staff = Auth::user()->staffProfile;
        
        $attendances = \App\Models\Attendance::where('attendable_type', \App\Models\StaffProfile::class)
            ->where('attendable_id', $staff->id)
            ->orderBy('attendance_date', 'desc')
            ->get();
            
        return view('staff.attendance', compact('staff', 'attendances'));
    }

    public function salary()
    {
        $staff = Auth::user()->staffProfile;
        
        $salaries = \App\Models\Salary::where('user_id', Auth::id())
            ->with('payments')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
            
        return view('staff.salary', compact('staff', 'salaries'));
    }
}
