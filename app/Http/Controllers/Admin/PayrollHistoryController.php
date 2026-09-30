<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffProfile;
use App\Models\Salary;
use App\Models\Attendance;
use Illuminate\Http\Request;

class PayrollHistoryController extends Controller
{
    public function index()
    {
        $staffMembers = StaffProfile::with(['user.role'])
            ->whereHas('user', function($q) {
                $q->where('status', 'active');
            })->paginate(15);

        return view('admin.payroll.history.index', compact('staffMembers'));
    }

    public function show($id)
    {
        $staff = StaffProfile::with(['user.role'])->findOrFail($id);

        $salaries = Salary::where('user_id', $staff->user_id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $attendances = Attendance::where('attendable_type', StaffProfile::class)
            ->where('attendable_id', $staff->id)
            ->orderBy('attendance_date', 'desc')
            ->get();

        $groupedAttendances = $attendances->groupBy(function ($item) {
            return \Carbon\Carbon::parse($item->attendance_date)->format('F Y');
        });

        return view('admin.payroll.history.show', compact('staff', 'salaries', 'groupedAttendances'));
    }
}
