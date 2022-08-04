<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserAssignViewRequest extends FormRequest
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
            'users.*' => [Rule::exists('users', 'email')->withoutTrashed()]
        ];
    }

    public function attributes(): array
    {
        return [
            'users.*' => 'کاربر'
        ];
    }
}
