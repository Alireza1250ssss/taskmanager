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
            'permissions.*.action' => ['required',Rule::in(['read','create','update','delete'])] ,
            'permissions.*.model' => ['required' , Rule::in(['company','project','team','task'])]
        ];
    }
}
