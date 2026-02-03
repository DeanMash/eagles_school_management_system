<?php

namespace App\Http\Requests\TimeTable;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TTRecordRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules()
    {
        if($this->method() === 'POST'){
            return [
                'name' => [
                    'required',
                    'string',
                    'min:3',
                    Rule::unique('time_table_records', 'name')
                        ->where(function ($query) {
                            return $query->where('year', \App\Helpers\Qs::getCurrentSession());
                        })
                ],
                'my_class_id' => 'required|exists:my_classes,id',
            ];
        }

        return [
            'name' => [
                'required',
                'string',
                'min:3',
                Rule::unique('time_table_records', 'name')->ignore($this->ttr)
            ],
            'my_class_id' => 'required|exists:my_classes,id',
        ];
    }
    
    public function messages()
    {
        return [
            'name.required' => 'Timetable name is required.',
            'name.min' => 'Timetable name must be at least 3 characters.',
            'name.unique' => 'A timetable with this name already exists for the current year.',
            'my_class_id.required' => 'Please select a class.',
            'my_class_id.exists' => 'The selected class does not exist.',
        ];
    }

    public function attributes()
    {
        return  [
            'my_class_id' => 'Class',
        ];
    }

}
