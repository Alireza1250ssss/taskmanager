<?php


namespace App\Http\Traits;


trait MainPropertyGetter
{
    public function getFillable()
    {
        return $this->fillable;
    }

    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

}
