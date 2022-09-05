<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ResolvePermissionController;
use App\Http\Controllers\RoleController;
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

// routes to work and control database seeding & migrations
Route::get('/migrate/status',[DatabaseController::class,'migrateStatus']);
Route::get('/migrate/run',[DatabaseController::class,'migrateRun']);
Route::get('/migrate/fresh',[DatabaseController::class,'migrateFresh']);
Route::get('/db/seed',[DatabaseController::class,'dbSeed']);
Route::get('/composer/autoload',[DatabaseController::class,'dumpAutoload']);

Route::middleware('jwt_auth')->post('/logout', function (Request $request) {
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

    Route::get('/permissions/insert',[ResolvePermissionController::class,'insertKeys'])->name('permissions.insert-keys');

    Route::get('/notifications',[AccountController::class ,'getNotifications'])->name('get-notifications');
    Route::delete('/notifications',[AccountController::class , 'deleteNotifications'])->name('delete-notifications');
    Route::put('/notifications/mark-as-read',[AccountController::class , 'markAsRead'])->name('mark-as-read-notifications');
    Route::post('set-watcher/{model}/{modelId}',[AccountController::class ,'setWatcher'])->name('set-watcher');
    Route::post('set-member/{model}/{modelId}',[AccountController::class ,'setMember'])->name('set-member');
    Route::get('/get-watcher/{model}/{modelId}',[AccountController::class,'getWatchers'])->name('get-watchers');
    Route::get('/get-member/{model}/{modelId}',[AccountController::class,'getMembers'])->name('get-members');


    Route::apiResource('tasks.comments', CommentController::class)->shallow();

    Route::apiResource('stages', StageController::class)->only(['index']);
    Route::apiResource('statuses', StatusController::class)->only(['index']);
    Route::apiResource('entities' , EntityController::class)->only(['index','show','destroy']);
    Route::get('permissions',[RoleController::class , 'getPermissions'])->name('permissions.index');
    Route::put('/companies/{company}/assign',[CompanyController::class,'addAssign'])->name('companies.add-viewer');
    Route::put('/projects/{project}/assign',[ProjectController::class,'addAssign'])->name('projects.add-viewer');
    Route::put('/teams/{team}/assign',[TeamController::class,'addAssign'])->name('teams.add-viewer');
    Route::get('/tasks/take/{task}',[TaskController::class , 'takeTask'])->name('tasks.take-task');
    Route::put('/tasks/{task}/change-order',[TaskController::class , 'taskReorder'])->name('tasks.reorder');

    Route::put('/roles/assign',[RoleController::class ,'setRolesForUser'])->name('roles.assign');
    Route::put('/roles/detach',[RoleController::class ,'detachRoleFromUser'])->name('roles.detach');
    Route::put('/roles/{role}/permissions/attach-condition',[RoleController::class , 'addCondition'])->name('roles.permissions.attach-condition');
    Route::get('/columns/{model}',[RoleController::class , 'getColumnsFor'])->name('columns.show')
        ->where('model', '(company)|(project)|(team)|(task)');

    Route::apiResources([
        'companies' => CompanyController::class ,
        'projects' => ProjectController::class ,
        'teams' => TeamController::class ,
        'users' => UserController::class ,
        'tasks' => TaskController::class ,
        'schedules' => ScheduleController::class ,
        'leave' => LeaveController::class ,
        'roles' => RoleController::class ,
        ]);



});
