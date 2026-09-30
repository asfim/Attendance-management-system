<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BiometricDeviceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'ip_address' => 'required|string|max:100',
            'port' => 'required|integer|min:1|max:65535',
            'location' => 'nullable|string|max:255',
            'device_sn' => 'nullable|string|max:100',
            'status' => 'required|in:online,offline,disabled',
        ];
    }
}
