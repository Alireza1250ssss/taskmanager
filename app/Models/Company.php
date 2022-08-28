<?php

namespace App\Models;

use App\Http\Contracts\Hierarchy;
use App\Http\Traits\FilterRecords;
use App\Http\Traits\HasMembers;
use App\Http\Traits\MainPropertyGetter;
use App\Http\Traits\MainPropertySetter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model implements Hierarchy
{
    use HasFactory,SoftDeletes,FilterRecords,MainPropertyGetter,MainPropertySetter,HasMembers;

    protected $fillable = ['name'];
    protected $primaryKey = 'company_id';
    public array $filters = ['name'];
    protected $hidden = ['created_at','updated_at'];

    /**
     * @return HasMany
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class,'company_ref_id');
    }

    /**
     * @return MorphToMany
     */
    public function watchers(): MorphToMany
    {
        return $this->morphToMany(
            User::class ,
            'watchable',
            'watchers' ,
            'watchable_id',
            'user_ref_id'
        );
    }

    public function IsParentOf(Model $model): bool
    {
        if ($model instanceof Task)
            return $this->company_id == $model->team->project->company->company_id;
        elseif ($model instanceof Team)
            return $this->company_id == $model->project->company->company_id;
        elseif ($model instanceof Project)
            return $this->company_id == $model->company->company_id;
        return false;
    }
}
