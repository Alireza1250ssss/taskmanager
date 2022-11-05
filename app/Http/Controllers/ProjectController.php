<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Requests\UserAssignViewRequest;
use App\Models\Entity;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


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
            Project::getRecords($request->toArray())->addConstraints(function ($query) use($request){
                if (!$request->filled('company_ref_id')) {
                    $query->where(function ($q){
                        $q->whereIn('company_ref_id', Project::getAvailableCompanies(auth()->user()->user_id)->pluck('company_id')->toArray());
                        $q->orWhereIn('project_id',auth()->user()->projectsJoined->pluck('project_id')->toArray());
                    });
                }
                })->get()
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


}
