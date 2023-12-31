<?php

namespace App\Http\Controllers;

use App\Http\Requests\SelectNotificationRequest;
use App\Http\Requests\UserAssignViewRequest;
use App\Http\Requests\UserAssignWatcherRequest;
use App\Models\Company;
use App\Models\Project;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    /**
     * get notifications for the authenticated user
     * @param Request $request
     * @return JsonResponse
     */
    public function getNotifications(Request $request): JsonResponse
    {
        $requestType = $request->get('type');
        $type = in_array($requestType, ['notifications', 'unreadNotification']) ? $requestType : 'notifications';
        $response = $this->getResponse(__('apiResponse.index', ['resource' => 'اعلان']), [
            auth()->user()->load($type)
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * delete notifications
     * @param SelectNotificationRequest $request
     * @return JsonResponse
     */
    public function deleteNotifications(SelectNotificationRequest $request): JsonResponse
    {
        $selectedNotifications = Auth::user()->notifications()->whereIn('id', $request->get('uuids', []))->get();
        $count = $selectedNotifications->count();
        $selectedNotifications->each(function ($item, $key) {
            $item->delete();
        });
        $response = $this->getResponse(__('apiResponse.destroy', ['items' => $count]));
        return response()->json($response, $response['statusCode']);
    }

    /**
     * mark selected notifications as read
     * @param SelectNotificationRequest $request
     * @return JsonResponse
     */
    public function markAsRead(SelectNotificationRequest $request): JsonResponse
    {
        $selectedNotifications = Auth::user()->notifications()->whereIn('id', $request->get('uuids', []))->get();
        $count = $selectedNotifications->count();
        $selectedNotifications->each(function ($item, $key) {
            $item->markAsRead();
        });
        $response = $this->getResponse(__('apiResponse.markAsRead', ['items' => $count]));
        return response()->json($response, $response['statusCode']);
    }

    /**
     * @param $model
     * @param $modelId
     * @param UserAssignWatcherRequest $request
     * @return JsonResponse
     */
    public function setWatcher($model, $modelId, UserAssignWatcherRequest $request): JsonResponse
    {
        if (!in_array($model, array_keys(ResolvePermissionController::$models))) {
            $response = $this->getError('برای موجودیت انتخابی واچر تعیین نمی شود');
            return response()->json($response, $response['statusCode']);
        }

        try {
            // company or project or team or task
            $modelInstance = ResolvePermissionController::$models[$model]['class']::findOrFail($modelId);

            \auth()->user()->authorizeFor('can_change_watcher_in', $modelInstance);

            $users = User::query()->whereIn('email', $request->get('users'))->get();
            if ($users->isNotEmpty()) {
                if ($request->get('mode', 'attach') === 'detach')
                    $modelInstance->watchers()->detach($users->pluck('user_id')->toArray());
                else {
                    if ($modelInstance->watchers()->whereIn('user_id', $users->pluck('user_id')->toArray())->get()->isNotEmpty()) {
                        $response = $this->getError('واچر تکراری انتخاب شده است');
                        return response()->json($response, $response['statusCode']);
                    }
                    $modelInstance->watchers()->syncWithoutDetaching($users->pluck('user_id')->toArray());
                    // watchers would also be members
                    $this->setMembersRecursive($modelInstance, $users->pluck('user_id')->toArray());
                }
            }
        } catch (\Exception $e) {
            $response = $this->getError(__('apiResponse.forbidden'));
            return response()->json($response, $response['statusCode']);
        }

        $message = $request->get('mode', 'attach') === 'detach' ?
            ' واچر ها با موفقیت کاسته شدند' : 'واچر ها با موفقیت افزوده شدند';
        $response = $this->getResponse($message);
        return response()->json($response, $response['statusCode']);
    }


    /**
     * @param $model
     * @param $modelId
     * @return JsonResponse
     */
    public function getWatchers($model, $modelId): JsonResponse
    {
        if (!in_array($model, array_keys(ResolvePermissionController::$models))) {
            $response = $this->getError('برای موجودیت انتخابی واچر تعیین نمی شود');
            return response()->json($response, $response['statusCode']);
        }
        // company or project or team or task
        $modelInstance = ResolvePermissionController::$models[$model]['class']::find($modelId);

        $access = \auth()->user()->canDo('can_get_watchers_in', $modelInstance, \auth()->user()->user_id);
        //unset the relations possibly loaded when checking permission
        $modelInstance = $modelInstance->withoutRelations();

        $response = $this->getResponse(__('apiResponse.index', ['resource' => 'واچر']), [
            $access ? $modelInstance->load('watchers') : $modelInstance
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * @param $model
     * @param $modelId
     * @param UserAssignViewRequest $request
     * @return JsonResponse
     */
    public function setMember($model, $modelId, UserAssignViewRequest $request): JsonResponse
    {
        try {
            DB::transaction(function () use ($request, $model, $modelId) {
                // company or project or team or task
                $modelInstance = ResolvePermissionController::$models[$model]['class']::findOrFail($modelId);
                \auth()->user()->authorizeFor('can_add_member_in', $modelInstance);

                $users = User::query()->whereIn('email', $request->get('users'))->get();

                $userIds = $users->pluck('user_id')->toArray();

                $this->setMembersRecursive($modelInstance, $userIds);
                foreach ($users as $user) {
                    foreach ($request->get('roles') as $roleItem) {
                        RoleController::checkAccessOnRole($roleItem, $modelInstance);
                        $data = [
                            'user_ref_id' => $user->user_id,
                            'role_ref_id' => $roleItem,
                            'rolable_type' => $model,
                            'rolable_id' => $modelId
                        ];
                        RoleUser::query()->upsert($data, array_keys($data));
                    }
                }

            });
        } catch (\Exception $e) {
            $response = $this->getForbidden(__('apiResponse.forbidden'));
            return response()->json($response, $response['statusCode']);
        }

        $message = 'اعضا با موفقیت افزوده شدند';
        $response = $this->getResponse($message);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * @param $model
     * @param $modelId
     * @param UserAssignViewRequest $request
     * @return JsonResponse
     */
    public function removeMember($model, $modelId, UserAssignViewRequest $request): JsonResponse
    {
        try {
            DB::transaction(function () use ($request, $model, $modelId) {
                // company or project or team or task
                $modelInstance = ResolvePermissionController::$models[$model]['class']::findOrFail($modelId);
                \auth()->user()->authorizeFor('can_remove_member_in', $modelInstance);

                $users = User::query()->whereIn('email', $request->get('users'))->get();

                $userIds = $users->pluck('user_id')->toArray();

                $this->NotAllowOwner($modelInstance, $userIds);
                foreach ($userIds as $userId) {
                    Role::takeRolesOn($modelInstance, $userId);
                }
                $modelInstance->members()->detach($userIds);
                // take all membership and roles on any child items
                static::removeFromChildItems($modelInstance,$userIds);
                // take membership from parent items if possible
                // ( if has no role on parent items or membership on same level entity)
                static::freshMembers($modelInstance, $userIds);
            });
        } catch (\Throwable $e) {
            $response = $this->getForbidden(__('apiResponse.forbidden'));
            return response()->json($response, $response['statusCode']);
        }

        $message = ' اعضا با موفقیت کاسته شدند';
        $response = $this->getResponse($message);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * @param $model
     * @param $modelId
     * @return JsonResponse
     */
    public function getMembers($model, $modelId): JsonResponse
    {
        if (!in_array($model, array_keys(ResolvePermissionController::$models))) {
            $response = $this->getError('برای موجودیت انتخابی عضو تعیین نمی شود');
            return response()->json($response, $response['statusCode']);
        }
        // company or project or team or task
        $modelInstance = ResolvePermissionController::$models[$model]['class']::findOrFail($modelId);

        $access = \auth()->user()->canDo('can_get_members_in', $modelInstance, \auth()->user()->user_id);
        //unset the relations possibly loaded when checking permission
        $modelInstance = $modelInstance->withoutRelations();

        $response = $this->getResponse(__('apiResponse.index', ['resource' => 'عضو']), [
            $access ? $modelInstance->load('members') : $modelInstance
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * set members on entities recursively. ex : member of a team will be member of the team's project as well
     * @param array $users
     * @param $model
     */
    protected function setMembersRecursive($model, array $users)
    {
        if ($model instanceof Company) {
            $model->members()->syncWithoutDetaching($users);
        } elseif ($model instanceof Project) {
            $model->members()->syncWithoutDetaching($users);
            $this->setMembersRecursive($model->company, $users);
        } elseif ($model instanceof Team) {
            $model->members()->syncWithoutDetaching($users);
            $this->setMembersRecursive($model->project, $users);
        } elseif ($model instanceof Task) {
            $model->members()->syncWithoutDetaching($users);
            $this->setMembersRecursive($model->team, $users);
        }
    }

    protected function NotAllowOwner($modelInstance, $userIds)
    {
        $res = false;

        foreach ($userIds as $userId) {
            do {
                if (Role::hasBaseRoleOn($modelInstance, $userId)) {
                    $res = true;
                    break;
                }
            } while ($modelInstance = RoleController::getParentModel($modelInstance));
            if ($res) break;
        }
        if ($res)
            throw new AuthorizationException('امکان حذف مالک را ندارید');
    }


    public static function freshMembers($model, $users)
    {
        foreach ($users as $user) {

            while ($model = RoleController::getParentModel($model)) {
                if (!Role::hasAnyRoleOn($model, $user)) {
                    $roleOnChildren = false;
                    foreach (RoleController::getChildModels($model) as $child)
                        if (Role::hasAnyRoleOn($child, $user))
                            $roleOnChildren = true;
                    if ($roleOnChildren) break;
                    $model->members()->detach($user);
                } else
                    break;
            }
        }
    }

    public static function removeFromChildItems(Model $model, $userIds)
    {
        if (empty($childItems = RoleController::getChildModels($model)))
            return;
        foreach ($userIds as $userId){
            foreach ($childItems as $childItem){
                Role::takeRolesOn($childItem,$userId);
                $childItem->members()->detach($userId);
                static::removeFromChildItems($childItem,$userIds);
            }
        }
    }
}
