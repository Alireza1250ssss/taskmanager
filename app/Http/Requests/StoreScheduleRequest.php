<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
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
        return [
            'user_ref_id' => ['required' , Rule::exists('users','user_id')->withoutTrashed()],
            'time_from' => 'required|date_format:H:i',
            'time_to' => 'required|date_format:H:i|after:time_from',
            'day' => ['required' , Rule::in(['saturday','sunday','monday','tuesday','wednesday','thursday','friday'])],
            'type' => ['required' , Rule::in(['remote','face_to_face'])]
        ];
    }
}
