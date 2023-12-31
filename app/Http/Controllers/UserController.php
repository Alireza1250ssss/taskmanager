<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Permission;
use App\Models\RoleUser;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.index', ['resource' => 'کاربر']), [
            User::getRecords($request->toArray())->get()
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreUserRequest $request
     * @return JsonResponse
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create($request->validated());
        $response = $this->getResponse(__('apiResponse.store', ['resource' => 'کاربر']), [
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
    public function show(User $user): JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.show', ['resource' => 'کاربر']), [
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
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user->update($request->validated());
        $response = $this->getResponse(__('apiResponse.update', ['resource' => 'کاربر']), [
            'user' => $user
        ]);

        return response()->json($response, $response['statusCode']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $user
     * @return JsonResponse
     */
    public function destroy($user): JsonResponse
    {
        $count = User::destroy(explode(',', $user));
        $response = $this->getResponse(__('apiResponse.destroy', ['items' => $count]));
        return response()->json($response, $response['statusCode']);
    }

    public function getRolesOfUser(Request $request,User $user): JsonResponse
    {
        $request->validate([
           'type' => 'required|in:company,project,team',
           'type_id' => 'required'
        ]);

        ['type' => $type , 'type_id' => $typeId] = $request->only(['type','type_id']);

        $modelInstance = ResolvePermissionController::$models[$type]['class']::findOrFail($typeId);
        \auth()->user()->authorizeFor('can_get_members_in', $modelInstance);

        $response = $this->getResponse(__('apiResponse.show',['resource' => 'نقش های کاربر']),[
           $user->load(['roles' => function($query) use($type,$typeId){
               $query->where('name','!=','base-role');
               $query->wherePivot('rolable_type',$type);
               $query->wherePivot('rolable_id',$typeId);
           }])
        ]);
        return response()->json($response,$response['statusCode']);
    }

}
