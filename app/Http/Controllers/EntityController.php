<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use Illuminate\Http\JsonResponse;
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
            Entity::getRecords($request->toArray())->addConstraints(function ($query) use($request){
                if ($request->filled('key')){
                    $key = ResolvePermissionController::$models[$request->get('key')]['class'];
                    $query->where('key',$key);
                }
                if ($request->filled('action'))
                    $query->where('action',$request->get('action'));
                if ($request->filled('model_id'))
                    $query->where('model_id',$request->get('model_id'));
            })->get()
        ]);
        return response()->json($response,$response['statusCode']);
    }

    /**
     * Display the specified resource.
     *
     * @param Entity $entity
     * @return JsonResponse
     */
    public function show(Entity $entity) : JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.show',['resource'=>'دسترسی موجودیت']), [
            'entity' => $entity->load('users')
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
