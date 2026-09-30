<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\StaffProfile;
use App\Models\Attendance;
use App\Models\AttendanceNotification;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceLeaveController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');

        $applicationsQuery = LeaveApplication::with(['leaveType', 'applicant.user', 'approver'])
            ->orderBy('id', 'desc');

        if ($status) {
            $applicationsQuery->where('status', $status);
        }

        $applications = $applicationsQuery->paginate(15);
        $leaveTypes = LeaveType::all();
        $staffMembers = StaffProfile::with('user')->where('status', 'active')->get();
        $leaveBalances = LeaveBalance::with('staff.user')->where('year', now()->year)->get();

        return view('admin.attendance_software.leaves.index', compact('applications', 'leaveTypes', 'staffMembers', 'leaveBalances', 'status'));
    }

    public function storeType(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'max_days'   => 'required|integer|min:1',
            'type_key'   => 'nullable|string|max:50',
        ]);

        LeaveType::create([
            'name' => $request->name,
            'days' => $request->max_days,
        ]);

        return redirect()->back()->with('success', 'Leave type added successfully!');
    }

    public function storeApplication(Request $request)
    {
        $request->validate([
            'staff_profile_id' => 'required|exists:staff_profiles,id',
            'leave_type_id'    => 'required|exists:leave_types,id',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after_or_equal:start_date',
            'reason'           => 'required|string|max:1000',
        ]);

        LeaveApplication::create([
            'leave_type_id'  => $request->leave_type_id,
            'applicant_type' => StaffProfile::class,
            'applicant_id'   => $request->staff_profile_id,
            'start_date'     => $request->start_date,
            'end_date'       => $request->end_date,
            'reason'         => $request->reason,
            'status'         => 'pending',
        ]);

        return redirect()->back()->with('success', 'Leave application submitted successfully!');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status'  => 'required|in:approved,rejected',
            'remarks' => 'nullable|string|max:500',
        ]);

        $app = LeaveApplication::with(['leaveType', 'applicant'])->findOrFail($id);
        $app->status = $request->status;
        $app->remarks = $request->remarks;
        $app->approved_by = auth()->id();
        $app->save();

        if ($request->status === 'approved' && $app->applicant_type === StaffProfile::class) {
            $staff = StaffProfile::find($app->applicant_id);
            if ($staff) {
                // Update Leave Balance
                $balance = LeaveBalance::firstOrCreate(
                    ['staff_profile_id' => $staff->id, 'year' => now()->year],
                    ['casual_leave_quota' => 10, 'sick_leave_quota' => 14, 'annual_leave_quota' => 15, 'emergency_leave_quota' => 5]
                );

                $leaveDays = Carbon::parse($app->start_date)->diffInDays(Carbon::parse($app->end_date)) + 1;
                $leaveTypeName = strtolower($app->leaveType?->name ?? 'casual');

                if (str_contains($leaveTypeName, 'sick')) {
                    $balance->increment('sick_leave_used', $leaveDays);
                } elseif (str_contains($leaveTypeName, 'annual')) {
                    $balance->increment('annual_leave_used', $leaveDays);
                } elseif (str_contains($leaveTypeName, 'emergency')) {
                    $balance->increment('emergency_leave_used', $leaveDays);
                } else {
                    $balance->increment('casual_leave_used', $leaveDays);
                }

                // Mark Attendance as Leave for those dates
                $start = Carbon::parse($app->start_date);
                $end   = Carbon::parse($app->end_date);
                for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                    Attendance::updateOrCreate([
                        'attendable_type' => StaffProfile::class,
                        'attendable_id'   => $staff->id,
                        'attendance_date' => $d->toDateString(),
                    ], [
                        'branch_id'     => $staff->branch_id,
                        'department_id' => $staff->department_id,
                        'status'        => 'leave',
                        'leave_reason'  => $app->reason,
                    ]);
                }

                // Send Notification
                AttendanceNotification::create([
                    'staff_profile_id' => $staff->id,
                    'type'             => 'leave_approval',
                    'channel'          => 'sms',
                    'recipient'        => $staff->phone,
                    'message'          => "Your leave application from {$app->start_date->format('Y-m-d')} to {$app->end_date->format('Y-m-d')} has been APPROVED.",
                    'status'           => 'sent',
                ]);
            }
        }

        return redirect()->back()->with('success', "Leave application status updated to {$request->status}!");
    }
}
