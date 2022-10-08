<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Entity;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Task;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ResolvePermissionController extends Controller
{
    public static array $models = [
        'company' => ['class' => Company::class, 'table' => 'companies', 'primaryKey' => 'company_id'],
        'project' => ['class' => Project::class, 'table' => 'projects', 'primaryKey' => 'project_id'],
        'team' => ['class' => Team::class, 'table' => 'teams', 'primaryKey' => 'team_id'],
        'task' => ['class' => Task::class, 'table' => 'tasks', 'primaryKey' => 'task_id'],
    ];

    public function insertKeys(): JsonResponse
    {
        $categories = config('permission-keys');
        foreach ($categories as $catName => $permissionKeys)
            foreach ($permissionKeys as $keyTitle => $key) {
                Permission::query()->updateOrCreate(
                    ['key' => $key, 'category' => $catName],
                    ['title' => $keyTitle]
                );
            }

        $baseRole = Role::query()->firstOrCreate([
            'name' => 'base-role'
        ]);
        $baseRole->permissions()->sync(Permission::all()->pluck('permission_id')->toArray());
        $response = $this->getResponse(__('apiResponse.index', ['resource' => 'دسترسی']));
        return response()->json($response, $response['statusCode']);
    }

    public function getBaseRoles(Request $request): JsonResponse
    {
        $request->validate([
            'category' => ['required', Rule::in(['company', 'project', 'team'])]
        ]);
        $result = RoleUser::getBaseRolesOfUser(auth()->user()->user_id,$request->get('category'));
        $response = $this->getResponse('موجودیت های ساخته شده دریافت شدند',[
           $result->values()
        ]);
        return response()->json($response,$response['statusCode']);
    }
}
