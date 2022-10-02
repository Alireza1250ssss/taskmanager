<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreColumnRequest;
use App\Models\Column;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class ColumnController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) : JsonResponse
    {
        $request->validate([
           'personal_ref_id' => ['required']
        ]);
        $response = $this->getResponse(__('apiResponse.index',['resource'=>'فیلد']),[
            Column::getRecords($request->toArray())->addConstraints(function ($query) use($request){
                $query->where('personal_ref_id',$request->get('personal_ref_id'));
            })->get()
        ]);
        return response()->json($response,$response['statusCode']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreColumnRequest $request
     * @return JsonResponse
     */
    public function store(StoreColumnRequest $request) : JsonResponse
    {
        $column = Column::create($request->validated());
        $response = $this->getResponse(__('apiResponse.store',['resource'=>'فیلد']), [
            'column' => $column
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Display the specified resource.
     *
     * @param Column $column
     * @return JsonResponse
     */
    public function show(Column $column) : JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.show',['resource'=>'فیلد']), [
            'column' => $column
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Column $column
     * @return JsonResponse
     */
//    public function update(Request $request, Column $column) : JsonResponse
//    {
//        $column->update($request->validated());
//        $response = $this->getResponse(__('apiResponse.update',['resource'=>'فیلد']), [
//            'column' => $column
//        ]);
//
//        return response()->json($response, $response['statusCode']);
//    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $column
     * @return JsonResponse
     */
    public function destroy($column) : JsonResponse
    {
        $count = Column::destroy(explode(',',$column));
        $response = $this->getResponse(__('apiResponse.destroy',['items'=>$count]));
        return response()->json($response, $response['statusCode']);
    }
}
