<?php

namespace App\Http\Controllers\Attachment;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class TaskAttachmentController extends AttachmentController
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
        $response = $this->getResponse(__('apiResponse.index',['resource'=>'پیوست']),[
            Attachment::getRecords($request->toArray())->addConstraints(function ($query)use($task){
                $query->where([
                    'attachable_type' => get_class($task),
                    'attachable_id' => $task->task_id
                ]);
            })->get()
        ]);
        return response()->json($response,$response['statusCode']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param Task $task
     * @return JsonResponse
     */
    public function store(Request $request,Task $task) : JsonResponse
    {
        $request->validate([
           'attachment' => 'required|mimes:jpg,jpeg,csv,gif,mp4,png,pdf,svg,tar,txt'
        ]);

        $uploadedFile = $request->file('attachment');
        $path = $uploadedFile->store('tasks');

        $attachment = $task->attachments()->create([
            'path' => $path,
            'size' => $uploadedFile->getSize(),
            'extension' => $uploadedFile->extension(),
            'user_ref_id' => auth()->user()->user_id
        ]);
        $response = $this->getResponse(__('apiResponse.store',['resource'=>'پیوست']), [
            'attachment' => $attachment
        ]);
        return response()->json($response, $response['statusCode']);
    }

}
