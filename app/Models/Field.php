<?php

namespace App\Models;

use App\Http\Traits\FilterRecords;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Field extends Model
{
    use HasFactory,FilterRecords;

    protected $primaryKey = 'field_id';
    protected $fillable = ['name','model'];

    /**
     * @return MorphToMany
     */
    public function users(): MorphToMany
    {
        return $this->morphToMany(
            User::class ,
            'permissible',
            'permissibles' ,
            'permissible_id' ,
            'user_ref_id'
        )->withPivot(['id','parent_id']);
    }
}
