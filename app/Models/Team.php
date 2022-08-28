<?php

namespace App\Models;

use App\Http\Contracts\Hierarchy;
use App\Http\Traits\FilterRecords;
use App\Http\Traits\HasMembers;
use App\Http\Traits\MainPropertyGetter;
use App\Http\Traits\MainPropertySetter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model implements Hierarchy
{
    use HasFactory,SoftDeletes,FilterRecords,MainPropertyGetter,MainPropertySetter,HasMembers;

    protected $primaryKey = 'team_id';
    protected $fillable = ['name','project_ref_id','sprint_start_date','sprint_period','git_repo'];
    public array $filters = ['name','project_ref_id','sprint_start_date','sprint_period'];

    /**
     * @return BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class,'project_ref_id');
    }

    /**
     * @return HasMany
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class , 'team_ref_id');
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
            return $this->team_id == $model->team->team_id;
        return false;
    }
}
