<?php

namespace App\Models\Permissions;

use App\Http\Traits\FilterRecords;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use HasFactory, SoftDeletes, FilterRecords;

    protected $primaryKey = 'permission_id';
    protected $fillable = ['key', 'level', 'type', 'parent_id'];
    public array $filters = ['key', 'level', 'type', 'parent_id'];

    /**
     * @return HasMany
     */
    public function childPermissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'parent_id');
    }

    /**
     * @return BelongsTo
     */
    public function parentPermission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'parent_id');
    }

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'permission_user',
            'permission_ref_id',
            'user_ref_id'
        );
    }
}
