<?php


namespace App\Http\ColumnTypes;


use App\Models\Company;
use App\Models\Personal;
use Illuminate\Auth\Access\AuthorizationException;

abstract class CustomField
{
    protected string $name,$title,$default;
    protected int $personal_ref_id;
    protected bool $nullable;



    public function setDefault(string $default)
    {
        $this->default = $default;
    }





}
