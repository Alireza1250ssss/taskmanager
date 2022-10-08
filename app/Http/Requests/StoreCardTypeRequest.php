<?php

namespace App\Http\Requests;

use App\Http\Controllers\ResolvePermissionController;
use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCardTypeRequest extends FormRequest
{

    protected ?Company $relatedCompany;
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
            'description' => 'nullable|string' ,
            'company_ref_id' => [Rule::requiredIf(fn() => $this->isMethod('POST')),function ($attribute, $value, $fail) {
                $this->relatedCompany = Company::findOrFail($value);
                if (!Company::isCompanyOwner($this->relatedCompany,auth()->user()->user_id))
                    $fail('کمپانی انتخاب شده مربوط به شما نمی باشد');
                }],

        ];
    }
}
