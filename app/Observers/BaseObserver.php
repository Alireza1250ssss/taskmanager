<?php

namespace App\Observers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Field;
use App\Models\Permission;
use App\Models\Project;
use App\Models\RoleUser;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Tymon\JWTAuth\Facades\JWTAuth;

class BaseObserver extends Controller
{
    public ?User $user;
    public bool $noAuth = false;
    public array $userRoles = [];


    public function __construct()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!empty($user)){
                $this->user = $user;

                // set cache time to a week
                $timeToStore = 60*60*24*7;
                $keyCache = 'user-'.$user->user_id.'-roles';
                $this->userRoles = Cache::remember($keyCache,$timeToStore,function () use ($user){
                    return $user->roles->pluck('role_id')->toArray();
                });
            }
            else
                $this->noAuth = true;
        } catch (\Exception $e) {
            $this->noAuth = true;
        }
    }


    /**
     * This observer method is called when a model record is retrieved
     * @param $modelItem
     */
    public function retrieved($modelItem)
    {
        if ($this->noAuth === true)
            return ;

        $isAllowed = in_array($this->user->user_id , $modelItem->members->pluck('user_id')->toArray());

        //check if the authenticated user is among the allowed users or not
        if (!$isAllowed) {
            $modelItem->setAttributes([]);
            return;
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
            return ;

        $isAllowed = $this->checkIfAllowed(auth()->user()->user_id , $modelItem,'update');

        // throw exception if not allowed !
        if (!$isAllowed) {
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

        $isAllowed = $this->checkIfAllowedForCreation(auth()->user()->user_id,$modelItem);

        //throw exception if not allowed !
        if (!$isAllowed) {
            throw new AuthorizationException();
        }
    }


    /**
     * This observer method is called when a model record is in the deletion process,
     * at this point, the record has not yet been deleted from the database,
     * and using its id to retrieve it from the database will return appropriate data.
     * @param $modelItem
     * @throws AuthorizationException
     */
    public function deleting($modelItem)
    {
        if ($this->noAuth === true)
            return ;

        $isAllowed = $this->checkIfAllowed(auth()->user()->user_id,$modelItem,'delete');

        // throw exception if not allowed !
        if (!$isAllowed) {
            throw new AuthorizationException();
        }
    }


    //private methods to check if the user is among the allowed user to do that action (used in crud permission check methods)

    private function checkIfAllowed($userId, $modelItem,$action): bool
    {
        $modelId = $modelItem->{$modelItem->getPrimaryKey()};
        $modelName = get_class($modelItem);


        // get roles relating to that permission
        $rolesHavingPermission = Permission::query()->where([
            'action' => $action,
            'model' => $modelName
        ])->get();
        if ($rolesHavingPermission->isEmpty())
            return true;

        // get users having that permission on the model retrieved via his role
        $allowedUsers = RoleUser::query()->where('rolable_type',$modelName)
            ->where(function ($query) use ($modelId){
                $query->where('rolable_id',$modelId)->orWhere('rolable_id',0);
            })
            ->whereIn('role_ref_id',$rolesHavingPermission->pluck('role_ref_id')->toArray())
            ->get()->pluck('user_ref_id')->toArray();
        return in_array($userId,$allowedUsers);
    }

    private function checkIfAllowedForCreation($userId, $modelItem): bool
    {
        $modelName = get_class($modelItem);

        // get roles relating to that permission
        $rolesHavingPermission = Permission::query()->where([
            'action' => 'create',
            'model' => $modelName
        ])->get();
        if ($rolesHavingPermission->isEmpty())
            return true;

        // get users having that permission on the model retrieved via his role
        $allowedUsers = RoleUser::query()
            ->whereIn('role_ref_id',$rolesHavingPermission->pluck('role_ref_id')->toArray())
            ->get()->pluck('user_ref_id')->toArray();
        return in_array($userId,$allowedUsers);
    }
}
