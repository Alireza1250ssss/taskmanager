<?php

namespace App\Observers;

use App\Models\Comment;
use App\Models\Task;
use App\Models\TaskLog;
use App\Models\TaskMeta;
use App\Services\CalcTimeService;

class TaskLogObserver
{
    public function created($model)
    {
        if ($model instanceof Task) {
            $this->addTaskCreationLog($model);
        } elseif ($model instanceof TaskMeta) {
            $this->addMetaCreationLog($model);
        }
        elseif (($model instanceof Comment) && $model->commentable_type == Task::class){
            $this->addCommentLog($model);
        }
    }

    public function updated($task)
    {
        if ($task instanceof Task) {
            $changes = $task->getChanges();
            unset($changes['updated_at']);
            $original = $task->getOriginal();

            foreach ($changes as $field => $change){
                $log = $this->addFieldUpdateLog($task, $field, $change, $original);
                if (CalcTimeService::hasCalcTimeField($task))
                    (new CalcTimeService($task,$log))->calculate();
            }
        } elseif ($task instanceof TaskMeta) {
            $changes = $task->getChanges();
            $fieldTitle = $task->column->title;
            unset($changes['updated_at']);
            $original = $task->getOriginal();

            if (array_key_exists('task_value',$changes)) {
                $this->addFieldMetaUpdateLog($task, $fieldTitle, $original, $changes);
            }
        }
    }

    /**
     * @param Task $task
     * @return TaskLog
     */
    protected function addTaskCreationLog(Task $task): TaskLog
    {
        /** @var TaskLog $log */
        $log = TaskLog::query()->create([
            'task_id' => $task->task_id,
            'action' => 'create',
            'description' => "task created successfully by  ".auth()->user()->full_name  ,
            'user_ref_id' => auth()->user()->user_id
        ]);
        return $log;
    }

    /**
     * @param TaskMeta $task
     * @return TaskLog
     */
    protected function addMetaCreationLog(TaskMeta $task): TaskLog
    {

        $fieldTitle = $task->column->title;
        /** @var TaskLog $log */
        $log = TaskLog::query()->create([
            'user_ref_id' => auth()->user()->user_id,
            'action' => 'set',
            'description' => "$fieldTitle field set",
            'task_id' => $task->task_ref_id,
            'column' => $task->column_ref_id,
            'after_value' => $task->task_value
        ]);
        return $log;
    }

    /**
     * @param Comment $comment
     * @return TaskLog
     */
    protected function addCommentLog(Comment $comment): TaskLog
    {
        /** @var TaskLog $log */
        $log = TaskLog::query()->create([
           'user_ref_id' => auth()->user()->user_id,
            'task_id' => $comment->commentable_id ,
            'action' => 'create',
            'description' => 'comment added by '. auth()->user()->full_name,
            'after_value' => $comment->content
        ]);
        return $log;
    }

    /**
     * @param Task $task
     * @param string $field
     * @param $change
     * @param array $original
     * @return TaskLog
     */
    protected function addFieldUpdateLog(Task $task, string $field, $change, array $original): TaskLog
    {
        $before = $original[$field] ?? '--';
        /** @var TaskLog $log */
        $log = TaskLog::query()->create([
            'task_id' => $task->task_id,
            'user_ref_id' => auth()->user()->user_id,
            'action' => "update",
            'description' => "field $field changed from $before to $change value",
            'column' => $field,
            'before_value' => $original[$field],
            'after_value' => $change
        ]);
        return $log;
    }

    /**
     * @param TaskMeta $taskMeta
     * @param $fieldTitle
     * @param array $original
     * @param array $changes
     * @return TaskLog
     */
    protected function addFieldMetaUpdateLog(TaskMeta $taskMeta, $fieldTitle, array $original, array $changes): TaskLog
    {
        $before = $original['task_value'];
        $after = $changes['task_value'];
        /** @var TaskLog $log */
        $log = TaskLog::query()->create([
            'user_ref_id' => auth()->user()->user_id,
            'task_id' => $taskMeta->task_ref_id,
            'action' => "update",
            'column' => $taskMeta->column_ref_id,
            'description' => "field $fieldTitle changed from $before to $after value",
            'before_value' => $before,
            'after_value' => $after
        ]);
        return $log;
    }
}
