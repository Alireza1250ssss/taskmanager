<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskMeta extends Model
{
    use HasFactory;

    protected $primaryKey = 'task_meta_id';
    protected $fillable = ['task_key', 'task_value', 'task_ref_id', 'column_ref_id'];

    /**
     * @return BelongsTo
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_ref_id');
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(Column::class, 'column_ref_id');
    }

    public static function updateMeta(Task $task, array $metaItems)
    {
        $taskId = $task->task_id;
        foreach ($metaItems as $metaItem) {
            $doDelete = $metaItem['delete'] ?? false;
            if (empty($metaItem['column_ref_id']) && $doDelete == true)
                static::deleteMetaRecord($task, $metaItem);
            elseif (!empty($metaItem['column_ref_id']))
                TaskMeta::query()->updateOrCreate(
                    ['task_ref_id' => $taskId, 'column_ref_id' => $metaItem['column_ref_id']],
                    ['task_value' => $metaItem['task_value'], 'task_key' => $metaItem['task_key']]
                );
            else
                TaskMeta::query()->updateOrCreate(
                    ['task_ref_id' => $taskId, 'task_key' => $metaItem['task_key']],
                    ['task_value' => $metaItem['task_value']]
                );

        }
    }

    public static function deleteMetaRecord(Task $task, $metaItem)
    {
        TaskMeta::query()->where('task_ref_id', $task->task_id)->where('task_key', $metaItem['task_key'])
            ->whereNull('column_ref_id')->delete();
    }
}
