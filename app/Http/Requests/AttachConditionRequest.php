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
            'conditions.relation' => [
                Rule::requiredIf(fn() => $this->filled('conditions')),
                Rule::in('AND', 'OR')
            ],
            'conditions' => ['array', new ConditionCheckRule($this->get('conditions'))],
            'actions' => [
                Rule::requiredIf(fn() => $this->filled('conditions')),
                'array'
            ],
            'actions.*.type' => ['required', Rule::in(['permission'])]
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
