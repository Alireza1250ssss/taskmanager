<?php


namespace App\Http\Contracts;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface Hierarchy
{
    public function IsParentOf(Model $model) : bool;

    public static function getHierarchyItems(Model $model) : Collection;
}
