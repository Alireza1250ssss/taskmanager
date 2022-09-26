<?php

namespace App\Http\Requests;

use App\Rules\ConditionCheckRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachConditionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'permission_id' => 'required',
            'version' => 'required',
            'conditions' => ['array'],
            'conditions.*.then' => [Rule::requiredIf(fn() => $this->filled('conditions'))],
            'conditions.*.then.*.when' => ['array', new ConditionCheckRule()],
            'conditions.*.when.relation' => [
                Rule::requiredIf(fn() => $this->filled('conditions')),
                Rule::in('AND', 'OR')
            ],
            'conditions.*.then.*.type' => ['required', Rule::in(['permission','validation'])],
            'conditions.*.when' => ['array','required', new ConditionCheckRule()],
        ];
    }



    public function attributes()
    {
        return [
            'conditions.relation' => 'رابطه شرط',
            'conditions.actions.*.type' => 'عملیات'
        ];
    }

}
