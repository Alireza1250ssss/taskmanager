<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\TaskMeta;

class MetaFireEventOnMainModelObserver
{
    public function updating(TaskMeta $meta)
    {
        /** @var Task $task */
        $task = $meta->task;
        $task->touchUpdating();
    }
}
