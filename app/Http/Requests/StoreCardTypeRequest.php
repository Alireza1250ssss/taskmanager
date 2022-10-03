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
            'description' => 'string' ,
            'company_ref_id' => [Rule::requiredIf(fn() => $this->isMethod('POST')),function ($attribute, $value, $fail) {
                $this->relatedCompany = Company::findOrFail($value);
                if (!Company::isCompanyOwner($this->relatedCompany,auth()->user()->user_id))
                    $fail('کمپانی انتخاب شده مربوط به شما نمی باشد');
                }],
            'level_type' => [Rule::requiredIf(fn() => $this->isMethod('POST')),Rule::in(['company','project','team'])],
            'level_id' => [Rule::requiredIf(fn() => $this->filled('level_type')),'numeric',function($attribute,$value,$fail){
                if (!$this->filled('company_ref_id'))
                    $this->relatedCompany = Company::findOrFail($this->route('cardType')->company_ref_id);
                $model = $this->filled('level_type') ?
                    ResolvePermissionController::$models[$this->get('level_type')]['class']::findOrFail($this->get('level_id'))
                    :
                    ResolvePermissionController::$models[$this->route('cardType')->level_type]['class']::findOrFail($this->route('cardType')->level_id);
                if (!$this->relatedCompany->isParentOf($model)) {
                    $fail('سطح انتخابی در کمپانی شما موجود نمی باشد');
                }
            }]
        ];
    }
}
