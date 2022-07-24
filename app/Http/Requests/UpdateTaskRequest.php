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

    public function prepareForValidation()
    {
        if ($this->filled('use_logged_in_user') && $this->input('use_logged_in_user') == true && !empty(auth()->user())) {
            $this->merge([
                'user_ref_id' => auth()->user()->user_id
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_ref_id' => [Rule::exists('users', 'user_id')->withoutTrashed()],
            'parent_id' => Rule::exists('tasks', 'task_id')->withoutTrashed(),
            'team_ref_id' => [Rule::exists('teams', 'team_id')->withoutTrashed()],
            'stage_ref_id' => Rule::exists('stages', 'stage_id')->withoutTrashed(),
            'status_ref_id' => Rule::exists('statuses', 'status_id')->withoutTrashed(),
            'real_time' => 'string',
            'estimate_time' => 'string',
            'priority' => 'string',
            'title' => 'string',
            'description' => 'string',
            'labels' => 'string',
            'task_metas' => 'array',
            'task_metas.*.task_key' => 'required|distinct',
            'task_metas.*.task_value' => 'required',
        ];
    }
}
