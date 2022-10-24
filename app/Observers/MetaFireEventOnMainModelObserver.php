<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\TaskMeta;

class MetaFireEventOnMainModelObserver
{
    // updating event must be triggered only once per each updating request
    public static bool $enabledAccessCheck = false;

    public function updating(TaskMeta $meta)
    {
        /** @var Task $task */
        $task = $meta->task;
        if (static::$enabledAccessCheck === false ) {
            $task->touchUpdating();
            static::$enabledAccessCheck = true;
        }
    }
}
