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
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model implements Hierarchy
{
    use HasFactory, SoftDeletes, FilterRecords, MainPropertyGetter, MainPropertySetter, HasMembers;

    protected $fillable = ['name'];
    protected $primaryKey = 'company_id';
    public array $filters = ['name'];
    protected $hidden = ['created_at', 'updated_at','deleted_at'];

    /**
     * @return HasMany
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'company_ref_id');
    }

    /**
     * @return MorphToMany
     */
    public function watchers(): MorphToMany
    {
        return $this->morphToMany(
            User::class,
            'watchable',
            'watchers',
            'watchable_id',
            'user_ref_id'
        );
    }

    public function cardTypes(): HasMany
    {
        return $this->hasMany(CardType::class, 'company_ref_id');
    }

    /**
     * @return HasManyThrough
     */
    public function teams(): HasManyThrough
    {
        return $this->hasManyThrough(
            Team::class ,
            Project::class,
            'company_ref_id',
            'project_ref_id',
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
        elseif ($model instanceof Company)
            return $this->company_id == $model->company_id;
        return false;
    }

    public static function getCompanyOf(Model $model): ?Company
    {
        $result = false;
        if ($model instanceof Company)
            $result = $model;
        elseif ($model instanceof Project)
            $result = $model->company;
        elseif ($model instanceof Team)
            $result = $model->project->company;
        if (!$result)
            throw new ModelNotFoundException();
        return $result;
    }

    public static function isCompanyOwner(Company $company, int $userId): bool
    {
        return Role::hasBaseRoleOn($company, $userId);
    }

    public static function getHierarchyItems(Model $model): Collection
    {
        $result = new Collection();
        if ($model instanceof Company)
            $result->push($model);
        elseif ($model instanceof Project)
            $result->push($model->company);
        elseif ($model instanceof Team)
            $result->push($model->project->company);
        return $result;
    }
}
