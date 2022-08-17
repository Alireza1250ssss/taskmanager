<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignRoleRequest;
use App\Http\Requests\StoreRoleRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) : JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.index',['resource'=>'نقش']),[
            Role::getRecords($request->toArray())->get()
        ]);
        return response()->json($response,$response['statusCode']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRoleRequest $request
     * @return JsonResponse
     */
    public function store(StoreRoleRequest $request) : JsonResponse
    {
        $role = Role::create($request->validated());
        if ($request->filled('permissions'))
            $role->permissions()->createMany($request->get('permissions'));
        $response = $this->getResponse(__('apiResponse.store',['resource'=>'نقش']), [
            'role' => $role->load('permissions')
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Display the specified resource.
     *
     * @param Role $role
     * @return JsonResponse
     */
    public function show(Role $role) : JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.show',['resource'=>'نقش']), [
            'role' => $role->load('permissions')
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param StoreRoleRequest $request
     * @param Role $role
     * @return JsonResponse
     */
    public function update(StoreRoleRequest $request, Role $role) : JsonResponse
    {
        $role->update($request->validated());
        if ($request->filled('permissions')){
            $role->permissions()->delete();
            $role->permissions()->createMany($request->get('permissions'));
        }
        $response = $this->getResponse(__('apiResponse.update',['resource'=>'نقش']), [
            'role' => $role->load('permissions')
        ]);

        return response()->json($response, $response['statusCode']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $role
     * @return JsonResponse
     */
    public function destroy($role) : JsonResponse
    {
        $count = Role::destroy(explode(',',$role));
        $response = $this->getResponse(__('apiResponse.destroy',['items'=>$count]));
        return response()->json($response, $response['statusCode']);
    }

    public function setRolesForUser(AssignRoleRequest $request,User $user)
    {
        foreach ($request['roles'] as  $itemForThisRole) {
            $roleId = $itemForThisRole['role_id'];
            unset($itemForThisRole['role_id']);
            $user->roles()->attach([
                $roleId => $itemForThisRole
            ]);
        }

        $response = $this->getResponse("نقش ها با موفقیت اختصاص یافتند",[
            $user->load('roles')
        ]);
        return response()->json($response,$response['statusCode']);
    }
}
