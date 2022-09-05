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
            'permission_id' => [
                'required',
                Rule::exists('permission_role', 'permission_ref_id')->where('role_ref_id', $this->route('role')->role_id)
            ],
            'conditions.relation' => [
                Rule::requiredIf(fn() => !empty($this->get('conditions'))),
                Rule::in('AND', 'OR')
            ],
            'conditions' => ['array', new ConditionCheckRule($this->get('conditions'))],
            'conditions.actions' => [
                Rule::requiredIf(fn() => !empty($this->get('conditions'))),
                'array'
            ],
            'conditions.actions.*.type' => ['required', Rule::in(['permission'])]
        ];
    }

    public function messages(): array
    {
        return [
            'permission_id.exists' => 'دسترسی انتخاب شده در نقش مورد نظر موجود نمی باشد'
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
