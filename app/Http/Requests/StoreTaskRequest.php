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
            'user_ref_id' => ['nullable',Rule::exists('users','user_id')->withoutTrashed()],
            'parent_id' => Rule::exists('tasks','task_id')->withoutTrashed() ,
            'team_ref_id' => ['required', Rule::exists('teams','team_id')->withoutTrashed()],
            'description' => 'string',
            'stage_ref_id' => [Rule::exists('stages','stage_id')->withoutTrashed()],
            'status_ref_id' => Rule::exists('statuses','status_id')->withoutTrashed(),
            'real_time' => 'array',
            'estimate_time' => 'string',
            'priority' => 'string',
            'labels' => 'string',
            'due_date' => 'string' ,
            'order'  => 'array' ,
            'task_metas' => 'array' ,
            'task_metas.*.task_key' => 'required|distinct',
            'task_metas.*.task_value' => 'required',
            'watchers' => 'array' ,
            'watchers.*' => [Rule::exists('users', 'user_id')->withoutTrashed()]
        ];
    }
}
