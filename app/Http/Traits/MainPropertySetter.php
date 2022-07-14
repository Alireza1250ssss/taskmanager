<?php


namespace App\Http\Traits;


trait MainPropertySetter
{
    public function setAttributes($data)
    {
        $this->attributes = $data;
    }
}
