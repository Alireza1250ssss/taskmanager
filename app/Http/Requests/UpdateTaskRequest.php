<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
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
            'user_ref_id' => [Rule::exists('users','user_id')->withoutTrashed()],
            'parent_id' => Rule::exists('tasks','task_id')->withoutTrashed() ,
            'team_ref_id' => [ Rule::exists('teams','team_id')->withoutTrashed()] ,
            'stage_ref_id' => Rule::exists('stages','stage_id')->withoutTrashed(),
            'status_ref_id' => Rule::exists('statuses','status_id')->withoutTrashed(),
            'task_metas' => 'array' ,
            'task_metas.*.task_key' => 'required|distinct',
            'task_metas.*.task_value' => 'required',
        ];
    }
}
