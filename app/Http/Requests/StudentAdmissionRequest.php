<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentAdmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'roll_no' => ['required', 'string', 'max:20'],
            'biometric_id' => ['nullable', 'string', 'max:100'],
            'session_id' => ['required', 'exists:academic_sessions,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'section_id' => ['required', 'exists:sections,id'],
            'shift_id' => ['nullable', 'exists:shifts,id'],
            'admission_no' => ['required', 'string', 'unique:student_profiles,admission_no'],
            'admission_date' => ['required', 'date'],
            'dob' => ['required', 'date'],
            'gender' => ['required', 'in:Male,Female,Other'],
            'blood_group' => ['nullable', 'string', 'max:5'],
            'medical_info' => ['nullable', 'string'],
            'parent_name' => ['required', 'string', 'max:255'],
            'parent_email' => ['required', 'email'],
            'parent_phone' => ['required', 'string'],
            'parent_occupation' => ['nullable', 'string'],
            'parent_address' => ['required', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'signature' => ['nullable', 'image', 'max:1024'],
            'assign_hostel' => ['nullable', 'boolean'],
            'hostel_id' => ['nullable', 'required_if:assign_hostel,1'],
            'room_id' => ['nullable', 'required_if:assign_hostel,1'],
            'bed_id' => ['nullable', 'required_if:assign_hostel,1'],
        ];
    }
}
