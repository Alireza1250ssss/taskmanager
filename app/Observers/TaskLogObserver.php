<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\TaskLog;
use App\Models\TaskMeta;

class TaskLogObserver
{
    public function created($task)
    {
        if ($task instanceof Task) {
            $this->addTaskCreationLog($task);
        } elseif ($task instanceof TaskMeta) {
            $this->addMetaCreationLog($task);
        }
    }

    public function updated($task)
    {
        if ($task instanceof Task) {
            $changes = $task->getChanges();
            unset($changes['updated_at']);
            unset($changes['stage_ref_id']);
            $original = $task->getOriginal();

            foreach ($changes as $field => $change){
                $this->addFieldUpdateLog($task, $field, $change, $original);
            }
        } elseif ($task instanceof TaskMeta) {
            $params = [
              'meta_item_before' => $task
            ];
        }
    }

    /**
     * @param Task $task
     */
    protected function addTaskCreationLog(Task $task): void
    {
        $params = [
            'task' => $task->toArray()
        ];
        TaskLog::query()->create([
            'name' => 'ایحاد تسک',
            'task_id' => $task->task_id,
            'tags' => 'create,task',
            'params' => $params,
            'description' => ' ایجاد شد ' . auth()->user()->full_name . ' تسک با موفقیت توسط ',
            'user_ref_id' => auth()->user()->user_id
        ]);
    }

    /**
     * @param TaskMeta $task
     */
    protected function addMetaCreationLog(TaskMeta $task): void
    {
        $params = [
            'meta_item' => $task->toArray()
        ];
        TaskLog::query()->create([
            'name' => 'وارد کردن فیلد',
            'user_ref_id' => auth()->user()->user_id,
            'params' => $params,
            'tags' => 'task,meta,create',
            'description' => '',
            'task_id' => $task->task_ref_id,
            'column' => $task->column_ref_id
        ]);
    }

    /**
     * @param Task $task
     * @param string $field
     * @param $change
     * @param array $original
     */
    protected function addFieldUpdateLog(Task $task, string $field, $change, array $original): void
    {
        $params = [
            'task_before_update' => $task->toArray(),
            'request_update' => request()->all()
        ];
        TaskLog::query()->create([
            'name' => $field . ' ویرایش فیلد ',
            'params' => $params,
            'task_id' => $task->task_id,
            'user_ref_id' => auth()->user()->user_id,
            'tags' => "update,task,$field",
            'description' => ' ویرایش شد' . $change . ' به مقدار ' . $original[$field] . ' از مقدار ' . $field . 'فیلد '
        ]);
    }
}
