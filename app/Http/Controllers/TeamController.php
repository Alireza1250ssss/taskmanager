<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Http\Requests\UserAssignViewRequest;
use App\Models\Company;
use App\Models\Entity;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;


class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) : JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.index',['resource'=>'تیم']),[
            Team::getRecords($request->toArray())->addConstraints(function ($query) use($request){
                if (!$request->filled('project_ref_id')) {
                    $query->where(function ($q){
                        $q->whereIn('project_ref_id', Team::getAvailableProjects(auth()->user()->user_id)->pluck('project_id')->toArray());
                        $q->orWhereIn('team_id',auth()->user()->teamsJoined->pluck('team_id')->toArray());
                    });
                }
                })->get()
        ]);
        return response()->json($response,$response['statusCode']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreTeamRequest $request
     * @return JsonResponse
     */
    public function store(StoreTeamRequest $request) : JsonResponse
    {
        $team = Team::create($request->validated());
        $response = $this->getResponse(__('apiResponse.store',['resource'=>'تیم']), [
            'team' => $team->load('project.company')
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * @param Request $request
     * @param Team $team
     * @return JsonResponse
     * @throws ValidationException
     */
    public function setGithubAccessToken(Request $request,Team $team): JsonResponse
    {
        $request->validate([
           'github_access_token' => 'required'
        ]);
        if (!Company::isCompanyOwner($team->project->company,auth()->user()->user_id))
            throw ValidationException::withMessages(['team' => __('apiResponse.not-company-owner')]);
        $team->github_access_token = $request->get('github_access_token');
        $team->save();
        $response = $this->getResponse(__('apiResponse.update',['resource'=>'توکن گیتهاب تیم']), [
            'team' => $team->makeVisible('github_access_token')->refresh()
        ]);

        return response()->json($response, $response['statusCode']);
    }

    /**
     * Display the specified resource.
     *
     * @param Team $team
     * @return JsonResponse
     */
    public function show(Team $team) : JsonResponse
    {
        $team->load(['project.company','tasks']);
        $team->tasks->transform(function($item,$key){
           $item->mergeMeta('taskMetas');
           return $item;
        });
        $response = $this->getResponse(__('apiResponse.show',['resource'=>'تیم']), [
            'team' => $team
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateTeamRequest $request
     * @param Team $team
     * @return JsonResponse
     */
    public function update(UpdateTeamRequest $request, Team $team) : JsonResponse
    {
        $team->update($request->validated());
        $response = $this->getResponse(__('apiResponse.update',['resource'=>'تیم']), [
            'team' => $team->load('project.company')
        ]);

        return response()->json($response, $response['statusCode']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $team
     * @return JsonResponse
     */
    public function destroy($team) : JsonResponse
    {
        $count = Team::destroy(explode(',',$team));
        $response = $this->getResponse(__('apiResponse.destroy',['items'=>$count]));
        return response()->json($response, $response['statusCode']);
    }


}
