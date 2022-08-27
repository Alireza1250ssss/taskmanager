<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignRoleRequest extends FormRequest
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
            'email' => ['required',Rule::exists('users','email')->withoutTrashed()],
            'role_ref_id' => ['required',Rule::exists('roles','role_id')],
            'rolable_type' => ['required',Rule::in(['company','project','team','task'])] ,
            'rolable_id'  => 'required'
        ];
    }
}
