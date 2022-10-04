<?php

namespace App\Http\Requests;

use App\Http\ColumnTypes\CustomField;
use App\Http\ColumnTypes\DropDown;
use App\Http\ColumnTypes\Text;
use App\Models\CardType;
use App\Models\Column;
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
            'name' => ['required'],
            'title' => 'required',
            'nullable' => 'boolean',
            'default' => 'string',
            'card_type_ref_id' => ['required', new RelatedCompanyOwner(CardType::class)],
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
            $this->customField = $type;
            $typeValidation = Validator::make($this->all(), $type->validation(), [], $type->validationMessages());
            $typeValidation->validate();
        });
    }


    public function validated(): array
    {
        if (!method_exists($this->customField, 'extractColumn'))
            return parent::validated();
        $typeColumns = $this->customField->extractColumn($this->get('type_args'));
        if (!empty($typeColumns))
            return array_merge(parent::validated(), $typeColumns);
    }
}
