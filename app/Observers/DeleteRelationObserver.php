<?php

namespace App\Observers;

use App\Http\Contracts\ClearRelations;

class DeleteRelationObserver
{
    public function deleting($model)
    {
        if ($model instanceof ClearRelations)
            $model->deleteRelations();
    }
}
