<?php

namespace App\Models;

use App\Http\Requests\UpdateTaskRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class TaskLog extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $primaryKey = 'task_log_id';
    protected $fillable = ['name','description','params','tags','task_id','column','user_ref_id'];
    protected $casts = [
      'params' => 'array'
    ];

    /**
     * @return BelongsTo
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class ,'task_id' , 'task_id');
    }

    public static function stageChangeLog(Task $task ,UpdateTaskRequest $request)
    {
        $beforeStage = $task->stage->name;
        $afterStage = Stage::find($request->get('stage_ref_id'))->name;
        $params = [
            'task_before_update' => $task->toArray() ,
            'update_request' => $request->validated() ,
        ];
        return
            static::create([
            'name' => 'تغییر استیج تسک' ,
            'description' => sprintf(" تسک از استیج  %s به استیج %s انتقال یافت",$beforeStage,$afterStage)  ,
            'tags' => 'stage,task' ,
            'params'  => $params ,
            'task_id' => $task->task_id
        ]);
    }

}
