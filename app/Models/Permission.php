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
    protected $fillable = ['model','action','role_ref_id'];
    public array $filters = ['model','action'];

    /**
     * @return BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'permission_ref_id', 'role_ref_id');
    }

    /**
     * set qualified namespace name of the model (replace this with the model string coming from request)
     * @return Attribute
     */
    protected function model(): Attribute
    {
        return Attribute::set(fn($value) => ResolvePermissionController::$models[$value]['class']);
    }
}
