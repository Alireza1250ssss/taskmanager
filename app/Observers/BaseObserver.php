<?php

namespace App\Observers;

use App\Http\Controllers\Controller;
use App\Http\Requests\BaseRequest;
use App\Models\Entity;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class BaseObserver extends  Controller
{
    public ?User $user;
    public bool $noAuth = false;

    public function __construct()
    {

        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            $this->noAuth = true;
        }
    }


    /**
     * This observer method is called when a model record is retrieved
     * @param $modelItem
     * @throws AuthorizationException
     */
    public function retrieved($modelItem)
    {
        $modelId = $modelItem->{$modelItem->getPrimaryKey()};
        $entityPermission = Entity::query()->where([
            'key' => get_class($modelItem) ,
            'action' => 'read' ,
            'model_id' => $modelId
        ])->first();
        if (!empty($entityPermission) && $this->noAuth == false){
            if (!in_array($entityPermission->entity_id,auth()->user()->entities->pluck('entity_id')->toArray())) {
                  $modelItem->setAttributes([]);
            }
        }

    }

    /**
     * This observer method is called when a model record is in the updating process,
     * at this point, the updates has not yet been persisted to the database.
     * @param $modelItem
     */
    public function updating($modelItem)
    {
        die('omm');
        dd(auth()->user());
    }

    /**
     * This observer method is called when a model record is in the process of creation,
     * and not yet stored into the database,
     * @param $modelItem
     */
    public function creating($modelItem)
    {

    }
}
