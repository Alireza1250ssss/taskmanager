<?php

namespace App\Observers;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use App\Models\Field;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tymon\JWTAuth\Facades\JWTAuth;

class BaseObserver extends Controller
{
    public ?User $user;
    public bool $noAuth = false;


    public function __construct()
    {

        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            if (!in_array('jwt_auth', Route::current()->gatherMiddleware()))
                $this->noAuth = true;
        }
    }


    /**
     * This observer method is called when a model record is retrieved
     * @param $modelItem
     */
    public function retrieved($modelItem)
    {

        //if app (this route) does not need authentication check
        if ($this->noAuth === true)
            return;
        $modelId = $modelItem->{$modelItem->getPrimaryKey()};
        $class = get_class($modelItem);
        $userId = auth()->user()->user_id;
        // check permission for entity

        $entityPermission = Entity::query()->where([
            'key' => $class,
            'action' => 'read',
            'model_id' => $modelId
        ])->with('users')->first();
        if (empty($entityPermission))
            return;
        // check if permission found

        // check the permission on user
        if (!in_array($entityPermission->entity_id, auth()->user()->entities->pluck('entity_id')->toArray())) {
            $modelItem->setAttributes([]);
            return;
        }


        //check permissions for fields
        $fieldPermission = Field::query()->where('model', $class)->with('users')->get();

        $notAllowedFields = [];
//        dd($entityPermission);
        $parentEntity = $entityPermission->users()->where('user_id', $userId)->first()->pivot->id;
        $userFields = auth()->user()->fields()->wherePivot('parent_id', $parentEntity)->get()->pluck('field_id')->toArray();
//    dd($userFields,$fieldPermission->pluck('field_id')->toArray());
        $fieldPermission->each(function ($item, $key) use (&$notAllowedFields, $entityPermission, $userFields) {
            if (!in_array($item->field_id, $userFields))
                $notAllowedFields[] = $item;
        });
//        dd($notAllowedFields);
        if (!empty($notAllowedFields)) {
            $modelAttributes = $modelItem->getAttributes();
            collect($notAllowedFields)->each(function ($item, $key) use (&$modelAttributes) {
                unset($modelAttributes[$item->name]);
            });
        }
        $modelItem->setAttributes($modelAttributes);

    }

    /**
     * This observer method is called when a model record is in the updating process,
     * at this point, the updates has not yet been persisted to the database.
     * @param $modelItem
     */
    public function updating($modelItem)
    {

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
