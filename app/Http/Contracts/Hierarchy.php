<?php


namespace App\Http\Contracts;


use Illuminate\Database\Eloquent\Model;

interface Hierarchy
{
    public function IsParentOf(Model $model) : bool;
}
