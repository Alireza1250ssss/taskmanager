<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ResolvePermissionController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\StageController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tymon\JWTAuth\Facades\JWTAuth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::middleware('jwt_auth')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('jwt_auth')->get('/logout', function (Request $request) {
    JWTAuth::parseToken()->invalidate();
    return response()->json([
        'status' => true,
        'message' => __('apiResponse.logout'),
        'data' => [],
        'statusCode' => 200
    ]);
});

Route::post('/register',[AuthController::class,'register'])->name('register');
Route::post('/login',[AuthController::class,'login'])->name('login');

Route::middleware(['jwt_auth'])->group(function(){

    Route::get('/notifications',[AccountController::class ,'getNotifications'])->name('get-notifications');
    Route::delete('/notifications',[AccountController::class , 'deleteNotifications'])->name('delete-notifications');
    Route::put('/notifications/mark-as-read',[AccountController::class , 'markAsRead'])->name('mark-as-read-notifications');

    Route::post('/users/{user}/permissions/{type}',[ResolvePermissionController::class , 'setPermissions'])
        ->where('type',"(fields)|(entities)")
        ->name('set-permissions');

    Route::post("/permissions/resolve",[ResolvePermissionController::class ,'resolvePermission'])->name('permissions.resolve');
    Route::apiResource('tasks.comments', CommentController::class)->shallow();

    Route::apiResource('stages', StageController::class)->only(['index']);
    Route::apiResource('statuses', StatusController::class)->only(['index']);
    Route::apiResource('entities' , EntityController::class)->only(['index','show','destroy']);

    Route::apiResources([
        'companies' => CompanyController::class ,
        'projects' => ProjectController::class ,
        'teams' => TeamController::class ,
        'users' => UserController::class ,
        'tasks' => TaskController::class ,
        'schedules' => ScheduleController::class ,
        'leave' => LeaveController::class ,
        ]);



});
