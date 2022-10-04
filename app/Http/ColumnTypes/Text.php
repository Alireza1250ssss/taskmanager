<?php


namespace App\Http\ColumnTypes;


class Text extends CustomField
{

    public function validation(): array
    {
        return [
          'type_args.length' => 'numeric'
        ];
    }

    public function validationMessages(): array
    {
        return [
          'type_args.length' => 'طول(تعداد کاراکترهای مجاز)'
        ];
    }
}
