<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeacherRegisterRequest extends FormRequest
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
            'phone' => ['required', 'string'],
            'address' => ['required', 'string'],
            'qualification' => ['required', 'string'],
            'designation' => ['required', 'string'],
            'joining_date' => ['required', 'date'],
            'salary' => ['required', 'numeric', 'min:0'],
        ];
    }
}
