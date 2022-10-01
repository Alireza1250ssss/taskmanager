<?php


namespace App\Http\ColumnTypes;


class DropDown extends CustomField
{
    protected array $values;



    public function validation(): array
    {
        return [
            'values' => 'required|array'
        ];
    }

    public function getEnumValues($data)
    {
        return $data['values'];
    }
}
