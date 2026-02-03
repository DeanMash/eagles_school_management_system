<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class TeacherCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'province' => 'nullable|string',
            'district' => 'nullable|string',
            'medical_history' => 'nullable|string',
            'username' => 'nullable|string|unique:users,username',
            'password' => 'nullable|string|min:6|max:50',
            'photo' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
            'section_id' => 'nullable|exists:sections,id',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'exists:subjects,id',
        ];
    }

    public function messages(): array
    {
        return [
            'photo.mimes' => 'The photo must be a file of type: jpeg, png, jpg, gif.',
            'photo.max' => 'The photo must not be larger than 2MB.',
        ];
    }
}
