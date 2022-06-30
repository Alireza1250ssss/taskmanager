<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
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
            'title' =>'required' ,
            'user_ref_id' => ['required' ,Rule::exists('users','user_id')->withoutTrashed()],
            'parent_id' => Rule::exists('tasks','task_id')->withoutTrashed() ,
            'teams' => 'required|array' ,
            'teams.*' => Rule::exists('teams','team_id')->withoutTrashed(),
            'description' => 'required'
        ];
    }
}
