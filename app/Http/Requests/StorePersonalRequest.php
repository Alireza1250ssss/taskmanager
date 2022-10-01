<?php

namespace App\Http\Requests;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePersonalRequest extends FormRequest
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
            'name' => $this->isMethod('POST') ? 'required' : 'string',
            'description' => 'string' ,
            'company_ref_id' => [Rule::requiredIf(fn() => $this->isMethod('POST')),function ($attribute, $value, $fail) {
                if (!Company::isCompanyOwner(Company::findOrFail($value),auth()->user()->user_id))
                    $fail('کمپانی انتخاب شده معتبر نمی باشد');
                }]
        ];
    }
}
