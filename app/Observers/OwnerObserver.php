<?php

namespace App\Observers;

use App\Models\Company;
use App\Models\Project;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Tymon\JWTAuth\Facades\JWTAuth;

class OwnerObserver
{
    public static ?User $user;
    public bool $noAuth = false;
    public array $userRoles = [];
    public array $models = [
        Company::class => "company",
        Project::class => "project",
        Team::class => "team",
        Task::class => "task",
    ];

    public function __construct()
    {
        try {
            $user = self::$user ?? JWTAuth::parseToken()->authenticate();
            if (!empty($user)) {
                self::$user = $user;

                // set cache time to a week
                $timeToStore = 60 * 60 * 24 * 7;
                $keyCache = 'user-' . $user->user_id . '-roles';
                $this->userRoles = Cache::remember($keyCache, $timeToStore, function () use ($user) {
                    return $user->roles->pluck('role_id')->toArray();
                });
            } else
                $this->noAuth = true;
        } catch (\Exception $e) {
            $this->noAuth = true;
        }
    }

    public function created($modelItem)
    {
        $modelName = $this->models[get_class($modelItem)];
        $modelId = $modelItem->{$modelItem->getPrimaryKey()};

        $baseRole = Role::query()->where('name','base-role')->first();
        if (empty($baseRole))
            return;
        RoleUser::query()->updateOrCreate(
            [
                'user_ref_id' => auth()->user()->user_id , 'role_ref_id' => $baseRole->role_id,
                'rolable_type' => $modelName , 'rolable_id' => $modelId
            ]
        );

    }
}
