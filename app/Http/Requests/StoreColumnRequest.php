<?php

namespace App\Http\Requests;

use App\Http\ColumnTypes\DropDown;
use App\Models\Personal;
use App\Rules\RelatedCompanyOwner;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StoreColumnRequest extends FormRequest
{
    public static array $types = [
        'dropdown' => DropDown::class
    ];


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
        if ($this->filled('type_args.values'))
            $this->merge([
               'enum_values' => $this->get('type_args')['values']
            ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {

        return [
            'name' => 'required|unique:columns,name',
            'title' => 'required',
            'nullable' => 'boolean',
            'default' => 'string',
            'personal_ref_id' => ['required', new RelatedCompanyOwner(Personal::class)],
            'params' => 'array',
            'enum_values' => 'array',
            'type' => ['required', Rule::in(array_keys(self::$types))],
            'length' => 'numeric',
            'level_type' => 'string',
            'level_id' => 'numeric',
            'type_args' => 'present|array'
        ];
    }

    public function withValidator($validator)
    {
        if ($validator->fails()) return;
        $validator->after(function ($validator) {
            $type = new self::$types[$this->get('type')];
            $typeValidation = Validator::make($this->all(), $type->validation(),[],$type->validationMessages());
            $typeValidation->validate();
        });
    }
}
