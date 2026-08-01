<?php

namespace App\Http\Requests\Student;

use App\Helpers\Qs;
use Illuminate\Foundation\Http\FormRequest;

class StudentRecordCreateRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female',
            'address' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'nullable|string',
            'phone2' => 'nullable|string',
            'dob' => 'nullable|date|before:today',
            'province' => 'nullable|string',
            'district' => 'nullable|string',
            'nationality' => 'required|string|in:Zimbabwean,Other',
            'photo' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
            'medical_history' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string',
            'emergency_contact_phone' => 'nullable|string',
            'emergency_contact_relationship' => 'nullable|string',
            
            // Student Data Section
            'my_class_id' => 'required|exists:my_classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'my_parent_id' => 'nullable|exists:users,id',
            'adm_no' => 'nullable|unique:student_records,adm_no|regex:/^[A-Z0-9]{4,10}$/',
            'admission_date' => 'required|date|before_or_equal:today',
            'year_admitted' => 'nullable|digits:4|min:2000|max:' . date('Y'),
            'dorm_id' => 'nullable|exists:dorms,id',
            'dorm_room_no' => 'nullable|string',
            'house' => 'nullable|string',
            
            // Old fields for backward compatibility
            'nal_id' => 'nullable|exists:nationalities,id',
            'state_id' => 'nullable|exists:states,id',
            'lga_id' => 'nullable|exists:lgas,id',
            'bg_id' => 'nullable|exists:blood_groups,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'photo.mimes' => 'The photo must be a file of type: jpeg, png, jpg, gif.',
            'photo.max' => 'The photo must not be larger than 2MB.',
            'adm_no.regex' => 'Admission number must be 4-10 characters long and contain only uppercase letters and numbers.',
        ];
    }

    /**
     * Views submit Hashids-encoded parent IDs; decode before validation/persistence.
     */
    protected function getValidatorInstance()
    {
        $input = $this->all();
        $input['my_parent_id'] = !empty($input['my_parent_id'])
            ? Qs::decodeHash($input['my_parent_id'])
            : null;
        $this->getInputSource()->replace($input);

        return parent::getValidatorInstance();
    }
}