<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SmsSettingController extends Controller
{
    public function index()
    {
        $setting = \App\Models\SmsSetting::first();
        return view('admin.sms_configuration.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'is_active' => 'nullable|boolean',
            'gateway_provider' => 'required|string',
            'api_key' => 'required|string',
            'sender_id' => 'nullable|string',
            'custom_api_url' => 'nullable|string',
            'entry_message_template' => 'nullable|string',
            'exit_message_template' => 'nullable|string',
        ]);

        $setting = \App\Models\SmsSetting::first() ?? new \App\Models\SmsSetting();
        $setting->is_active = $request->has('is_active');
        $setting->gateway_provider = $request->input('gateway_provider');
        $setting->api_key = $request->input('api_key');
        $setting->sender_id = $request->input('sender_id');
        $setting->custom_api_url = $request->input('custom_api_url');
        $setting->entry_message_template = $request->input('entry_message_template');
        $setting->exit_message_template = $request->input('exit_message_template');
        $setting->save();

        return back()->with('success', 'SMS Configuration updated successfully!');
    }

    public function customSmsForm()
    {
        return view('admin.sms_configuration.custom');
    }

    public function searchStudent(Request $request)
    {
        $query = $request->get('q');
        
        $students = \App\Models\StudentProfile::with(['user', 'parent', 'schoolClass', 'section'])
            ->whereHas('user', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            })
            ->orWhere('roll_no', 'like', "%{$query}%")
            ->orWhereHas('parent', function ($q) use ($query) {
                $q->where('phone', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        $results = [];
        foreach ($students as $student) {
            $parentPhone = $student->parent ? $student->parent->phone : '';
            $className = $student->schoolClass ? $student->schoolClass->name : '';
            $sectionName = $student->section ? $student->section->name : '';
            
            $text = "{$student->user->name} (Roll: {$student->roll_no})";
            if ($className) $text .= " - {$className}";
            if ($sectionName) $text .= "({$sectionName})";
            if ($parentPhone) $text .= " | Guardian: {$parentPhone}";
            
            $imageUrl = $student->photoUrl();

            if ($parentPhone) {
                $results[] = [
                    'id' => $student->id, // Use student ID to allow selecting siblings uniquely
                    'text' => $text,
                    'phone' => $parentPhone,
                    'image' => $imageUrl
                ];
            }
        }

        return response()->json(['results' => $results]);
    }

    public function sendCustomSms(Request $request)
    {
        $request->validate([
            'phones' => 'required|array',
            'message' => 'required|string'
        ]);

        $smsService = new \App\Services\SmsService();
        $successCount = 0;
        
        $resolvedPhones = [];
        foreach ($request->phones as $input) {
            // If the input is a small number, it's likely a student ID
            if (is_numeric($input) && strlen($input) < 10) {
                $student = \App\Models\StudentProfile::with('parent')->find($input);
                if ($student && $student->parent && $student->parent->phone) {
                    $resolvedPhones[] = $student->parent->phone;
                }
            } else {
                // Otherwise it's a manually entered phone number
                $resolvedPhones[] = $input;
            }
        }

        $uniquePhones = array_unique(array_filter($resolvedPhones));

        foreach ($uniquePhones as $phone) {
            $success = $smsService->sendSms($phone, $request->message);
            if ($success) {
                $successCount++;
            }
        }

        if ($successCount > 0) {
            return back()->with('success', "SMS sent successfully to {$successCount} recipient(s).");
        } else {
            return back()->with('error', 'Failed to send SMS. Please check your SMS configuration.');
        }
    }
}
