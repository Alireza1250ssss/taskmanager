<?php

namespace App\Models;

use App\Http\Traits\FilterRecords;
use App\Http\Traits\HasMembers;
use App\Http\Traits\MainPropertyGetter;
use App\Http\Traits\MainPropertySetter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, FilterRecords, SoftDeletes, MainPropertyGetter, MainPropertySetter,HasMembers;

    protected $primaryKey = 'task_id';
    protected $fillable = [
        'title', 'description', 'user_ref_id', 'parent_id', 'team_ref_id', 'stage_ref_id', 'status_ref_id',
        'priority', 'labels', 'real_time', 'estimate_time', 'due_date', 'order' , 'reviewed_at','card_type_ref_id'
    ];
    public array $filters = [
        'title', 'description', 'user_ref_id', 'parent_id', 'team_ref_id', 'stage_ref_id', 'status_ref_id',
        'priority', 'labels', 'real_time', 'estimate_time', 'due_date','card_type_ref_id'
    ];
    protected $hidden = ['taskMetas'];
    protected $casts = [
        'real_time' => 'array',
        'done_at' => 'datetime' , 'reviewed_at' => 'datetime'
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_ref_id');
    }

    public function cardType(): BelongsTo
    {
        return $this->belongsTo(CardType::class,'card_type_ref_id');
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
        return $this->belongsTo(Status::class, 'status_ref_id')->select(['name','status_id']);
    }

    /**
     * @return BelongsTo
     */
    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'stage_ref_id')->select(['name','stage_id']);
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

    public function mergeMeta($relationship)
    {

        $meta = $this->$relationship;

        $meta->map(function ($item, $key) {
            $this->setAttribute($item->column_ref_id, [
                'meta' => true,
                'value' => $item->task_value,
                'column' => $item->column
            ]);
        });
    }

    public function setDoneAt()
    {
        $this->done_at = now();
        $this->saveQuietly();
    }

    public function setLastOrderInStage()
    {
        $lastOrder = static::query()->where('stage_ref_id',$this->stage_ref_id)->max('order');
        $this->order = (int)$lastOrder + 1000;
        $this->saveQuietly();
    }
}
