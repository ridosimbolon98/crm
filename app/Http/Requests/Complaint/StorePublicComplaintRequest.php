<?php

namespace App\Http\Requests\Complaint;

use Illuminate\Foundation\Http\FormRequest;

class StorePublicComplaintRequest extends FormRequest
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
            'full_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'gender' => ['nullable', 'in:Male,Female'],
            'birth_date' => ['nullable', 'date'],
            'province' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'district' => ['nullable', 'string', 'max:120'],
            'subdistrict' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:1000'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'complaint_category_id' => ['nullable', 'exists:complaint_categories,id'],
            'purchase_date' => ['nullable', 'date'],
            'purchase_location' => ['nullable', 'string', 'max:255'],
            'production_code' => ['nullable', 'string', 'max:80'],
            'story' => ['required', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimetypes:image/jpeg,image/png,image/webp,video/mp4,application/pdf'],
            'consent' => ['required', 'accepted'],
        ];
    }
}
