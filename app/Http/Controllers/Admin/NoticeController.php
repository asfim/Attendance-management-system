<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\Event;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function index()
    {
        $notices = Notice::orderBy('published_at', 'desc')->get();
        $events = Event::orderBy('start_date', 'desc')->get();
        $shifts = \App\Models\Shift::where('status', 'active')->get();
        return view('admin.notices.index', compact('notices', 'events', 'shifts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'target_audience' => 'required|in:all,teachers,students,parents,staff',
            'published_at' => 'required|date',
            'expires_at' => 'nullable|date|after_or_equal:published_at',
            'shift_id' => 'nullable|exists:shifts,id',
        ]);

        $notice = Notice::create($request->all());

        if ($request->has('send_sms')) {
            // Ideally, you would dispatch a Job here, but for simplicity we can send them here
            // To prevent timeout for many SMS, using a background job is highly recommended.
            $smsService = new \App\Services\SmsService();
            $phones = collect();

            if ($request->target_audience == 'all' || $request->target_audience == 'parents') {
                $phones = $phones->merge(\App\Models\ParentProfile::whereNotNull('phone')->pluck('phone'));
            }
            if ($request->target_audience == 'all' || $request->target_audience == 'teachers' || $request->target_audience == 'staff') {
                $phones = $phones->merge(\App\Models\StaffProfile::whereNotNull('phone')->pluck('phone'));
            }
            // For students, you might want to send to their parents or student's own phone if available.
            if ($request->target_audience == 'students') {
                $phones = $phones->merge(\App\Models\ParentProfile::whereNotNull('phone')->pluck('phone')); // sending to parents by default
            }

            $phones = $phones->unique()->filter();
            
            $message = "Notice: " . $notice->title . "\n" . substr($notice->content, 0, 100) . "...";

            foreach ($phones as $phone) {
                $smsService->sendSms($phone, $message);
            }
        }

        return back()->with('success', 'Notice published successfully!');
    }

    public function storeEvent(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
        ]);

        Event::create($request->all());

        return back()->with('success', 'Event scheduled successfully!');
    }
}
