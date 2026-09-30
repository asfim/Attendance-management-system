<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceNotification;
use App\Models\StaffProfile;
use Illuminate\Http\Request;

class AttendanceNotificationController extends Controller
{
    public function index()
    {
        $notifications = AttendanceNotification::with('staff.user')
            ->orderBy('id', 'desc')
            ->paginate(20);

        $staffMembers = StaffProfile::with('user')->where('status', 'active')->get();

        return view('admin.attendance_software.notifications.index', compact('notifications', 'staffMembers'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'staff_profile_id' => 'required|exists:staff_profiles,id',
            'type'             => 'required|in:late,absent,leave_approval,attendance_correction',
            'channel'          => 'required|in:sms,email,whatsapp',
            'message'          => 'required|string|max:1000',
        ]);

        $staff = StaffProfile::with('user')->findOrFail($request->staff_profile_id);
        $recipient = match($request->channel) {
            'email'    => $staff->user?->email,
            'whatsapp' => $staff->phone,
            default    => $staff->phone,
        };

        AttendanceNotification::create([
            'staff_profile_id' => $staff->id,
            'type'             => $request->type,
            'channel'          => $request->channel,
            'recipient'        => $recipient,
            'message'          => $request->message,
            'status'           => 'sent',
            'sent_at'          => now(),
        ]);

        return redirect()->back()->with('success', strtoupper($request->channel) . ' notification triggered successfully to employee!');
    }
}
