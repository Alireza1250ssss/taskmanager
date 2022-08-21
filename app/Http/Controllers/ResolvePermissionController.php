<?php

namespace App\Http\Controllers;

use App\Events\PermissionAdded;
use App\Models\Company;
use App\Models\Entity;
use App\Models\Field;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ResolvePermissionController extends Controller
{
    public static array $models = [
        'company' => ['class' => Company::class, 'table' => 'companies', 'primaryKey' => 'company_id'],
        'project' => ['class' => Project::class, 'table' => 'projects', 'primaryKey' => 'project_id'],
        'team' => ['class' => Team::class, 'table' => 'teams', 'primaryKey' => 'team_id'],
        'task' => ['class' => Task::class, 'table' => 'tasks', 'primaryKey' => 'task_id'],
    ];

}
