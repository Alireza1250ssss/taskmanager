<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

    public function changeStageManual(Request $request , Task $task): JsonResponse
    {
        $request->validate([
           'stage_ref_id' => ['required',Rule::exists('stages','stage_id')->withoutTrashed()],
           'time' => 'required',
        ]);
        $beforeStage = $task->stage_ref_id;
        $afterStage = $request->get('stage_ref_id');

        $task->stage_ref_id = $request->get('stage_ref_id');
        $task->saveQuietly();

        $taskLog = new TaskLog();
        $taskLog->description = "field stage changed from $beforeStage to $afterStage";
        $taskLog->created_at = $request->get('time',now());
        $taskLog->task_id = $task->task_id;
        $taskLog->column = "stage_ref_id";
        $taskLog->before_value = $beforeStage;
        $taskLog->after_value = $afterStage;
        $taskLog->user_ref_id = auth()->user()->user_id;
        $taskLog->save();
        $response = $this->getResponse(__("apiResponse.store",['resource' => 'لاگ تسک']),[
           $taskLog
        ]);
        return response()->json($response,$response['statusCode']);
    }

}
