<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use App\Models\Schedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) : JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.index',['resource'=>'زمان بندی']),[
            Schedule::getRecords($request->toArray())->get()
        ]);
        return response()->json($response,$response['statusCode']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreScheduleRequest $request
     * @return JsonResponse
     */
    public function store(StoreScheduleRequest $request) : JsonResponse
    {
        $schedule = Schedule::create($request->validated());
        $response = $this->getResponse(__('apiResponse.store',['resource'=>'زمان بندی']), [
            'schedule' => $schedule->load('user')
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Display the specified resource.
     *
     * @param Schedule $schedule
     * @return JsonResponse
     */
    public function show(Schedule $schedule) : JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.show',['resource'=>'زمان بندی']), [
            'schedule' => $schedule->load('user')
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateScheduleRequest $request
     * @param Schedule $schedule
     * @return JsonResponse
     */
    public function update(UpdateScheduleRequest $request, Schedule $schedule) : JsonResponse
    {
        $schedule->update($request->validated());
        $response = $this->getResponse(__('apiResponse.update',['resource'=>'زمان بندی']), [
            'schedule' => $schedule->load('user')
        ]);

        return response()->json($response, $response['statusCode']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $schedule
     * @return JsonResponse
     */
    public function destroy($schedule) : JsonResponse
    {
        $count = Schedule::destroy(explode(',',$schedule));
        $response = $this->getResponse(__('apiResponse.destroy',['items'=>$count]));
        return response()->json($response, $response['statusCode']);
    }
}
