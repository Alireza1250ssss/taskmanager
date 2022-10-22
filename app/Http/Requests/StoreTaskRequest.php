<?php

namespace App\Http\Requests;

use App\Models\Column;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreTaskRequest extends FormRequest
{
    public $cardTypeFields = [];
    protected $stopOnFirstFailure = true;
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
            'title' => 'required',
            'card_type_ref_id' => ['required','numeric'],
            'user_ref_id' => ['nullable', Rule::exists('users', 'user_id')->withoutTrashed()],
            'parent_id' => Rule::exists('tasks', 'task_id')->withoutTrashed(),
            'team_ref_id' => ['required', Rule::exists('teams', 'team_id')->withoutTrashed()],
            'description' => 'string',
            'stage_ref_id' => [Rule::exists('stages', 'stage_id')->withoutTrashed()],
            'status_ref_id' => Rule::exists('statuses', 'status_id')->withoutTrashed(),
            'real_time' => 'array',
            'estimate_time' => 'string',
            'priority' => 'string',
            'labels' => 'string',
            'due_date' => 'string',
            'order' => 'array',
            'task_metas' => 'array',
            'task_metas.*.task_key' => 'required|distinct',
            'task_metas.*.task_value' => 'required',
            'task_metas.*.column_ref_id' => ['numeric'],
            'watchers' => 'array',
            'watchers.*' => [Rule::exists('users', 'user_id')->withoutTrashed()]
        ];
    }

    public function withValidator($validator)
    {
        if ($validator->fails()) return;
        $validator->after(function ($validator) {
            $errors = $this->getCardTypeValidation();
            if (!empty($errors))
                throw ValidationException::withMessages($errors);
        });
    }

    protected function getCardTypeValidation(): array
    {
        $validationErrors = [];

        if ($this->filled('card_type_ref_id') && $this->filled('team_ref_id'))
            $this->cardTypeFields = Column::getCardTypeColumns($this->get('card_type_ref_id'), $this->get('team_ref_id'));
        foreach ($this->cardTypeFields as $cardTypeField) {

            $metaItem = $this->checkoutFieldFromMeta($cardTypeField->column_id);

            if ($cardTypeField->nullable == false && (empty($metaItem) || empty($metaItem['task_value']))) {
                $validationErrors[$cardTypeField->name][] = sprintf(
                    "فیلد %s الزامی است", $cardTypeField->title);
            }

            if (!empty($cardTypeField->enum_values) && !empty($metaItem['task_value']) && !in_array($metaItem['task_value'],$cardTypeField->enum_values)){
//                Log::channel('dump_debug')->debug(json_encode($metaItem['task_value'])."\n".json_encode($cardTypeField->enum_values));
                $validationErrors[$cardTypeField->name][] = sprintf(
                    "فیلد %s مقدار معتبری ندارد", $cardTypeField->title);
            }


        }
        return $validationErrors;
    }

    public function checkoutFieldFromMeta(int $columnId)
    {
        foreach ($this->get('task_metas', []) as $item) {
            if (isset($item['column_ref_id']) && $item['column_ref_id'] == $columnId) {
                $res = $item;
                break;
            }
        }
        return $res ?? null;
    }
}
