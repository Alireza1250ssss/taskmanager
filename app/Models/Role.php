<?php

namespace App\Models;

use App\Http\Traits\FilterRecords;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Role extends Model
{
    use HasFactory,FilterRecords;

    protected $primaryKey = 'role_id';
    protected $fillable = ['name'];
    public array $filters = ['name'];

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
          User::class ,
          'role_user' ,
          'role_ref_id' ,
          'user_ref_id' ,
        );
    }

    /*
     * --------- polymorphic permissions defined here with MorphedByMany return type -------------------
     */


    /**
     * @return MorphToMany
     */
    public function entities(): MorphToMany
    {
        return $this->morphedByMany(
            Entity::class ,
            'permissible' ,
            'permissibles' ,
            'role_ref_id'
        )->withPivot(['id']);
    }

    /**
     * @return MorphToMany
     */
    public function fields(): MorphToMany
    {
        return $this->morphedByMany(
            Field::class ,
            'permissible' ,
            'permissibles' ,
            'role_ref_id'
        )->withPivot(['id','parent_id']);
    }
}
