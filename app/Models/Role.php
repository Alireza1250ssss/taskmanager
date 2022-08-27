<?php

namespace App\Models;

use App\Http\Traits\FilterRecords;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    /**
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'permission_role',
        'role_ref_id' ,
            'permission_ref_id',
        );
    }

}
