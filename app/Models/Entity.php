<?php

namespace App\Models;

use App\Http\Traits\FilterRecords;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Entity extends Model
{
    use HasFactory,FilterRecords;

    protected $primaryKey = 'entity_id';
    protected $fillable = ['key','action'];
    public array $filters = [];

    /**
     * @return MorphToMany
     */
    public function roles(): MorphToMany
    {
        return $this->morphToMany(
            Role::class ,
            'permissible',
            'permissibles',
            'permissible_id',
            'role_ref_id'
        )->withPivot(['id']);
    }
}
