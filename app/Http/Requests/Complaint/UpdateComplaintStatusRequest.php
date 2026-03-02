<?php

namespace App\Http\Requests\Complaint;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateComplaintStatusRequest extends FormRequest
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
            'status' => ['required', 'in:'.implode(',', Complaint::STATUS_OPTIONS)],
            'assigned_to' => ['nullable', 'string', 'max:120'],
            'target_resolution_date' => ['nullable', 'date'],
            'resolution_summary' => ['nullable', 'string', 'max:5000'],
            'compensation_type' => ['nullable', 'string', 'max:60'],
            'detail_progress' => ['required', 'string', 'max:5000'],
            'pool_to_department' => ['nullable', 'in:'.implode(',', User::DEPARTMENT_OPTIONS)],
            'author' => ['nullable', 'string', 'max:120'],
        ];
    }
}
