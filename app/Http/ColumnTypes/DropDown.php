<?php


namespace App\Http\ColumnTypes;


use Illuminate\Http\Request;

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

    public function extractColumn(array $data): array
    {
        $result = [];
        if (array_key_exists('values',$data))
            $result['enum_values'] = $data['values'];
        return $result;
    }
}
