<?php

namespace App\Models;

use App\Http\Requests\UpdateTaskRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskLog extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $primaryKey = 'task_log_id';
    protected $fillable = ['before_value', 'description', 'after_value', 'action', 'task_id', 'column', 'user_ref_id'];
    protected $casts = [
        'params' => 'array'
    ];

    /**
     * @return BelongsTo
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id', 'task_id');
    }

    public static function stageChangeLog(Task $task, UpdateTaskRequest $request)
    {
        $beforeStage = $task->stage->name;
        $afterStage = Stage::find($request->get('stage_ref_id'))->name;
        $params = [
            'task_before_update' => $task->toArray(),
            'update_request' => $request->validated(),
        ];
        return
            static::create([
                'name' => 'تغییر استیج تسک',
                'description' => "stage changed from $beforeStage to $afterStage",
                'tags' => 'update,stage,task',
                'params' => $params,
                'task_id' => $task->task_id,
                'column' => 'stage_ref_id',
                'user_ref_id' => auth()->user()->user_id ,
            ]);
    }

}
