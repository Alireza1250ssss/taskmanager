<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

class StoreLeaveRequest extends FormRequest
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

    protected function prepareForValidation()
    {
        $this->merge([
            'params' => $this->get('schedule'),
        ]);
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
            'time_to' => 'required|date_format:H:i|after:time_from' ,
            'status' =>
                str_contains(Route::currentRouteName(),'leave.update') ?
                Rule::in(['accepted','refused','pending']) :
                'prohibited'
            ,
            'type' => ['required' , Rule::in(['remote','off','alternative'])] ,
            'schedule' => [
                Rule::requiredIf($this->get('type') == 'alternative' || $this->get('type') == 'remote'),
                'array'
            ],
            'schedule.*.time_from' => ['required','date_format:H:i'],
            'schedule.*.time_to' => ['required','date_format:H:i','after:schedule.*.time_from'],
            'schedule.*.day' => ['required','date_format:Y-m-d'],
            'schedule.*.type' => ['required',Rule::in(['remote','face_to_face'])],
            'params' => 'array',
        ];
    }
}
