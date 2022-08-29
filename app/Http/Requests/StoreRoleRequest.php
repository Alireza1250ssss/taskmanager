<?php

namespace App\Http\Requests;

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
    public function rules()
    {
        return [
            'name' => $this->isMethod('POST') ? 'required' : 'string' ,
            'permissions' => 'array' ,
            'permissions.*' => ['required',Rule::exists('permissions','permission_id')] ,
            'user_ref_id' => 'required'
        ];
    }
}
