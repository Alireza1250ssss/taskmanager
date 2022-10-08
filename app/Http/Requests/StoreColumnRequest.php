<?php

namespace App\Http\Requests;

use App\Http\ColumnTypes\CustomField;
use App\Http\ColumnTypes\DropDown;
use App\Http\ColumnTypes\Text;
use App\Models\CardType;
use App\Rules\RelatedCompanyOwner;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StoreColumnRequest extends FormRequest
{
    public static array $types = [
        'dropdown' => DropDown::class,
        'text' => Text::class
    ];
    public CustomField $customField;


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
    public function rules(): array
    {

        return [
            'name' => [$this->isMethod('POST') ? 'required' : 'string'],
            'title' => [$this->isMethod('POST') ? 'required' : 'string'],
            'nullable' => 'boolean',
            'show' => 'boolean',
            'default' => 'string',
            'card_type_ref_id' => [
                $this->isMethod('POST') ? 'required' : 'numeric',
                new RelatedCompanyOwner(CardType::class)
            ],
            'params' => 'array',
            'enum_values' => 'array',
            'type' => [
                $this->isMethod('POST') ? 'required' : 'prohibited',
                Rule::in(array_keys(self::$types))
            ],
            'length' => 'numeric',
            'level_type' => 'string',
            'level_id' => 'numeric',
            'type_args' => $this->isMethod('POST') ? 'present|array' : 'array'
        ];
    }

    public function withValidator($validator)
    {
        if ($validator->fails()) return;
        $validator->after(function ($validator) {
            $type = new self::$types[$this->get('type') ?? $this->route('column')->type];
            $this->customField = $type;
            if ($this->filled('type_args')) {
                $typeValidation = Validator::make($this->all(), $type->validation(), [], $type->validationMessages());
                $typeValidation->validate();
            }
        });
    }


    public function validated(): array
    {
        if (!method_exists($this->customField, 'extractColumn'))
            return parent::validated();
        $typeColumns = $this->customField->extractColumn($this->get('type_args',[]));
        if (!empty($typeColumns))
            return array_merge(parent::validated(), $typeColumns);
    }
}
