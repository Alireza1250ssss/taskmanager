<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Entity;
use App\Models\Field;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function resolvePermission(Request $request): JsonResponse
    {
        //TODO add actions option
        //TODO user selected field

        $request->validate([
            'name' => ['required', Rule::in(array_keys(self::$models))],
        ]);
        $entity = self::$models[$request->get('name')];

        $request->validate([
            'id' => [Rule::requiredIf($request->get('only_fields',false) == false) , Rule::exists($entity['table'], $entity['primaryKey'])],
            'insert_fields' => 'required|boolean',
            'only_fields' => 'required|boolean'
        ]);

        // Upsert entities

        try {
            if (!$request->filled('only_fields') || $request->get('only_fields') == false) {
                $class = $entity['class'];
                Entity::query()->upsert([
                    ['key' => $class, 'action' => 'read', 'model_id' => $request->get('id')],
                    ['key' => $class, 'action' => 'update', 'model_id' => $request->get('id')],
                    ['key' => $class, 'action' => 'delete', 'model_id' => $request->get('id')],

                ], ['key', 'action', 'model_id']);

                // insert create action separately because the unique index is on
                // key,action,model_id fields
                Entity::query()->updateOrInsert([
                    'key' => $class,
                    'action' => 'create'
                ],['action' => 'create']);
            }
            // Upsert fields
            if ($request->filled('insert_fields') && $request->get('insert_fields')) {
                $entityModel = new $entity['class'];
                $fields = $entityModel->getFillable();
                $fields = collect($fields)->map(fn($item) => ['name' => $item, 'model' => $entity['class']])->toArray();
                Field::query()->upsert($fields, ['name', 'model']);

                $response = $this->getResponse('دسترسی ها ساخته شدند');
                return response()->json($response, $response['statusCode']);
            }
        } catch (\Exception $e) {
            $response = $this->getError('مشکلی در ثبت دسترسی ها وجود داشت !', [], self::$HTTP_SERVER_ERROR);
            return response()->json($response, $response['statusCode']);
        }
    }

    /**
     * @param User $user
     * @param string $type
     * @param Request $request
     * @return JsonResponse
     */
    public function setPermissions(User $user, string $type, Request $request): JsonResponse
    {
        // permission info mentioned here
        $permissiveRelations = [
            'fields' => ['table' => 'fields', 'primaryKey' => 'field_id'],
            'entities' => ['table' => 'entities', 'primaryKey' => 'entity_id'],
        ];

        $tableName = $permissiveRelations[$type]['table'];
        $primaryKey = $permissiveRelations[$type]['primaryKey'];

        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => Rule::exists($tableName, $primaryKey),
        ]);


        //sync permissions of user
        if ($type === 'entities')
            $user->$type()->sync($request->get('permissions'));
        else {
            $parentPermission = Entity::query()->where([
                'key' => static::$models[$request->get('field_entity')]['class'],
                'action' => $request->get('field_entity_action') ,
                'model_id' => $request->get('field_entity_id')
            ])->firstOrFail()->users->where('user_id',$user->user_id)->firstOrFail()->pivot;

            $user->$type()->syncWithPivotValues(
                $request->get('permissions'),
                ['parent_id' =>$parentPermission->id]);
        }

        $response = $this->getResponse(__('apiResponse.update', ['resource' => 'کاربر']), [
            'user' => $user->load($type)
        ]);
        return response()->json($response, $response['statusCode']);
    }
}
