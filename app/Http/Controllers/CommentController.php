<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
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
        $response = $this->getResponse(__('apiResponse.index',['resource'=>'کامنت']),[
            Comment::getRecords($request->toArray())->addConstraints(function ($query) use ($task){
                $query->where('commentable_type',get_class($task))->where('commentable_id',$task->task_id)
                ->with('user');
            })->get()
        ]);
        return response()->json($response,$response['statusCode']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCommentRequest $request
     * @param Task $task
     * @return JsonResponse
     */
    public function store(StoreCommentRequest $request,Task $task) : JsonResponse
    {
        $comment = $task->comments()->create($request->validated());
        $response = $this->getResponse(__('apiResponse.store',['resource'=>'کامنت']), [
            'comment' => $comment->load('parentComment')
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Display the specified resource.
     *
     * @param Comment $comment
     * @return JsonResponse
     */
    public function show(Comment $comment) : JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.show',['resource'=>'کامنت']), [
            'comment' => $comment->load('replyComments','parentComment')
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param StoreCommentRequest $request
     * @param Comment $comment
     * @return JsonResponse
     */
    public function update(StoreCommentRequest $request, Comment $comment) : JsonResponse
    {
        $comment->update($request->validated());
        $response = $this->getResponse(__('apiResponse.update',['resource'=>'کامنت']), [
            'comment' => $comment->load('replyComments','parentComment')
        ]);

        return response()->json($response, $response['statusCode']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $comment
     * @return JsonResponse
     */
    public function destroy($comment) : JsonResponse
    {
        $count = Comment::destroy(explode(',',$comment));
        $response = $this->getResponse(__('apiResponse.destroy',['items'=>$count]));
        return response()->json($response, $response['statusCode']);
    }
}
