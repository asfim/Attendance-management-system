<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MarksEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'exam_schedule_id' => ['required', 'exists:exam_schedules,id'],
            'marks' => ['required', 'array'],
            'marks.*.student_profile_id' => ['required', 'exists:student_profiles,id'],
            'marks.*.marks_obtained' => ['nullable', 'numeric', 'min:0'],
            'marks.*.attendance_status' => ['required', 'in:present,absent'],
            'marks.*.remarks' => ['nullable', 'string', 'max:255'],
        ];
    }
}
