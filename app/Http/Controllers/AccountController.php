<?php

namespace App\Http\Controllers;

use App\Http\Requests\SelectNotificationRequest;
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
        $type = in_array($requestType,['notifications','unreadNotification']) ? $requestType : 'notifications';
        $response = $this->getResponse(__('apiResponse.index',['resource'=>'اعلان']),[
            auth()->user()->load($type)
        ]);
        return response()->json($response,$response['statusCode']);
    }

    /**
     * delete notifications
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteNotifications(SelectNotificationRequest $request): JsonResponse
    {
        $selectedNotifications = Auth::user()->notifications()->whereIn('id',$request->get('uuids',[]))->get();
        $count = $selectedNotifications->count();
        $selectedNotifications->each(function($item,$key){
            $item->delete();
        });
        $response = $this->getResponse(__('apiResponse.destroy',['items'=>$count]));
        return response()->json($response,$response['statusCode']);
    }

    /**
     * mark selected notifications as read
     * @param Request $request
     * @return JsonResponse
     */
    public function markAsRead(SelectNotificationRequest $request): JsonResponse
    {
        $selectedNotifications = Auth::user()->notifications()->whereIn('id',$request->get('uuids',[]))->get();
        $count = $selectedNotifications->count();
        $selectedNotifications->each(function($item,$key){
            $item->markAsRead();
        });
        $response = $this->getResponse(__('apiResponse.markAsRead',['items'=>$count]));
        return response()->json($response,$response['statusCode']);
    }

}
