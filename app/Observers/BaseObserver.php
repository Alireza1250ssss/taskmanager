<?php

namespace App\Observers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResolvePermissionController;
use App\Http\Controllers\RoleController;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Services\ConditionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Tymon\JWTAuth\Facades\JWTAuth;

class BaseObserver extends Controller
{
    public ?User $user;
    public bool $noAuth = false;
    public array $userRoles = [];
    public array $models = [
        Company::class => "company",
        Project::class => "project",
        Team::class => "team",
        Task::class => "task",
    ];


    public function __construct()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!empty($user)) {
                $this->user = $user;

                // set cache time to a week
                $timeToStore = 60 * 60 * 24 * 7;
                $keyCache = 'user-' . $user->user_id . '-roles';
                $this->userRoles = Cache::remember($keyCache, $timeToStore, function () use ($user) {
                    return $user->roles->pluck('role_id')->toArray();
                });
            } else
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
            return;

        $isAllowed = in_array($this->user->user_id, $modelItem->members->pluck('user_id')->toArray());
        $isAllowedByParents = false;
        $parent = RoleController::getParentModel($modelItem);
        while ($parent)
        {
            if (in_array($this->user->user_id, $parent->members->pluck('user_id')->toArray())){
                $isAllowedByParents = true;
                break;
            }
            $parent = RoleController::getParentModel($parent);
        }
        //check if the authenticated user is among the allowed users or not
        if (!$isAllowed && !$isAllowedByParents) {
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
            return;

        $isAllowed = $this->checkIfAllowed(auth()->user()->user_id, $modelItem, 'update');

        // throw exception if not allowed !
        if (!$isAllowed) {
            throw new AuthorizationException('not allowed for update');
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

        $isAllowed = $this->checkIfAllowedForCreation(auth()->user()->user_id, $modelItem);
        //throw exception if not allowed !
        if (!$isAllowed) {
            throw new AuthorizationException('not allowed for creation');
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
            return;

        $isAllowed = $this->checkIfAllowed(auth()->user()->user_id, $modelItem, 'delete');

        // throw exception if not allowed !
        if (!$isAllowed) {
            throw new AuthorizationException('not allowed for delete');
        }
    }


    //private methods to check if the user is among the allowed user to do that action (used in crud permission check methods)

    protected function checkIfAllowed($userId, $modelItem, $action): bool
    {
        // first check if it is allowed by more broad permission defined on a parent model
        $allowedByParents = $this->checkIfAllowedByParents($userId,$modelItem,$action);
        if ($allowedByParents) return true;

        $modelId = $modelItem->{$modelItem->getPrimaryKey()};
        $modelName = get_class($modelItem);
        $modelName = $this->models[$modelName];

        $keyPermission = "can_" . $action . "_$modelName";

        // get roles relating to that permission
        $rolesHavingPermission = Role::query()->whereHas('permissions', function (Builder $builder) use ($keyPermission) {
            $builder->where('key', $keyPermission);
        })->get();


        // get users having that permission on the model retrieved via his role
        $allowedUsers = RoleUser::query()->where('rolable_type', $modelName)
            ->where('rolable_id', $modelId)
            ->whereIn('role_ref_id', $rolesHavingPermission->pluck('role_id')->toArray())
            ->where('user_ref_id',$userId)
            ->get();

        $rolesAllowed = Role::query()->whereIn('role_id',$allowedUsers->pluck('role_ref_id')->toArray())->get();
        $noCondition = true;
        $rolesAllowed->load('permissions');
        $allConditions = [];
        foreach ($rolesAllowed as $role){
            $conditions = $role->permissions()->where('key',$keyPermission)
                ->wherePivot('condition_params','!=',null)->first();
            if (empty($conditions) && $noCondition !== false){
                $noCondition = true;
                continue;
            }
            $noCondition = false;
            $allConditions = array_merge($allConditions,json_decode($conditions->pivot->condition_params));
        }
        (new ConditionService($modelItem,$allConditions))->checkConditions();

        return $allowedUsers->isNotEmpty();
    }

    private function checkIfAllowedForCreation($userId, $modelItem): bool
    {
        // first check if it is allowed by more broad permission defined on a parent model
        if ($this->checkIfAllowedByParents($userId,$modelItem ,'create'))
            return true;

        $modelName = get_class($modelItem);
        $modelName = $this->models[$modelName];

        $keyPermission = "can_create_".$modelName;

        if (empty(Permission::query()->where('key',$keyPermission)->first()))
            return false;

        // get roles relating to that permission
        $rolesHavingPermission = Role::query()->whereHas('permissions', function (Builder $builder) use ($keyPermission) {
            $builder->where('key', $keyPermission);
        })->get();


        // get users having that permission on the model retrieved via his role
        $allowedUsers = RoleUser::query()
            ->whereIn('role_ref_id', $rolesHavingPermission->pluck('role_id')->toArray())
            ->where('user_ref_id',$userId)
            ->get();
        return $allowedUsers->isNotEmpty();
    }

    private function checkIfAllowedByParents($userId, $modelItem, $action) : bool
    {

        $modelName = get_class($modelItem);
        $modelName = $this->models[$modelName] . "_in";

        $keyPermission = "can_" . $action . "_$modelName";

        // get roles relating to that permission
        $rolesHavingPermission = Role::query()->whereHas('permissions', function (Builder $builder) use ($keyPermission) {
            $builder->where('key', $keyPermission);
        })->get();

        $rolePermissionRecordForUser = RoleUser::query()->where('user_ref_id', $userId)
            ->whereIn('role_ref_id', $rolesHavingPermission->pluck('role_id')->toArray())
            ->whereNotNull('rolable_type')->get();

        if ($rolePermissionRecordForUser->isEmpty())
            return false;
        foreach ($rolePermissionRecordForUser as $rolePermission) {

            $parentItem = ResolvePermissionController::$models[$rolePermission->rolable_type]['class']::find($rolePermission->rolable_id);
            if (empty($parentItem)) continue;
            $correctType = in_array(get_class($parentItem),[Company::class , Project::class , Team::class]);

            if ($correctType && $parentItem->isParentOf($modelItem)) {
                // check if is there any condition to check
                $condition = Role::find($rolePermission->role_ref_id)->permissions()->where('key',$keyPermission)
                    ->wherePivot('condition_params','!=',null)->first();

                if (!empty($condition)) {
                    $access = $condition->pivot->access;
                    $condition = json_decode($condition->pivot->condition_params);
                    if (!empty($condition))
                        (new ConditionService($modelItem, $condition , $access))->checkConditions();
                }

                return true;
            }
        }
        return false;
    }

    public function created($modelItem)
    {
        $modelItem->members()->syncWithoutDetaching(auth()->user());
    }

}
