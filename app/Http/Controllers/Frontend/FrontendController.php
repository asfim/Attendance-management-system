<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    public function index()
    {
        $notices = \App\Models\Notice::latest()->take(4)->get();
        $events = \App\Models\Event::where('start_date', '>=', now())->orderBy('start_date', 'asc')->take(1)->get();
        
        // Fetch up to 4 senior teachers (using StaffProfile, filtering by designation and sorted by joining_date)
        $teachers = \App\Models\StaffProfile::with('user')->where('status', 'active')
            ->where(function($q) {
                $q->where('designation', 'like', '%Teacher%')
                  ->orWhere('designation', 'like', '%শিক্ষক%')
                  ->orWhere('designation', 'like', '%Professor%')
                  ->orWhere('designation', 'like', '%Lecturer%');
            })
            ->orderBy('joining_date', 'asc')
            ->take(4)->get();

        // If no teachers found by designation, just grab any 4 active staff as fallback
        if ($teachers->isEmpty()) {
            $teachers = \App\Models\StaffProfile::with('user')->where('status', 'active')
                ->orderBy('joining_date', 'asc')
                ->take(4)->get();
        }

        // Fetch classes and exams for the quick result search form
        $classes = \App\Models\SchoolClass::all();
        $exams = \App\Models\ExamType::where('status', 'active')->get();
        if ($exams->isEmpty()) {
            $exams = \App\Models\ExamType::all();
        }

        // Fetch gallery data for homepage
        $rawGallery = json_decode(\App\Models\Setting::get('gallery_data', '[]'), true);
        if (empty($rawGallery)) {
            $rawGallery = [
                ['image' => 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?auto=format&fit=crop&w=900&q=85', 'title' => 'Library'],
                ['image' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=900&q=85', 'title' => 'Classroom'],
                ['image' => 'https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=900&q=85', 'title' => 'Sports'],
                ['image' => 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=900&q=85', 'title' => 'Students'],
                ['image' => 'https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=900&q=85', 'title' => 'Campus'],
                ['image' => 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=900&q=85', 'title' => 'Graduation']
            ];
        }
        $homeGallery = array_slice($rawGallery, 0, 6);

        return view('frontend.home', compact('notices', 'events', 'teachers', 'classes', 'exams', 'homeGallery'));
    }

    public function about()
    {
        return view('frontend.pages.about');
    }

    public function academics()
    {
        return view('frontend.pages.academics');
    }

    public function departments()
    {
        return view('frontend.pages.departments');
    }

    public function admission()
    {
        $classes = \App\Models\SchoolClass::with('sections')->get();
        $sessions = \App\Models\AcademicSession::where('is_active', true)->get();
        if ($sessions->isEmpty()) $sessions = \App\Models\AcademicSession::all();
        $shifts = \App\Models\Shift::where('status', 'active')->get();
        return view('frontend.pages.admission', compact('classes', 'sessions', 'shifts'));
    }

    public function storeAdmission(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:academic_sessions,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'admission_date' => 'required|date',
            'dob' => 'required|date',
            'gender' => 'required|in:Male,Female,Other',
            'blood_group' => 'nullable|string',
            'medical_info' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'signature' => 'nullable|image|max:1024',
            'parent_name' => 'required|string|max:255',
            'parent_email' => 'required|email|max:255',
            'parent_phone' => 'required|string|max:20',
            'parent_occupation' => 'nullable|string',
            'parent_address' => 'required|string',
            'shift_id' => 'nullable|exists:shifts,id',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('admissions/photos', 'public');
        }

        $sigPath = null;
        if ($request->hasFile('signature')) {
            $sigPath = $request->file('signature')->store('admissions/signatures', 'public');
        }

        \App\Models\AdmissionApplication::create([
            'session_id' => $request->session_id,
            'class_id' => $request->class_id,
            'section_id' => $request->section_id,
            'name' => $request->name,
            'email' => $request->email,
            'admission_date' => $request->admission_date,
            'dob' => $request->dob,
            'gender' => $request->gender,
            'blood_group' => $request->blood_group,
            'medical_info' => $request->medical_info,
            'photo_path' => $photoPath,
            'signature_path' => $sigPath,
            'parent_name' => $request->parent_name,
            'parent_email' => $request->parent_email,
            'parent_phone' => $request->parent_phone,
            'parent_occupation' => $request->parent_occupation,
            'parent_address' => $request->parent_address,
            'shift_id' => $request->shift_id,
        ]);

        return redirect()->back()->with('success', 'Your admission application has been submitted successfully. We will contact you soon.');
    }

    public function campus()
    {
        return view('frontend.pages.campus');
    }

    public function notice()
    {
        $notices = \App\Models\Notice::latest()->paginate(12);
        return view('frontend.pages.notice', compact('notices'));
    }

    public function noticeDetails($id)
    {
        $notice = \App\Models\Notice::findOrFail($id);
        return view('frontend.pages.notice-details', compact('notice'));
    }

    public function noticeDownload($id)
    {
        $notice = \App\Models\Notice::findOrFail($id);
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('frontend.pages.notice-pdf', compact('notice'));
        $pdf->setPaper('A4', 'portrait');
        $filename = 'Notice_' . str_pad($notice->id, 4, '0', STR_PAD_LEFT) . '_' . \Illuminate\Support\Str::slug($notice->title) . '.pdf';
        return $pdf->download($filename);
    }

    public function events()
    {
        $events = \App\Models\Event::orderBy('start_date', 'asc')->paginate(12);
        return view('frontend.pages.events', compact('events'));
    }

    public function eventDetails($id)
    {
        $event = \App\Models\Event::findOrFail($id);
        return view('frontend.pages.event-details', compact('event'));
    }

    public function gallery()
    {
        $settings = \App\Models\Setting::where('key', 'like', 'gallery_%')->pluck('value', 'key')->toArray();

        $rawGallery = json_decode($settings['gallery_data'] ?? '[]', true);

        // Fallback default items if database setting is empty
        if (empty($rawGallery)) {
            $rawGallery = [
                ['image' => 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?q=80&w=1200&auto=format&fit=crop', 'title' => 'Main Campus Building',       'category' => 'Campus',    'size' => 'big'],
                ['image' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?q=80&w=800&auto=format&fit=crop',  'title' => 'Library Study Session',      'category' => 'Academics', 'size' => 'normal'],
                ['image' => 'https://images.unsplash.com/photo-1529390079861-591de354faf5?q=80&w=800&auto=format&fit=crop',  'title' => 'Annual Graduation Ceremony', 'category' => 'Events',    'size' => 'tall'],
                ['image' => 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?q=80&w=1200&auto=format&fit=crop', 'title' => 'Inter-School Athletics Meet','category' => 'Sports',    'size' => 'wide'],
                ['image' => 'https://images.unsplash.com/photo-1562774053-701939374585?q=80&w=800&auto=format&fit=crop',  'title' => 'Modern Science Labs',        'category' => 'Academics', 'size' => 'normal'],
                ['image' => 'https://images.unsplash.com/photo-1498075702571-ecb018f3752d?q=80&w=800&auto=format&fit=crop',  'title' => 'Lush Green Courtyard',       'category' => 'Campus',    'size' => 'normal'],
                ['image' => 'https://images.unsplash.com/photo-1577896851231-70ef18881754?q=80&w=800&auto=format&fit=crop',  'title' => 'Interactive Classrooms',    'category' => 'Academics', 'size' => 'tall'],
                ['image' => 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?q=80&w=800&auto=format&fit=crop',  'title' => 'Technology Seminar',         'category' => 'Events',    'size' => 'normal'],
                ['image' => 'https://images.unsplash.com/photo-1525926472898-a0f26ceb4cd2?q=80&w=1200&auto=format&fit=crop', 'title' => 'Campus Overview',            'category' => 'Campus',    'size' => 'wide'],
            ];
        }

        $categories = [];
        foreach ($rawGallery as &$item) {
            $catName = !empty($item['category']) ? trim($item['category']) : 'General';
            $catSlug = \Illuminate\Support\Str::slug($catName);
            $item['cat_slug'] = $catSlug;
            $item['category_name'] = $catName;

            if (!isset($categories[$catSlug])) {
                $categories[$catSlug] = $catName;
            }
        }
        unset($item);

        return view('frontend.pages.gallery', compact('settings', 'rawGallery', 'categories'));
    }

    public function contact()
    {
        $settings = \App\Models\Setting::pluck('value', 'key')->toArray();
        return view('frontend.pages.contact', compact('settings'));
    }

    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'email'      => 'required|email|max:150',
            'phone'      => 'nullable|string|max:50',
            'subject'    => 'required|string|max:200',
            'message'    => 'required|string|max:3000',
        ]);

        \App\Models\ContactMessage::create($validated);

        return back()->with('success', 'Thank you! Your message has been sent successfully. We will get back to you soon.');
    }
    public function faculty()
    {
        $teachers = \App\Models\StaffProfile::with('user')->where('status', 'active')->get();
        return view('frontend.pages.faculty', compact('teachers'));
    }

    public function calendar()
    {
        $settings = \App\Models\Setting::where('key', 'like', 'cms_calendar_%')->pluck('value', 'key')->toArray();
        
        // Fetch Exam Types for the sidebar info box
        $examTypes = \App\Models\ExamType::where('status', 'active')->orderBy('start_date')->get();
        
        // Fetch Exam Schedules for the calendar grid
        $examSchedules = \App\Models\ExamSchedule::with(['schoolClass', 'subject', 'examType'])->get();
        
        return view('frontend.pages.calendar', compact('settings', 'examTypes', 'examSchedules'));
    }

    public function principalMessage()
    {
        return view('frontend.pages.principal-message');
    }

    public function result(Request $request)
    {
        $classes = \App\Models\SchoolClass::all();

        $exams = \App\Models\ExamType::where('status', 'active')->get();
        if ($exams->isEmpty()) {
            $exams = \App\Models\ExamType::all();
        }

        $result = null;
        $student = null;
        $error = null;

        if ($request->has('exam_id') && $request->has('class_id') && $request->has('roll')) {
            $studentProfile = \App\Models\StudentProfile::where('class_id', $request->class_id)
                                    ->where('roll_no', $request->roll)
                                    ->first();
            
            if ($studentProfile) {
                // Find marks for this student and exam type
                $marks = \App\Models\MarksEntry::where('student_profile_id', $studentProfile->id)
                            ->whereHas('examSchedule', function($q) use ($request) {
                                $q->where('exam_type_id', $request->exam_id);
                            })
                            ->with('examSchedule.subject')
                            ->get();
                            
                if ($marks->isNotEmpty()) {
                    $student = $studentProfile;
                    $result = $marks;
                } else {
                    $error = "এই পরীক্ষার জন্য কোনো ফলাফল পাওয়া যায়নি।";
                }
            } else {
                $error = "এই রোল নম্বরের কোনো শিক্ষার্থী পাওয়া যায়নি।";
            }
        }

        return view('frontend.pages.result', compact('classes', 'exams', 'result', 'student', 'error'));
    }

    public function resultDownload(Request $request)
    {
        if (!$request->has('exam_id') || !$request->has('class_id') || !$request->has('roll')) {
            return redirect()->route('result')->with('error', 'অসম্পূর্ণ তথ্য। দয়া করে আবার চেষ্টা করুন।');
        }

        $studentProfile = \App\Models\StudentProfile::where('class_id', $request->class_id)
            ->where('roll_no', $request->roll)
            ->first();
            
        if (!$studentProfile) {
            return redirect()->route('result')->with('error', 'এই রোল নম্বরের কোনো শিক্ষার্থী পাওয়া যায়নি।');
        }

        $marks = \App\Models\MarksEntry::where('student_profile_id', $studentProfile->id)
            ->whereHas('examSchedule', function($q) use ($request) {
                $q->where('exam_type_id', $request->exam_id);
            })
            ->with('examSchedule.subject', 'examSchedule.examType', 'examSchedule.schoolClass')
            ->get();
            
        if ($marks->isEmpty()) {
            return redirect()->route('result')->with('error', 'এই পরীক্ষার জন্য কোনো ফলাফল পাওয়া যায়নি।');
        }

        $student = $studentProfile;
        $result = $marks;
        
        $examType = \App\Models\ExamType::find($request->exam_id);
        $schoolClass = \App\Models\SchoolClass::find($request->class_id);

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('frontend.pages.result-pdf', compact('student', 'result', 'examType', 'schoolClass', 'request'));
        $pdf->setPaper('A4', 'portrait');
        $filename = 'Result_' . ($schoolClass->name ?? 'Class') . '_Roll_' . $request->roll . '.pdf';
        
        return $pdf->download($filename);
    }
}
