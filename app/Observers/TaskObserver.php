<?php

namespace App\Observers;

use App\Models\Company;
use App\Models\Entity;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;

class TaskObserver extends BaseObserver
{
    public function retrieved($task)
    {
        $modelItem = $task;
        //if app (this route) does not need authentication check
        if ($this->noAuth === true)
            return;

        $userId = auth()->user()->user_id;


        /** special on child class */
        $parentTeam = $modelItem->team;
        $teamEntity = Entity::query()->where([
            'key' => get_class($parentTeam),
            'model_id' => $parentTeam->team_id,
            'action' => 'read'
        ])->first();
        $inTeamMates = false;
        if (!empty($teamEntity)) {
            $teamMatesUsers = $teamEntity->users->pluck('user_id')->toArray();
            $inTeamMates = in_array($userId, $teamMatesUsers);
        }

        $owners = collect([]);
        $childModel = $modelItem;
        $relatedEntities = Entity::query()->where([
            'key' => Company::class,
            'model_id' => $childModel->team->project->company->company_id,
            'action' => 'owner'
        ])->orWhere(function ($query) use ($childModel) {
            $query->where([
                'key' => Project::class,
                'model_id' => $childModel->team->project->project_id,
                'action' => 'owner'
            ]);
        })->orWhere(function ($query) use ($childModel) {
            $query->where([
                'key' => Team::class,
                'model_id' => $childModel->team->team_id,
                'action' => 'owner'
            ]);
        })
            ->get();

        foreach ($relatedEntities as $entityItem)
            $owners = $owners->merge($entityItem->users);

        $fromOwners = in_array($userId, $owners->pluck('user_id')->toArray());

        if (!empty($modelItem->user_ref_id)) {

            if (!$fromOwners && ($userId == $modelItem->user_ref_id)) {
                $modelItem->setAttributes([]);
                return;
            }
        } elseif (!$inTeamMates && !$fromOwners) {
            $modelItem->setAttributes([]);
            return;
        }

    }


    public function updating($modelItem)
    {
        //if app (this route) does not need authentication check
        if ($this->noAuth === true)
            return;

        $userId = auth()->user()->user_id;


        /** special on child class */
        $parentTeam = $modelItem->team;
        $teamEntity = Entity::query()->where([
            'key' => get_class($parentTeam),
            'model_id' => $parentTeam->team_id,
            'action' => 'read'
        ])->first();
        $inTeamMates = false;
        if (empty($teamEntity)) {
            $teamMatesUsers = $teamEntity->users->pluck('user_id')->toArray();
            $inTeamMates = in_array($userId, $teamMatesUsers);
        }

        $owners = collect([]);
        $childModel = $modelItem;
        $relatedEntities = Entity::query()->where([
            'key' => Company::class,
            'model_id' => $childModel->team->project->company->company_id,
            'action' => 'owner'
        ])->orWhere(function ($query) use ($childModel) {
            $query->where([
                'key' => Project::class,
                'model_id' => $childModel->team->project->project_id,
                'action' => 'owner'
            ]);
        })->orWhere(function ($query) use ($childModel) {
            $query->where([
                'key' => Team::class,
                'model_id' => $childModel->team->team_id,
                'action' => 'owner'
            ]);
        })
            ->get();

        foreach ($relatedEntities as $entityItem)
            $owners = $owners->merge($entityItem->users);

        $fromOwners = in_array($userId, $owners->pluck('user_id')->toArray());


        if (!empty($modelItem->user_ref_id)) {

            if (!$fromOwners && ($userId == $modelItem->user_ref_id))
                throw new AuthorizationException('not from owners not the assigni himself');

        } elseif (!$inTeamMates && !$fromOwners)
            throw new AuthorizationException('not in teammates not owner');

    }

    public function created($model)
    {
        $class = get_class($model);
        $modelId = $model->{$model->getPrimaryKey()};
        Entity::query()->upsert([
            ['key' => $class, 'action' => 'delete', 'model_id' => $modelId],
            ['key' => $class, 'action' => 'owner', 'model_id' => $modelId],

        ], ['key', 'action', 'model_id']);

        Entity::query()->updateOrInsert([
            'key' => $class,
            'action' => 'create'
        ], ['action' => 'create']);

        $entities = Entity::query()->where([
            'key' => $class,
            'action' => 'create'
        ])->orWhere(function (Builder $builder) use ($class, $modelId) {
            $builder->where('key', $class)->where('model_id', $modelId);
        })->get()->pluck('entity_id')->toArray();

        auth()->user()->entities()->syncWithoutDetaching($entities);
        $this->setOwnersPermissions($model);
    }
}
