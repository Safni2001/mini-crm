<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
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
        $employeeId = $this->route('employee')?->id ?? $this->route('employee');

        return [
            'first_name' => 'sometimes|required|string|max:50',
            'last_name' => 'sometimes|required|string|max:50',
            'company_id' => 'sometimes|required|integer|exists:companies,id',
            'email' => 'sometimes|nullable|email|max:255|unique:employees,email,' . $employeeId,
            'phone' => 'sometimes|nullable|string|max:20',
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
