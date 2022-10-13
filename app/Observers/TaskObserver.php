<?php

namespace App\Observers;

use Illuminate\Auth\Access\AuthorizationException;

class TaskObserver extends BaseObserver
{


    public function retrieved($task)
    {
        //if app (this route) does not need authentication check
        if ($this->noAuth === true)
            return;

        $userId = auth()->user()->user_id;
        $isAllowed = empty($task->user_ref_id) ?
            (in_array($userId, $task->team->members->pluck("user_id")->toArray()) || in_array($userId, $task->members->pluck('user_id')->toArray()))
            :
            ($userId == $task->user_ref_id || in_array($userId, $task->members->pluck('user_id')->toArray()));


        $task->team->unsetRelation('members');
        $task->unsetRelations();

        if (!$isAllowed) {
            $task->setAttributes([]);
            return;
        }

    }

    public function updating($task)
    {
        if ($this->noAuth === true)
            return;

        $isAllowed = $this->checkIfAllowed(auth()->user()->user_id, $task, 'update');

        $isTaskAssignee = $task->user_ref_id == auth()->user()->user_id;

        // throw exception if not allowed !
        if (!$isAllowed && !$isTaskAssignee) {
            throw new AuthorizationException('دسترسی ویرایش این تسک را ندارید !');
        }
    }
}
