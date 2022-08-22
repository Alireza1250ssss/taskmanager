<?php

namespace App\Http\Controllers;

use App\Http\Requests\SelectNotificationRequest;
use App\Http\Requests\UserAssignViewRequest;
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
     * @param Request $request
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
     * @param Request $request
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
     * @param UserAssignViewRequest $request
     * @return JsonResponse
     */
    public function setWatcher($model, $modelId, UserAssignViewRequest $request): JsonResponse
    {
        if (!in_array($model, array_keys(ResolvePermissionController::$models))) {
            $response = $this->getError('برای موجودیت انتخابی واچر تعیین نمی شود');
            return response()->json($response, $response['statusCode']);
        }
        // company or project or team or task
        $modelInstance = ResolvePermissionController::$models[$model]['class']::find($modelId);

        $users = User::query()->whereIn('email', $request->get('users'))->get();
//        dd($users->pluck('user_id')->toArray(),$relationWatcher[$model],$modelInstance);
        if ($users->isNotEmpty())
            $request->get('mode', 'attach') === 'detach' ?
                $modelInstance->watchers()->dettach($users->pluck('user_id')->toArray())
                :
                $modelInstance->watchers()->attach($users->pluck('user_id')->toArray());

        $response = $this->getResponse('واچر ها با موفقیت افزوده شدند');
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

}
