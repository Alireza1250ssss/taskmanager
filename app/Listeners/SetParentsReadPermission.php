<?php

namespace App\Listeners;

use App\Events\PermissionAdded;
use App\Models\Company;
use App\Models\Entity;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;

class SetParentsReadPermission
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param PermissionAdded $event
     * @return void
     */
    public function handle(PermissionAdded $event)
    {
        foreach ($event->entities as $entity) {
            if ($entity->action === 'read') {
                $model = $entity->key::find($entity->model_id); //Task:: for example

                if (get_class($model) == Task::class)
                    $this->setParentsForTask($model, $event->user);
                elseif (get_class($model) == Team::class)
                    $this->setParentsForTeam($model, $event->user);
                elseif (get_class($model) == Project::class)
                    $this->setParentsForProject($model, $event->user);
            }
        }
    }

    protected function setParentsForTask(Task $task, User $user)
    {
        $parentTeam = Team::withoutEvents(function () use ($task) {
            return Team::find($task->team_ref_id);
        });

        $relatedEntity = Entity::query()->where('key', get_class($parentTeam))
            ->where('model_id', $parentTeam->team_id)->where('action', 'read')->first();

        if (!empty($relatedEntity)) {
            $relatedEntity->users()->attach($user->user_id);
        }
        $this->setParentsForTeam($parentTeam, $user);
    }

    protected function setParentsForTeam(Team $team, User $user)
    {
        $parentProject = Project::withoutEvents(function () use ($team) {
            return Project::find($team->project_ref_id);
        });

        $relatedEntity = Entity::query()->where('key', get_class($parentProject))
            ->where('model_id', $parentProject->project_id)->where('action', 'read')->first();

        if (!empty($relatedEntity)) {
            $relatedEntity->users()->attach($user->user_id);
        }

        $this->setParentsForProject($parentProject , $user);
    }

    protected function setParentsForProject(Project $project, User $user)
    {
        $parentCompany = Company::withoutEvents(function () use ($project){
           return Company::find($project->company_ref_id);
        });

        $relatedEntity = Entity::query()->where('key', get_class($parentCompany))
            ->where('model_id', $parentCompany->company_id)->where('action', 'read')->first();

        if (!empty($relatedEntity)) {
            $relatedEntity->users()->attach($user->user_id);
        }
    }
}
