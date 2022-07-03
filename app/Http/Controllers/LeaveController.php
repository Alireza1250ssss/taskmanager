<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeaveRequest;
use App\Http\Requests\UpdateLeaveRequest;
use App\Models\Leave;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) : JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.index',['resource'=>'رخصت']),[
            Leave::getRecords($request->toArray())->get()
        ]);
        return response()->json($response,$response['statusCode']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreLeaveRequest $request
     * @return JsonResponse
     */
    public function store(StoreLeaveRequest $request) : JsonResponse
    {
        $leave = Leave::create($request->validated());
        $response = $this->getResponse(__('apiResponse.store',['resource'=>'رخصت']), [
            'leave' => $leave
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Display the specified resource.
     *
     * @param Leave $leave
     * @return JsonResponse
     */
    public function show(Leave $leave) : JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.show',['resource'=>'رخصت']), [
            'leave' => $leave->load('user','schedules')
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param StoreLeaveRequest $request
     * @param Leave $leave
     * @return JsonResponse
     */
    public function update(StoreLeaveRequest $request, Leave $leave) : JsonResponse
    {
        $leave->update($request->validated());
        $response = $this->getResponse(__('apiResponse.update',['resource'=>'رخصت']), [
            'leave' => $leave
        ]);

        return response()->json($response, $response['statusCode']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $leave
     * @return JsonResponse
     */
    public function destroy($leave) : JsonResponse
    {
        $count = Leave::destroy(explode(',',$leave));
        $response = $this->getResponse(__('apiResponse.destroy',['items'=>$count]));
        return response()->json($response, $response['statusCode']);
    }
}
