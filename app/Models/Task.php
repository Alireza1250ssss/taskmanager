<?php

namespace App\Models;

use App\Http\Contracts\ClearRelations;
use App\Http\Contracts\WithMeta;
use App\Http\Traits\FilterRecords;
use App\Http\Traits\HasMembers;
use App\Http\Traits\MainPropertyGetter;
use App\Http\Traits\MainPropertySetter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model implements WithMeta,ClearRelations
{
    use HasFactory, FilterRecords, SoftDeletes, MainPropertyGetter, MainPropertySetter, HasMembers;

    protected $primaryKey = 'task_id';
    protected $fillable = [
        'title', 'description', 'user_ref_id', 'parent_id', 'team_ref_id', 'stage_ref_id', 'status_ref_id',
        'priority', 'labels', 'real_time', 'estimate_time', 'due_date', 'order', 'reviewed_at', 'card_type_ref_id'
    ];
    public array $filters = [
        'title', 'description', 'user_ref_id', 'parent_id', 'team_ref_id', 'stage_ref_id', 'status_ref_id',
        'priority', 'labels', 'real_time', 'estimate_time', 'due_date', 'card_type_ref_id'
    ];
    protected $hidden = ['taskMetas'];
    protected $casts = [
        'real_time' => 'array',
        'done_at' => 'datetime', 'reviewed_at' => 'datetime'
    ];

    public array $metaDirty  = [] ;
    public array $modelDirty = [] ;

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_ref_id');
    }

    public function cardType(): BelongsTo
    {
        return $this->belongsTo(CardType::class, 'card_type_ref_id');
    }

    /**
     * @return BelongsTo
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_ref_id');
    }

    /**
     * @return BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_ref_id')->select(['name', 'status_id']);
    }

    /**
     * @return BelongsTo
     */
    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'stage_ref_id')->select(['name', 'stage_id']);
    }

    /**
     * @return MorphMany
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * @return HasMany
     */
    public function taskMetas(): HasMany
    {
        return $this->hasMany(TaskMeta::class, 'task_ref_id');
    }

    /**
     * @return HasMany
     */
    public function taskLogs(): HasMany
    {
        return $this->hasMany(TaskLog::class, 'task_id', 'task_id');
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

    public function mergeMeta($relationship = 'taskMetas')
    {
        $meta = $this->$relationship;

        $meta->map(function ($item, $key) {
            $this->setAttribute("m-" . $item->column_ref_id, [
                'meta' => true,
                'value' => $item->task_value,
                'column' => $item->column
            ]);
        });
    }

    public function mergeRawMeta(): Task
    {
        $meta = $this->taskMetas;
        $this->modelDirty = parent::getDirty();
        $meta->map(function ($item, $key) {
            if (!empty($item->column))
                if (!empty($item->column->title))
                    $this->{$item->column->title} = $item->task_value;
        });
        return $this;
    }

    public function syncMetaWithRequest(): Task
    {
        $reqMeta = request()->get('task_metas',[]);
        foreach ($reqMeta as $metaItem) {
            if (array_key_exists('column_ref_id', $metaItem)) {
                $column = Column::query()->find($metaItem['column_ref_id']);
                if (empty($column)) continue;
                if ($this->{$column->title} != $metaItem['task_value'])
                    $this->metaDirty[$column->title] = $metaItem['task_value'];
                $this->{$column->title} = $metaItem['task_value'];
            } elseif (!isset($metaItem['delete']) || $metaItem['delete'] == false) {
                if ($this->{$metaItem['task_key']} != $metaItem['task_value'])
                    $this->metaDirty[$metaItem['task_key']] = $metaItem['task_value'];
                $this->{$metaItem['task_key']} = $metaItem['task_value'];
            }
        }
        return $this;
    }

    public function setDoneAt()
    {
        $this->done_at = now();
        $this->saveQuietly();
    }

    public function setLastOrderInStage()
    {
        $lastOrder = static::query()->where('stage_ref_id', $this->stage_ref_id)->max('order');
        $this->order = (int)$lastOrder + 1000;
        $this->saveQuietly();
    }

    public static function getAvailableTeams(int $userID): Collection
    {
        $items = RoleUser::query()->whereNotNull(['rolable_type', 'rolable_id'])
            ->where('user_ref_id', $userID)->get();
        $items = $items->groupBy('rolable_type')->all();

        $result = new Collection();

        if (array_key_exists('company', $items)) {
            foreach ($items['company'] as $companyItem) {
                $company = Company::query()->find($companyItem->rolable_id);
                if (empty($company)) continue;
                $result = $result->merge($company->teams);
            }
        }
        if (array_key_exists('project', $items)) {
            foreach ($items['project'] as $projectItem) {
                $project = Project::query()->find($projectItem->rolable_id);
                if (empty($project)) continue;
                $result = $result->merge($project->teams);
            }
        }
        if (array_key_exists('team', $items)) {
            foreach ($items['team'] as $teamItem) {
                $team = Team::query()->find($teamItem->rolable_id);
                if (empty($team)) continue;
                $result = $result->push($team);
            }
        }
        return $result->unique();
    }

    public function getMetaRelation(): string
    {
        return 'taskMetas';
    }

    public function getAllDirty(): array
    {
        return array_merge($this->modelDirty, $this->metaDirty);
    }

    public function deleteRelations()
    {
        $this->taskMetas()->delete();
    }

    public function touchUpdating()
    {
        $this->fireModelEvent('updating',$this);
    }

    public function touchRetrieved()
    {
        $this->fireModelEvent('retrieved',$this);
    }
}
