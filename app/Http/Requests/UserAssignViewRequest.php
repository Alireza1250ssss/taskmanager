<?php

namespace App\Http\Requests;

use App\Http\Controllers\ResolvePermissionController;
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

    public function withValidator($validator)
    {
       $validator->after(function ($validator){
           if (!in_array($this->route('model'), array_keys(ResolvePermissionController::$models)))
               $validator->errors()->add('model','برای موجودیت انتخابی عضو تعیین نمی شود');
       });
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'users.*' => [Rule::exists('users', 'email')->withoutTrashed()],
            'roles' => $this->isMethod('PUT') ? 'array' : 'required|filled|array' ,
            'roles.*' => [Rule::exists('roles','role_id')]
        ];
    }

    public function attributes(): array
    {
        return [
            'users.*' => 'کاربر',
            'roles' => 'نقش'
        ];
    }
}
