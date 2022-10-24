<?php

namespace App\Observers;

use App\Models\Task;
use Illuminate\Auth\Access\AuthorizationException;

class TaskObserver extends BaseObserver
{
    public function updating($modelItem)
    {
        MetaFireEventOnMainModelObserver::$enabledAccessCheck = true;
        parent::updating($modelItem);
    }
}
