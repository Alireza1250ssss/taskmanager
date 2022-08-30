<?php

namespace App\Http\Controllers;

use App\Http\Requests\SelectNotificationRequest;
use App\Http\Requests\UserAssignViewRequest;
use App\Http\Requests\UserAssignWatcherRequest;
use App\Models\Company;
use App\Models\Project;
use App\Models\RoleUser;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $response = $this->getResponse(__('apiResponse.index', ['resource' => 'واچر']), [
            $modelInstance->load('watchers')
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
        if (!in_array($model, array_keys(ResolvePermissionController::$models))) {
            $response = $this->getError('برای موجودیت انتخابی عضو تعیین نمی شود');
            return response()->json($response, $response['statusCode']);
        }

        try {
            // company or project or team or task
            $modelInstance = ResolvePermissionController::$models[$model]['class']::findOrFail($modelId);
            $users = User::query()->whereIn('email', $request->get('users'))->get();

            if ($users->isNotEmpty())
                if ($request->get('mode', 'attach') === 'detach')
                    $modelInstance->members()->detach($users->pluck('user_id')->toArray());
                else {
                    $this->setMembersRecursive($modelInstance, $users->pluck('user_id')->toArray());
                    foreach ($users as $user){
                        foreach ($request->get('roles') as $roleItem){
                            $data = [
                                'user_ref_id' => $user->user_id ,
                                'role_ref_id' => $roleItem ,
                                'rolable_type' => $model ,
                                'rolable_id' => $modelId
                            ];
                            RoleUser::query()->upsert($data, array_keys($data));
                        }
                    }
                }
        } catch (\Exception $e) {
            $response = $this->getError(__('apiResponse.forbidden'));
            return response()->json($response, $response['statusCode']);
        }

        $message = $request->get('mode', 'attach') === 'detach' ?
            ' اعضا با موفقیت کاسته شدند' : 'اعضا با موفقیت افزوده شدند';
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

        $response = $this->getResponse(__('apiResponse.index', ['resource' => 'عضو']), [
            $modelInstance->load('members')
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

}
