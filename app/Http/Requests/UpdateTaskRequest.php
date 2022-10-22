<?php

namespace App\Http\Requests;

use App\Models\Column;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateTaskRequest extends FormRequest
{
    /**
     * @var mixed
     */
    private $cardTypeFields = [];
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
            'order' => 'array',
            'reviewed_at' => 'date',
            'task_metas' => 'array',
            'task_metas.*.task_key' => 'required|distinct',
            'task_metas.*.task_value' => 'required',
            'task_metas.*.column_ref_id' => 'numeric',
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
        $cardTypeId = $this->route('task')->card_type_ref_id;
        $teamId = $this->get('team_ref_id') ?? $this->route('task')->team_ref_id;
        $this->cardTypeFields = Column::getCardTypeColumns($cardTypeId, $teamId);
        foreach ($this->cardTypeFields as $cardTypeField) {

            $metaItem = $this->checkoutFieldFromMeta($cardTypeField->column_id);

            if ($cardTypeField->nullable == false && !empty($metaItem) && is_null($metaItem['task_value'])) {
                $validationErrors[$cardTypeField->name][] = sprintf(
                    "فیلد %s در الزامی است", $cardTypeField->title);
            }

            if (!empty($cardTypeField->enum_values) && !empty($metaItem['task_value']) && !in_array($metaItem['task_value'], $cardTypeField->enum_values)) {
//                Log::channel('dump_debug')->debug(json_encode($metaItem['task_value']) . "\n" . json_encode($cardTypeField->enum_values));
                $validationErrors[$cardTypeField->name][] = sprintf(
                    "فیلد %s در مقدار معتبری ندارد", $cardTypeField->title);
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
