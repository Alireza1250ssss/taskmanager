<?php

namespace App\Http\Requests;

use App\Http\Controllers\RoleController;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
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
        $this->merge([
           'user_ref_id' => auth()->user()->user_id
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $categories = array_keys(RoleController::LEVELS);
        return [
            'name' => $this->isMethod('POST') ? 'required' : 'string' ,
            'category' => $this->isMethod('POST') ?
                ['required',Rule::in($categories)] : [Rule::in($categories)] ,
            'permissions' => 'array' ,
            'permissions.*.permission_id' => ['required',Rule::exists('permissions','permission_id')] ,
            'permissions.*.access' => ['required',Rule::in('accept','reject')],
            'user_ref_id' => 'required'
        ];
    }

    public function attributes(): array
    {
        return [
            'permissions.*.permission_id' => 'شماره دسترسی',
            'permissions.*.access' => 'وضعیت'
        ];
    }
}
