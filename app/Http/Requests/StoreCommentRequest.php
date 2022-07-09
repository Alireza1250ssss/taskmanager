<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommentRequest extends FormRequest
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
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'user_ref_id' => auth()->user()->user_id ,
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
            'content' => $this->isMethod('post') ? 'required' : '' ,
            'user_ref_id' => ['required',Rule::exists('users','user_id')->withoutTrashed()] ,
            'parent_id' => Rule::exists('comments','comment_id')->withoutTrashed() ,
        ];
    }
}
