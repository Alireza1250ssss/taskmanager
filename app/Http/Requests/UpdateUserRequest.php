<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
            'email' => ['email',Rule::unique('users')->ignore($this->route()->parameter("user"),'user_id')],
            'phone' => ['size:11',Rule::unique('users')->ignore($this->route()->parameter("user"),'user_id')],
            'password' => 'min:5|confirmed',
            'status' => 'prohibited',
            'username' => Rule::unique('users')->ignore($this->route()->parameter("user"),'user_id') ,
        ];
    }
}
