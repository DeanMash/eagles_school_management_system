<?php

namespace App\Http\Requests\TimeTable;

use Illuminate\Foundation\Http\FormRequest;

class TSRequest extends FormRequest
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
        return [
            'ttr_id' => 'required|exists:time_table_records,id',
            'hour_from' => 'required|numeric|between:1,12',
            'min_from' => 'required|string|size:2',
            'meridian_from' => 'required|string|size:2',
            'hour_to' => 'required|numeric|between:1,12',
            'min_to' => 'required|string|size:2',
            'meridian_to' => 'required|string|size:2',
        ];
    }

    public function attributes()
    {
        return  [
            'ttr_id' => 'TimeTable Record',
            'hour_from' => 'Start Hour',
            'min_from' => 'Start Minute',
            'meridian_from' => 'Start Meridian',
            'hour_to' => 'End Hour',
            'min_to' => 'End Minute',
            'meridian_to' => 'End Meridian',
        ];
    }

    public function messages()
    {
        return [
            'ttr_id.required' => 'Timetable record is required.',
            'ttr_id.exists' => 'Selected timetable does not exist.',
            'hour_from.required' => 'Start hour is required.',
            'hour_from.between' => 'Start hour must be between 1 and 12.',
            'min_from.required' => 'Start minute is required.',
            'min_from.size' => 'Start minute must be 2 characters (e.g. 00, 30).',
            'meridian_from.required' => 'Start AM/PM is required.',
            'meridian_from.size' => 'Start meridian must be AM or PM.',
            'hour_to.required' => 'End hour is required.',
            'hour_to.between' => 'End hour must be between 1 and 12.',
            'min_to.required' => 'End minute is required.',
            'min_to.size' => 'End minute must be 2 characters (e.g. 00, 30).',
            'meridian_to.required' => 'End AM/PM is required.',
            'meridian_to.size' => 'End meridian must be AM or PM.',
        ];
    }

}
