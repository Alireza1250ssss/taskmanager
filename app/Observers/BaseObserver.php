<?php

namespace App\Observers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResolvePermissionController;
use App\Models\Entity;
use App\Models\Field;
use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
//            if (!in_array('jwt_auth', Route::current()->gatherMiddleware()))
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
            $modelItem->setAttributes($modelAttributes);
        }

    }

    /**
     * This observer method is called when a model record is in the updating process,
     * at this point, the updates has not yet been persisted to the database.
     * @param $modelItem
     * @throws AuthorizationException
     */
    public function updating($modelItem)
    {
        if ($this->noAuth === true)
            return;
        $modelId = $modelItem->{$modelItem->getPrimaryKey()};
        $class = get_class($modelItem);
        $userId = auth()->user()->user_id;

        $entityPermission = Entity::query()->where([
            'key' => $class,
            'action' => 'update',
            'model_id' => $modelId
        ])->with('users')->first();
        if (empty($entityPermission))
            return;
        // check if permission found

        // check the permission on user
        if (!in_array($entityPermission->entity_id, auth()->user()->entities->pluck('entity_id')->toArray())) {
            throw new AuthorizationException();
        }
    }

    /**
     * This observer method is called when a model record is in the process of creation,
     * and not yet stored into the database,
     * @param $modelItem
     * @throws AuthorizationException
     */
    public function creating($modelItem)
    {
        if ($this->noAuth === true)
            return;

        $class = get_class($modelItem);


        $entityPermission = Entity::query()->where([
            'key' => $class,
            'action' => 'create',
            'model_id' => null
        ])->with('users')->first();
        if (empty($entityPermission))
            return;
        // check if permission found

        // check the permission on user
        if (!in_array($entityPermission->entity_id, auth()->user()->entities->pluck('entity_id')->toArray())) {
            throw new AuthorizationException();
        }
    }

    public function created($model)
    {
        $class = get_class($model);
        $modelId =  $model->{$model->getPrimaryKey()};
        Entity::query()->upsert([
            ['key' => $class, 'action' => 'read', 'model_id' => $modelId],
            ['key' => $class, 'action' => 'update', 'model_id' => $modelId],
            ['key' => $class, 'action' => 'delete', 'model_id' => $modelId],

        ], ['key', 'action', 'model_id']);

        Entity::query()->updateOrInsert([
            'key' => $class,
            'action' => 'create'
        ],['action' => 'create']);

        $entities = Entity::query()->where([
            'key' => $class ,
            'action' => 'create'
        ])->orWhere(function (Builder $builder) use ($class,$modelId){
            $builder->where('key',$class)->where('model_id',$modelId);
        })->get()->pluck('entity_id')->toArray();

        auth()->user()->entities()->syncWithoutDetaching($entities);
    }
}
