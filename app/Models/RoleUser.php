<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RoleUser extends Pivot
{
    protected $fillable = ['rolable_type' , 'rolable_id' , 'role_ref_id' , 'user_ref_id' ];
    public $incrementing = true;

    public static function getBaseRolesOfUser($userId,$category = false)
    {
        return static::query()->where([
            'user_ref_id' => $userId,
            'role_ref_id' => Role::query()->where('name','base-role')->firstOrFail()->role_id
        ])->when($category,function ($query,$category){
            $query->where('rolable_type',$category);
        })->get();
    }
}
