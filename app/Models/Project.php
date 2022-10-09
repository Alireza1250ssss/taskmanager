<?php

namespace App\Models;

use App\Http\Contracts\Hierarchy;
use App\Http\Traits\FilterRecords;
use App\Http\Traits\HasMembers;
use App\Http\Traits\MainPropertyGetter;
use App\Http\Traits\MainPropertySetter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model implements Hierarchy
{
    use HasFactory, SoftDeletes, FilterRecords,MainPropertyGetter,MainPropertySetter,HasMembers;

    protected $primaryKey = 'project_id';
    protected $fillable = ['name','company_ref_id'];
    public array $filters = ['name','company_ref_id'];
    protected $hidden = ['created_at','updated_at','deleted_at'];

    /**
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_ref_id');
    }

    /**
     * @return HasMany
     */
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class, 'project_ref_id');
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
            return $this->project_id == $model->team->project->project_id;
        elseif ($model instanceof Team)
            return $this->project_id == $model->project->project_id;
        elseif ($model instanceof Project)
            return $this->project_id == $model->project_id;
        return false;
    }

    public static function getHierarchyItems(Model $model): Collection
    {
        $result = new Collection();
        if ($model instanceof Company)
            $result = $model->projects;
        elseif ($model instanceof Project)
            $result->push($model);
        elseif ($model instanceof Team)
            $result->push($model->project);
        return $result;
    }
}
