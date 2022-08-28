<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignRoleRequest;
use App\Http\Requests\StoreRoleRequest;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.index', ['resource' => 'نقش']), [
            Role::getRecords($request->toArray())->get()
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRoleRequest $request
     * @return JsonResponse
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = Role::create($request->validated());
        if ($request->filled('permissions'))
            $role->permissions()->sync($request->get('permissions'));
        $response = $this->getResponse(__('apiResponse.store', ['resource' => 'نقش']), [
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
    public function show(Role $role): JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.show', ['resource' => 'نقش']), [
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
    public function update(StoreRoleRequest $request, Role $role): JsonResponse
    {
        $role->update($request->validated());
        if ($request->filled('permissions')) {
            $role->permissions()->sync($request->get('permissions'));
        }
        $response = $this->getResponse(__('apiResponse.update', ['resource' => 'نقش']), [
            'role' => $role->load('permissions')
        ]);

        return response()->json($response, $response['statusCode']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $role
     * @return JsonResponse
     */
    public function destroy($role): JsonResponse
    {
        $count = Role::destroy(explode(',', $role));
        $response = $this->getResponse(__('apiResponse.destroy', ['items' => $count]));
        return response()->json($response, $response['statusCode']);
    }

    /**
     * @param AssignRoleRequest $request
     * @return JsonResponse
     */
    public function setRolesForUser(AssignRoleRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->get('email'))->first();

        $data = $request->only(['role_ref_id', 'rolable_type', 'rolable_id']);
        $data = array_merge(['user_ref_id' => $user->user_id], $data);

        RoleUser::query()->upsert($data, array_keys($data));


        $response = $this->getResponse("نقش ها با موفقیت اختصاص یافتند", [
            $user->load('roles')
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function detachRoleFromUser(Request $request): JsonResponse
    {
        $user = User::query()->where('email', $request->get('email'))->first();
        $request->validate([
            'roles' => 'required|filled|array',
            'roles.*' => ['required', Rule::exists('roles', 'role_id')]
        ]);

        $count = RoleUser::query()->where('user_ref_id', $user->user_id)
            ->whereIn('role_ref_id', $request->get('roles'))->delete();

        $response = $this->getResponse(__('apiResponse.destroy', ['items' => $count]));
        return response()->json($response, $response['statusCode']);
    }

    public static function getParentModel($model)
    {
        if ($model instanceof Project)
            return $model->company;
        elseif ($model instanceof Team)
            return $model->project;
        elseif ($model instanceof Task)
            return $model->team;
        else
            return null;
    }

    public function getPermissions(): JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.index',['resource' => 'دسترسی ها']) , [
           Permission::all()
        ]);
        return response()->json($response,$response['statusCode']);
    }
}