<?php


namespace App\Http\ColumnTypes;


class DropDown extends CustomField
{
    protected array $values;



    public function validation(): array
    {
        return [
            'type_args.values' => 'required|array'
        ];
    }

    public function validationMessages(): array
    {
        return [
          'type_args.values' => 'مقادیر ممکن'
        ];
    }
}
