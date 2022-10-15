<?php

namespace App\Http\Controllers;

use App\Events\CommitIDSentEvent;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Models\TaskLog;
use App\Models\TaskMeta;
use App\Notifications\TaskWatcherNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

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
        $tasks = Task::getRecords($request->toArray())->addConstraints(function ($query){
            $query->with('watchers');
            $query->withCount('comments');
        })->get();
        cleanCollection($tasks);
        dd($tasks);
        foreach ($tasks as &$task) {
            $task->mergeMeta('taskMetas');
        }
        $response = $this->getResponse(__('apiResponse.index',['resource'=>'تسک']),[
            $tasks
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
        $task->setLastOrderInStage();
        if ($request->filled('task_metas'))
            TaskMeta::updateMeta($task,$request->get('task_metas'));
        if ($request->filled('watchers'))
            $task->watchers()->sync($request->get('watchers'));
        $task->mergeMeta('taskMetas');
        $response = $this->getResponse(__('apiResponse.store',['resource'=>'تسک']), [
            'task' => $task->load('team','stage','status','watchers')
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
            'task' => $task->load('team.project.company','status','stage','comments','watchers')
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

        //check if the stage is being updated to set a log and send notification to its watchers
        if (array_key_exists('stage_ref_id' , $request->validated())) {
            $task->setLastOrderInStage();
//            $taskLog = TaskLog::stageChangeLog($task, $request);
//            Notification::send($task->watchers , new TaskWatcherNotification($taskLog));
        }

        if ($task->stage->name === 'review')
            $task->setDoneAt();

        if ($request->filled('task_metas')) {
            TaskMeta::updateMeta($task,$request->get('task_metas'));
        }

        $task->mergeMeta('taskMetas');

        if (!empty($task->commit_id) && empty($task->commit_message)) {
            //send event to get and fill the commit message automatically
            CommitIDSentEvent::dispatch($task);
        }

        $response = $this->getResponse(__('apiResponse.update',['resource'=>'تسک']), [
            'task' => $task->load('team','status','stage','watchers')
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

    /**
     * @param Task $task
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function takeTask(Task $task): JsonResponse
    {
        if (!empty($task->user_ref_id))
            throw new AuthorizationException();
        $task->update([
           'user_ref_id' => auth()->user()->user_id
        ]);
        $response = $this->getResponse('تسک با به شما موفقیت اختصاص داده شد');
        return response()->json($response,$response['statusCode']);
    }

    /**
     * @param Request $request
     * @param Task $task
     * @return JsonResponse
     */
    public function taskReorder(Request $request,Task $task): JsonResponse
    {
        $request->validate([
            'previous_task_order' => 'required|numeric' ,
            'next_task_order' => 'required|numeric' ,
        ]);
        \auth()->user()->authorizeFor('can_reorder_task_in', $task);
        $orderInStage =  getFloatBetween($request->get('previous_task_order') ,$request->get('next_task_order'));

        $task->order = $orderInStage;
        $task->saveQuietly();

        $response = $this->getResponse("ترتیب با موفقیت تغییر یافت",[
            'task' => $task
        ]);
        return response()->json($response,$response['statusCode']);
    }
}
