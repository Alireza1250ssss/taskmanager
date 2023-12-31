<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeamRequest extends FormRequest
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
            'name' => 'required',
            'project_ref_id' => ['required',Rule::exists('projects','project_id')->withoutTrashed()],
            'sprint_start_date' => 'string' ,
            'sprint_period' => 'string' ,
            'git_repo' => 'string|url' ,
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'project_ref_id' => 'پروژه مربوطه',
        ];
    }
}
