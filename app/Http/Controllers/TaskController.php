<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) : JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.index',['resource'=>'تسک']),[
            Task::getRecords($request->toArray())->get()
        ]);
        return response()->json($response,$response['statusCode']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreTaskRequest $request
     * @return JsonResponse
     */
    public function store(StoreTaskRequest $request) : JsonResponse
    {
        $task = Task::create($request->validated());
        $task->teams()->sync($request->get('teams'));
        $response = $this->getResponse(__('apiResponse.store',['resource'=>'تسک']), [
            'task' => $task->load('teams','taskMetas')
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Display the specified resource.
     *
     * @param Task $task
     * @return JsonResponse
     */
    public function show(Task $task) : JsonResponse
    {
        $task->mergeMeta('taskMetas');
        $response = $this->getResponse(__('apiResponse.show',['resource'=>'تسک']), [
            'task' => $task->load('teams')
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateTaskRequest $request
     * @param Task $task
     * @return JsonResponse
     */
    public function update(UpdateTaskRequest $request, Task $task) : JsonResponse
    {
        $task->update($request->validated());
        if ($request->filled('teams')){
            $task->teams()->sync($request->get('teams'));
        }
        $response = $this->getResponse(__('apiResponse.update',['resource'=>'تسک']), [
            'task' => $task->load('teams','taskMetas')
        ]);

        return response()->json($response, $response['statusCode']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $task
     * @return JsonResponse
     */
    public function destroy($task) : JsonResponse
    {
        $count = Task::destroy(explode(',',$task));
        $response = $this->getResponse(__('apiResponse.destroy',['items'=>$count]));
        return response()->json($response, $response['statusCode']);
    }
}
