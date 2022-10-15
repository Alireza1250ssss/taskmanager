<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskLogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param Task $task
     * @return JsonResponse
     */
    public function index(Request $request,Task $task) : JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.index',['resource'=>'تسک لاگ']),[
            TaskLog::getRecords($request->toArray())->addConstraints(function ($query)use($task){
                $query->where('task_id',$task->task_id);
            })->get()
        ]);
        return response()->json($response,$response['statusCode']);
    }

}
