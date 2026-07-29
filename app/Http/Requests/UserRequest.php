<?php

namespace App\Http\Requests;

use App\Helpers\Qs;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $store =  [
            'name' => 'required|string|min:2|max:150',
            'password' => 'nullable|string|min:3|max:50',
            'user_type' => 'required',
            'gender' => 'required|string|in:Male,Female',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100|unique:users',
            'username' => 'nullable|string|max:100|unique:users',
            'photo' => 'nullable|image|mimes:jpeg,gif,png,jpg|max:2048',
            'address' => 'required|string|min:3|max:255',
            'province' => 'nullable|string',
            'district' => 'nullable|string',
            'medical_history' => 'nullable|string',
            'nal_id' => 'nullable|exists:nationalities,id',
            'section_id' => 'nullable|exists:sections,id',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'exists:subjects,id',
        ];
        // Route param is `{id}` and RouteServiceProvider already hash-decodes it.
        $userId = $this->route('id') ?: 0;
        $update =  [
            'name' => 'required|string|min:6|max:150',
            'gender' => 'required|string',
            'phone' => 'sometimes|nullable|string|min:6|max:20',
            'phone2' => 'sometimes|nullable|string|min:6|max:20',
            'email' => 'sometimes|nullable|email|max:100|unique:users,email,'.$userId,
            'photo' => 'sometimes|nullable|image|mimes:jpeg,gif,png,jpg|max:2048',
            'address' => 'required|string|min:6|max:120',
            'province' => 'nullable|string',
            'district' => 'nullable|string',
            'medical_history' => 'nullable|string',
            'nal_id' => 'required',
        ];
        return ($this->method() === 'POST') ? $store : $update;
    }

    public function attributes()
    {
        return  [
            'nal_id' => 'Nationality',
            'province' => 'Province',
            'district' => 'District',
            'medical_history' => 'Medical History',
            'user_type' => 'User',
            'phone2' => 'Telephone',
        ];
    }

    protected function getValidatorInstance()
    {
        if($this->method() === 'POST'){
            $input = $this->all();

            $input['user_type'] = Qs::decodeHash($input['user_type']);

            $this->getInputSource()->replace($input);

        }

        return parent::getValidatorInstance();

    }
}
