<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
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
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'company_id' => 'required|integer|exists:companies,id',
            'email' => 'nullable|email|max:255|unique:employees,email',
            'phone' => 'nullable|string|max:20',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'company_id.required' => 'Company selection is required.',
            'company_id.exists' => 'Selected company does not exist.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already in use by another employee.',
        ];
    }
}
