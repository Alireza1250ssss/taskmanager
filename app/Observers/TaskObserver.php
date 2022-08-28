<?php

namespace App\Observers;

use App\Models\Company;
use App\Models\Entity;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Tymon\JWTAuth\Facades\JWTAuth;

class TaskObserver
{
    public ?User $user;
    public bool $noAuth = false;
    public array $userRoles = [];


    public function __construct()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!empty($user)){
                $this->user = $user;

                // set cache time to a week
                $timeToStore = 60*60*24*7;
                $keyCache = 'user-'.$user->user_id.'-roles';
                $this->userRoles = Cache::remember($keyCache,$timeToStore,function () use ($user){
                    return $user->roles->pluck('role_id')->toArray();
                });
            }
            else
                $this->noAuth = true;
        } catch (\Exception $e) {
            $this->noAuth = true;
        }
    }


    public function retrieved(Task $task)
    {
        //if app (this route) does not need authentication check
        if ($this->noAuth === true)
            return;

        $userId = auth()->user()->user_id;
        $isAllowed = empty($task->user_ref_id) ?
            in_array($userId,$task->team->members->pluck('user_id')->toArray())
            :
            ($userId == $task->user_ref_id || in_array($userId , $task->members->pluck('user_id')->toArray()));

        if (!$isAllowed) {
            $task->setAttributes([]);
            return;
        }

    }



    public function created(Task $task)
    {
        $task->members()->syncWithoutDetaching(auth()->user());
    }
}
