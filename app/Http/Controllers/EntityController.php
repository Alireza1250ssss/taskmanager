<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Request;

class EntityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) : JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.index',['resource'=>'دسترسی موجودیت']),[
            Entity::getRecords($request->toArray())->get()
        ]);
        return response()->json($response,$response['statusCode']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function store(Request $request) : JsonResponse
    {
        $entity = Entity::create($request->validated());
        $response = $this->getResponse(__('apiResponse.store',['resource'=>'']), [
            '' => $entity
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Entity  $entity
     * @return JsonResponse
     */
    public function show(Entity $entity) : JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.show',['resource'=>'']), [
            '' => $entity
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Entity  $entity
     * @return JsonResponse
     */
    public function update(Request $request, Entity $entity) : JsonResponse
    {
        $entity->update($request->validated());
        $response = $this->getResponse(__('apiResponse.update',['resource'=>'']), [
            '' => $entity
        ]);

        return response()->json($response, $response['statusCode']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $entity
     * @return JsonResponse
     */
    public function destroy($entity) : JsonResponse
    {
        $count = Entity::destroy(explode(',',$entity));
        $response = $this->getResponse(__('apiResponse.destroy',['items'=>$count]));
        return response()->json($response, $response['statusCode']);
    }
}
