<?php


namespace App\Http\ColumnTypes;


use App\Models\Company;
use App\Models\CardType;
use Illuminate\Auth\Access\AuthorizationException;

abstract class CustomField
{
    protected string $name,$title,$default;
    protected bool $nullable;

    abstract public function validation(): array;
    abstract public function validationMessages(): array;


}
