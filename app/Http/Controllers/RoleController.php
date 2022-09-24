<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignRoleRequest;
use App\Http\Requests\AttachConditionRequest;
use App\Http\Requests\StoreRoleRequest;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Services\AccessToChangeRoleService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    public const LEVELS = [
        'company' => 1,
        'project' => 2,
        'team' => 3,
        'task' => 4
    ];


    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'category' => ['required', Rule::in(array_keys(self::LEVELS))],
            'entity_id' => ['required', 'numeric']
        ]);

        $response = $this->getResponse(__('apiResponse.index', ['resource' => 'نقش']), [
            Role::getRecords($request->toArray())->addConstraints(function ($query) use ($request) {
                $category = $request->get('category');
                $query->with('permissions');
                $model = ResolvePermissionController::$models[$category]['class']::findOrFail($request->get('entity_id'));
                $company = Company::getCompanyOf($model);
                $query->where('company_ref_id', $company->company_id);
                $query->where('category', '>=', self::LEVELS[$category]);
            })->get()
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRoleRequest $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $company = Company::query()->findOrFail($request->get('company_ref_id'));
        StoreRoleRequest::checkForCompanyOwner($company, auth()->user()->user_id);
        $role = Role::create($request->validated());
        if ($request->filled('permissions')) {
            $permissions = collect($request->get('permissions'))
                ->keyBy('permission_id')
                ->transform(fn($val) => ['access' => $val['access']])
                ->all();
            $role->permissions()->sync($permissions);
        }
        $response = $this->getResponse(__('apiResponse.store', ['resource' => 'نقش']), [
            'role' => $role->load('permissions')
        ]);
        return response()->json($response, $response['statusCode']);
    }


    /**
     * @param AttachConditionRequest $request
     * @param Role $role
     * @return JsonResponse
     * @throws ValidationException
     */
    public function addCondition(AttachConditionRequest $request, Role $role): JsonResponse
    {
        $role = Role::query()->where('role_id', $role->role_id)
            ->with('permissions')->firstOrFail();
        StoreRoleRequest::checkForCompanyOwner(Company::findOrFail($role->company_ref_id), auth()->user()->user_id);

        $permission = $role->permissions->find($request->get('permission_id'));
        if (empty($permission))
            throw ValidationException::withMessages(['permission_id' => 'دسترسی انتخاب شده در نقش مورد نظر موجود نمی باشد']);

        $conditionParams = $request->filled('conditions') ?
            json_encode([
                'conditions' => $request->get('conditions', []),
                'actions' => $request->get('actions', []),
            ]) : null;
        if (empty($conditionParams) && $permission->pivot->access === 'reject')
            $role->permissions()->detach($permission->permission_id);
        else {
            $role->permissions()->updateExistingPivot($request->get('permission_id'), [
                'condition_params' => $conditionParams
            ]);
        }

        $response = $this->getResponse('شرط ها با موفقیت اعمال شدند', [
            $role->load('permissions')
        ]);
        return response()->json($response, $response['statusCode']);
    }

    public function getColumnsFor($model): JsonResponse
    {

        $model = new ResolvePermissionController::$models[$model]['class'];
        $fillable = $model->getFillable();
        $result = [];
        foreach ($fillable as $col) {
            $result[$col] = Schema::getColumnType($model->getTable(), $col);
        }
        $response = $this->getResponse(__("apiResponse.index", ['resource' => 'ستونها']), [
            $result
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
     * @throws ValidationException
     */
    public function update(StoreRoleRequest $request, Role $role): JsonResponse
    {
        if ($request->filled('company_ref_id')) {
            $company = Company::query()->findOrFail($request->get('company_ref_id'));
            StoreRoleRequest::checkForCompanyOwner($company, auth()->user()->user_id);
        }
        $role->update($request->validated());
        if ($request->filled('permissions')) {
            $permissions = collect($request->get('permissions'))
                ->keyBy('permission_id')
                ->transform(fn($val) => ['access' => $val['access']])
                ->all();
            $role->permissions()->sync($permissions);
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


        try {
            DB::transaction(function () use ($request, $user) {
                foreach ($request->get('roles') as $role) {
                    $data = array_merge(['user_ref_id' => $user->user_id], $role);

                    $type = $role['rolable_type'];
                    $modelId = $role['rolable_id'];
                    $modelInstance = ResolvePermissionController::$models[$type]['class']::findOrFail($modelId);
                    \auth()->user()->authorizeFor('can_change_member_in', $modelInstance);
                    static::checkAccessOnRole($role['role_ref_id'], $modelInstance);
                    RoleUser::query()->upsert($data, array_keys($data));
                }
            });
        } catch (\Throwable $e) {
            $response = $this->getForbidden($e->getMessage());
            return response()->json($response, $response['statusCode']);
        }

        $response = $this->getResponse("نقش ها با موفقیت اختصاص یافتند", [
            $user->load('roles')
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * @param AssignRoleRequest $request
     * @return JsonResponse
     */
    public function detachRoleFromUser(AssignRoleRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->get('email'))->first();

        try {
            DB::transaction(function () use ($request, $user) {
                foreach ($request->get('roles') as $roleUserRecord) {
                    $type = $roleUserRecord['rolable_type'];
                    $modelId = $roleUserRecord['rolable_id'];
                    $modelInstance = ResolvePermissionController::$models[$type]['class']::findOrFail($modelId);
                    \auth()->user()->authorizeFor('can_change_member_in', $modelInstance);
                    static::checkAccessOnRole($roleUserRecord['role_ref_id'], $modelInstance);
                    RoleUser::query()->where([
                        'rolable_type' => $type,
                        'rolable_id' => $modelId,
                        'role_ref_id' => $roleUserRecord['role_ref_id'],
                        'user_ref_id' => $user->user_id
                    ])->delete();
                }
            });
        } catch (\Throwable $e) {
            $response = $this->getForbidden($e->getMessage());
            return response()->json($response, $response['statusCode']);
        }

        $response = $this->getResponse(__('apiResponse.destroy', ['items' => count($request->get('roles'))]));
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

    public static function getChildModels($model)
    {
        if ($model instanceof Project)
            return $model->teams;
        elseif ($model instanceof Company)
            return $model->projects;
        elseif ($model instanceof Team)
            return $model->tasks;
        else
            return null;
    }

    public function getPermissions(): JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.index', ['resource' => 'دسترسی ها']), [
            Permission::all()
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * @param $roleId
     * @param $modelInstance
     * @throws AuthorizationException
     */
    public static function checkAccessOnRole($roleId, $modelInstance): void
    {
        if (!AccessToChangeRoleService::isAbleFor($roleId, $modelInstance, auth()->user()->user_id))
            throw new AuthorizationException('تمام دسترسی های نقش انتخاب شده را ندارید');
    }
}
