<?php

namespace App\Http\Requests;

use App\Models\Stage;
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

        if ($this->filled('real_time')) {
            $beforeRealTime = $this->route('task')->real_time;

            $this->merge([
                'real_time' => empty($beforeRealTime) ?
                    [$this->get('real_time')]
                    :
                    [...$beforeRealTime, $this->get('real_time')]
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {

        return [
            'user_ref_id' => ['nullable', Rule::exists('users', 'user_id')->withoutTrashed()],
            'parent_id' => Rule::exists('tasks', 'task_id')->withoutTrashed(),
            'team_ref_id' => [Rule::exists('teams', 'team_id')->withoutTrashed()],
            'stage_ref_id' => [Rule::exists('stages', 'stage_id')->withoutTrashed()],
            'status_ref_id' => Rule::exists('statuses', 'status_id')->withoutTrashed(),
            'card_type_ref_id' => ['prohibited'],
            'real_time' => ['array'],
            'estimate_time' => 'string',
            'priority' => 'string',
            'title' => 'string',
            'description' => 'string',
            'labels' => 'string',
            'due_date' => 'string',
            'order' => 'array' ,
            'reviewed_at' => 'date',
            'task_metas' => 'array',
            'task_metas.*.task_key' => 'required|distinct',
            'task_metas.*.task_value' => 'required',
            'task_metas.*.column_ref_id' => 'numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'prohibited_if' => 'این فیلد قبلا وارد شده است'
        ];
    }
}
