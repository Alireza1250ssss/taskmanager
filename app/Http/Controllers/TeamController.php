<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


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
            Team::getRecords($request->toArray())->get()
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
     * Display the specified resource.
     *
     * @param Team $team
     * @return JsonResponse
     */
    public function show(Team $team) : JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.show',['resource'=>'تیم']), [
            'team' => $team->load(['project.company','tasks'])
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
