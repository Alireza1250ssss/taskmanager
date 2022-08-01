<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Entity;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.index', ['resource' => 'پروژه']), [
            Project::getRecords($request->toArray())->get()
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProjectRequest $request
     * @return JsonResponse
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = Project::create($request->validated());
        $response = $this->getResponse(__('apiResponse.store', ['resource' => 'پروژه']), [
            'project' => $project->load('teams')
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Display the specified resource.
     *
     * @param Project $project
     * @return JsonResponse
     */
    public function show(Project $project): JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.show', ['resource' => 'پروژه']), [
            'project' => $project->load('teams')
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProjectRequest $request
     * @param Project $project
     * @return JsonResponse
     */
    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $project->update($request->validated());
        $response = $this->getResponse(__('apiResponse.update', ['resource' => 'پروژه']), [
            'project' => $project->load('teams')
        ]);

        return response()->json($response, $response['statusCode']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $project
     * @return JsonResponse
     */
    public function destroy($project): JsonResponse
    {
        $count = Project::destroy(explode(',', $project));
        $response = $this->getResponse(__('apiResponse.destroy', ['items' => $count]));
        return response()->json($response, $response['statusCode']);
    }

    /**
     * @param Project $project
     * @param Request $request
     * @return JsonResponse
     */
    public function addAssign(Project $project, Request $request): JsonResponse
    {

        $entityToGive = Entity::query()->where([
            'key' => Project::class,
            'model_id' => $project->project_id,
            'action' => 'read'
        ])->firstOrFail();

        if ($request->filled('users'))
            foreach ($request->get('users') as $user) {
                $user = User::find($user);
                if (!empty($user))
                    $user->entities()->syncWithoutDetaching($entityToGive->entity_id);
            }

        $response = $this->getResponse(__('apiResponse.add-viewer'));
        return response()->json($response, $response['statusCode']);

    }
}
