<?php

namespace App\Models;

use App\Http\Controllers\ResolvePermissionController;
use App\Http\Traits\FilterRecords;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Hash;

class Permission extends Model
{
    use HasFactory,FilterRecords;

    protected $primaryKey = 'permission_id';
    protected $fillable = ['key','title','description','category'];
    public array $filters = ['key','title','description','category'];

    /**
     * @return BelongsToMany
     */
    public function role(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'permission_role',
            'permission_ref_id',
            'role_ref_id'
        );
    }

}
