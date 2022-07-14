<?php

namespace App\Observers;

use App\Models\Task;

class TaskObserver
{
    public function retrieved(Task &$task)
    {
        $task->mergeMeta('taskMetas');
    }
}
