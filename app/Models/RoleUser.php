<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RoleUser extends Pivot
{
    protected $fillable = ['rolable_type' , 'rolable_id' , 'role_ref_id' , 'user_ref_id' , 'parent_id'];
    public $incrementing = true;
}
