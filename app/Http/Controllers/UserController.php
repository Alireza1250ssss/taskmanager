<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) : JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.index',['resource'=>'کاربر']),[
            User::getRecords($request->toArray())->get()
        ]);
        return response()->json($response,$response['statusCode']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreUserRequest $request
     * @return JsonResponse
     */
    public function store(StoreUserRequest $request) : JsonResponse
    {
        $user = User::create($request->validated());
        $response = $this->getResponse(__('apiResponse.store',['resource'=>'کاربر']), [
            'user' => $user
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return JsonResponse
     */
    public function show(User $user) : JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.show',['resource'=>'کاربر']), [
            'user' => $user
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateUserRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, User $user) : JsonResponse
    {
        $user->update($request->validated());
        $response = $this->getResponse(__('apiResponse.update',['resource'=>'کاربر']), [
            'user' => $user
        ]);

        return response()->json($response, $response['statusCode']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $user
     * @return JsonResponse
     */
    public function destroy($user) : JsonResponse
    {
        $count = User::destroy(explode(',',$user));
        $response = $this->getResponse(__('apiResponse.destroy',['items'=>$count]));
        return response()->json($response, $response['statusCode']);
    }

    /**
     * @param User $user
     * @param string $type
     * @param Request $request
     * @return JsonResponse
     */
    public function setPermissions(User $user,string $type,Request $request): JsonResponse
    {
        // permission info mentioned here
        $permissiveRelations = [
          'fields' => ['table'=>'fields' , 'primaryKey' => 'field_id'] ,
          'entities' => ['table' => 'entities' , 'primaryKey' => 'entity_id'] ,
        ];

        $tableName = $permissiveRelations[$type]['table'];
        $primaryKey = $permissiveRelations[$type]['primaryKey'];

        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => Rule::exists($tableName,$primaryKey) ,
        ]);


        //sync permissions of user
        $user->$type()->sync($request->get('permissions'));

        $response = $this->getResponse(__('apiResponse.update',['resource'=>'کاربر']), [
            'user' => $user->load($type)
        ]);
        return response()->json($response, $response['statusCode']);
    }
}
