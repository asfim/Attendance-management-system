<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FeeInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_id' => ['required', 'exists:classes,id'],
            'fee_category_id' => ['required', 'exists:fee_categories,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }
}
