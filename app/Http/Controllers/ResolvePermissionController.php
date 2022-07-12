<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Entity;
use App\Models\Field;
use App\Models\Project;
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
            'id' => ['required', Rule::exists($entity['table'], $entity['primaryKey'])],
            'insert_fields' => 'boolean',
            'only_fields' => 'boolean'
        ]);

        // Upsert entities

        try {
            if (!$request->filled('only_fields') || $request->get('only_fields') == false) {
                $class = $entity['class'];
                Entity::query()->upsert([
                    ['key' => $class, 'action' => 'read', 'model_id' => $request->get('id')],
                    ['key' => $class, 'action' => 'update', 'model_id' => $request->get('id')],
                    ['key' => $class, 'action' => 'delete', 'model_id' => $request->get('id')],
                    ['key' => $class, 'action' => 'create', 'model_id' => null],
                ], ['key', 'action', 'model_id']);
                Entity::query()->updateOrInsert([
                    'key' => $class,
                    'action' => 'create'
                ],['action' => 'create']);
            }// Upsert fields
            if ($request->filled('insert_fields') && $request->get('insert_fields')) {
                $entityModel = new $entity['class'];
                $fields = $entityModel->getFillable();
                $fields = collect($fields)->map(fn($item) => ['name' => $item, 'model' => $entity['class']])->toArray();
                Field::query()->upsert($fields, ['name', 'model']);

                $response = $this->getResponse('دسترسی ها ساخته شدند');
                return response()->json($response, $response['statusCode']);
            }
        } catch (\Exception $e) {
            dd($e->getMessage());
            $response = $this->getError('مشکلی در ثبت دسترسی ها وجود داشت !', [], self::$HTTP_SERVER_ERROR);
            return response()->json($response, $response['statusCode']);
        }
    }
}
