<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) : JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.index',['resource'=>'مرحله']),[
            Stage::getRecords($request->toArray())->get()
        ]);
        return response()->json($response,$response['statusCode']);
    }


//
//
//    /**
//     * Store a newly created resource in storage.
//     *
//     * @param  \Illuminate\Http\Request  $request
//     * @return JsonResponse
//     */
//    public function store(Request $request) : JsonResponse
//    {
//        $stage = Stage::create($request->validated());
//        $response = $this->getResponse(__('apiResponse.store',['resource'=>'']), [
//            '' => $stage
//        ]);
//        return response()->json($response, $response['statusCode']);
//    }
//
//    /**
//     * Display the specified resource.
//     *
//     * @param  \App\Models\Stage  $stage
//     * @return JsonResponse
//     */
//    public function show(Stage $stage) : JsonResponse
//    {
//        $response = $this->getResponse(__('apiResponse.show',['resource'=>'']), [
//            '' => $stage
//        ]);
//        return response()->json($response, $response['statusCode']);
//    }
//
//    /**
//     * Update the specified resource in storage.
//     *
//     * @param  \Illuminate\Http\Request  $request
//     * @param  \App\Models\Stage  $stage
//     * @return JsonResponse
//     */
//    public function update(Request $request, Stage $stage) : JsonResponse
//    {
//        $stage->update($request->validated());
//        $response = $this->getResponse(__('apiResponse.update',['resource'=>'']), [
//            '' => $stage
//        ]);
//
//        return response()->json($response, $response['statusCode']);
//    }
//
//    /**
//     * Remove the specified resource from storage.
//     *
//     * @param  string  $stage
//     * @return JsonResponse
//     */
//    public function destroy($stage) : JsonResponse
//    {
//        $count = Stage::destroy(explode(',',$stage));
//        $response = $this->getResponse(__('apiResponse.destroy',['items'=>$count]));
//        return response()->json($response, $response['statusCode']);
//    }
//
//

}
