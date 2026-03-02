<?php

namespace App\Http\Requests\Complaint;

use App\Models\Complaint;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreComplaintRequest extends FormRequest
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
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'complaint_category_id' => ['nullable', 'exists:complaint_categories,id'],
            'complaint_channel' => ['required', 'in:'.implode(',', Complaint::CHANNEL_OPTIONS)],
            'production_code' => ['nullable', 'string', 'max:80'],
            'complaint_date' => ['required', 'date'],
            'severity' => ['required', Rule::exists('complaint_severities', 'name')->where('is_active', true)],
            'status' => ['required', 'in:'.implode(',', Complaint::STATUS_OPTIONS)],
            'assigned_to' => ['nullable', 'string', 'max:120'],
            'target_resolution_date' => ['nullable', 'date'],
            'description' => ['required', 'string', 'max:5000'],
            'author' => ['nullable', 'string', 'max:120'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimetypes:image/jpeg,image/png,image/webp,video/mp4,application/pdf'],
        ];
    }
}
