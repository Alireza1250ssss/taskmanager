<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskMeta extends Model
{
    use HasFactory;

    protected $primaryKey = 'task_meta_id';
    protected $fillable = ['task_key' ,'task_value' ,'task_ref_id'];

    /**
     * @return BelongsTo
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class ,'task_ref_id');
    }
}
