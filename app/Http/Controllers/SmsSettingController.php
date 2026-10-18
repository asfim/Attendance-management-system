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
        
        $staffMembers = \App\Models\StaffProfile::with(['user', 'departmentRel', 'designationRel'])
            ->where(function ($q) use ($query) {
                $q->whereHas('user', function ($sub) use ($query) {
                    $sub->where('name', 'like', "%{$query}%");
                })
                ->orWhere('phone', 'like', "%{$query}%")
                ->orWhere('biometric_id', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get();

        $results = [];
        foreach ($staffMembers as $staff) {
            $phone = $staff->phone ?? '';
            $dept = $staff->departmentRel->name ?? $staff->department ?? 'General';
            $desig = $staff->designationRel->title ?? $staff->designation ?? 'Staff';
            $name = $staff->user->name ?? 'Employee';
            $bioId = $staff->biometric_id ? " [ID: {$staff->biometric_id}]" : '';
            
            $text = "{$name}{$bioId} - {$desig} ({$dept})";
            if ($phone) {
                $text .= " | {$phone}";
            }

            if ($phone) {
                $results[] = [
                    'id' => $phone,
                    'text' => $text,
                    'phone' => $phone,
                    'image' => null
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
            if (is_numeric($input) && strlen($input) < 10) {
                $staff = \App\Models\StaffProfile::find($input);
                if ($staff && $staff->phone) {
                    $resolvedPhones[] = $staff->phone;
                }
            } else {
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
