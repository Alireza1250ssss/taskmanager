<?php

namespace App\Models;

use App\Http\Traits\FilterRecords;
use App\Http\Traits\MainPropertyGetter;
use App\Http\Traits\MainPropertySetter;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, FilterRecords, SoftDeletes, MainPropertyGetter, MainPropertySetter;

    protected $primaryKey = 'task_id';
    protected $fillable = [
        'title', 'description', 'user_ref_id', 'parent_id', 'team_ref_id', 'stage_ref_id', 'status_ref_id',
        'priority', 'labels', 'real_time', 'estimate_time', 'due_date'
    ];
    public array $filters = [
        'title', 'description', 'user_ref_id', 'parent_id', 'team_ref_id', 'stage_ref_id', 'status_ref_id',
        'priority', 'labels', 'real_time', 'estimate_time', 'due_date'
    ];
    protected $hidden = ['taskMetas'];
    protected $casts =[
      'real_time' => 'array'
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_ref_id');
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
        return $this->belongsTo(Status::class, 'status_ref_id');
    }

    /**
     * @return BelongsTo
     */
    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'stage_ref_id');
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

    public function mergeMeta($relationship)
    {
        $meta = $this->$relationship;
        $meta->map(function ($item, $key) {
            $this->{$item->task_key} = $item->task_value;
        });
    }


}
