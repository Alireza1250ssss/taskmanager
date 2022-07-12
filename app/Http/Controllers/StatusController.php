<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) : JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.index',['resource'=>'وضعیت']),[
            Status::getRecords($request->toArray())->get()
        ]);
        return response()->json($response,$response['statusCode']);
    }


//
//    /**
//     * Store a newly created resource in storage.
//     *
//     * @param  \Illuminate\Http\Request  $request
//     * @return JsonResponse
//     */
//    public function store(Request $request) : JsonResponse
//    {
//        $status = Status::create($request->validated());
//        $response = $this->getResponse(__('apiResponse.store',['resource'=>'']), [
//            '' => $status
//        ]);
//        return response()->json($response, $response['statusCode']);
//    }
//
//    /**
//     * Display the specified resource.
//     *
//     * @param  \App\Models\Status  $status
//     * @return JsonResponse
//     */
//    public function show(Status $status) : JsonResponse
//    {
//        $response = $this->getResponse(__('apiResponse.show',['resource'=>'']), [
//            '' => $status
//        ]);
//        return response()->json($response, $response['statusCode']);
//    }
//
//    /**
//     * Update the specified resource in storage.
//     *
//     * @param  \Illuminate\Http\Request  $request
//     * @param  \App\Models\Status  $status
//     * @return JsonResponse
//     */
//    public function update(Request $request, Status $status) : JsonResponse
//    {
//        $status->update($request->validated());
//        $response = $this->getResponse(__('apiResponse.update',['resource'=>'']), [
//            '' => $status
//        ]);
//
//        return response()->json($response, $response['statusCode']);
//    }
//
//    /**
//     * Remove the specified resource from storage.
//     *
//     * @param  string  $status
//     * @return JsonResponse
//     */
//    public function destroy($status) : JsonResponse
//    {
//        $count = Status::destroy(explode(',',$status));
//        $response = $this->getResponse(__('apiResponse.destroy',['items'=>$count]));
//        return response()->json($response, $response['statusCode']);
//    }


}
