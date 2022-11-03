<?php

namespace App\Services;

use App\Exceptions\PermissionException;
use App\Http\Contracts\WithMeta;
use App\Http\Traits\AllowedFieldTrait;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ConditionCheckService
{
    use AllowedFieldTrait;

    protected $conditions;
    // put this to false by default for "reject" type permissions to be true by conditions
    public bool $isAllowed = false;
    protected string $access; // "reject" or "accept"
    public static ?\Throwable $conException;
    // store model before action getting done, and model stored currently in the database
    protected static ?Model $modelPersisting;
    protected static ?Model $modelExisting;

    public static function checkForConditions($rolePermission, $modelItem): bool
    {
        $service = new static();

        try {
            $service->prepareToCheck($rolePermission, $modelItem);

            foreach ($service->conditions as $i => $condition) {
                $service->allowedFields = [];
                $conditionService = new ConditionService($modelItem, $condition->when);
                $result = $conditionService->checkConditions();
//              dd($conditionService->results,$result,$service->access,ConditionService::$messages,self::getPersistingModel());
                if ($result === false) continue;


                $service->prepareActions($result, $condition->then);
                $actionService = new ActionsService($condition->then, $service, $modelItem);
                $actionService->callActions();
                $service->allowedFields = array_merge($service->allowedFields, $actionService->allowedFields);
                if ($actionService->unlockAccess === true) {
                    $service->isAllowed = true;
                    // check for only allowed fields if the access hasn't got unlocked so far
                    if (!array_diff(array_keys(self::$dirties), $service->allowedFields) && !$service->isOnlyAllowedFields) {
                        $service->isOnlyAllowedFields = true;
//                        Log::channel('dump_debug')->debug('access unlocked', [
//                            'index' => $i,
//                            'condition_item'=> json_encode($rolePermission)
//                        ]);
                    }
                }
            }

            if ($service->access === 'reject') {
                $service->checkRejectWasAllowed();
                $service->CheckOnlyForReject();
            }
        } catch (AuthorizationException | PermissionException $throwable) {
            if (empty(self::$conException))
                self::$conException = $throwable;
            return false;
        }
        return true;
    }

    protected function prepareToCheck($rolePermission, $modelItem)
    {
        $this->access = $rolePermission->pivot->access;
        if (empty($rolePermission->pivot->condition_params) && $this->access === 'reject') {
            throw new AuthorizationException("دسترسی ندارید");
        }

        $conditionParams = json_decode($rolePermission->pivot->condition_params);
        $this->conditions = $conditionParams->conditions ?? [];

        static::setExistingModel($modelItem);
        static::setPersistingModel($modelItem);

        self::$dirties = ($modelItem instanceof WithMeta) ?
            self::getPersistingModel()->getAllDirty() : self::getPersistingModel()->getDirty();

        $this->mergeAllowedFieldForPermission($rolePermission->key);
    }

    protected function prepareActions(bool $result, &$then)
    {
        foreach ($then as &$action)
            if ($action->type == 'permission' or $action->type == 'validation')
                $action->value = ($this->access == 'reject');
    }

    protected function checkRejectWasAllowed()
    {
        if ($this->isAllowed !== true)
            throw new AuthorizationException('دسترسی شما توسط هیچکدام از شرایط باز نشده است');
    }

    public static function getPersistingModel(): ?Model
    {
        return !empty(static::$modelPersisting) ? static::$modelPersisting : null;
    }

    protected static function setPersistingModel(Model $modelItem)
    {
        if (!($modelItem instanceof WithMeta)) {
            static::$modelPersisting = $modelItem;
            return;
        }

        $modelItem->loadMissing($modelItem->getMetaRelation());
        $modelItem = clone $modelItem;
        $modelItem->mergeRawMeta()->syncMetaWithRequest();
        static::$modelPersisting = $modelItem;
    }

    public static function getExistingModel(): ?Model
    {
        return !empty(static::$modelExisting) ? static::$modelExisting : null;
    }

    protected static function setExistingModel(Model $modelItem)
    {
        $modelExisting = get_class($modelItem)::find($modelItem->{$modelItem->getPrimaryKey()});
        if (!($modelExisting instanceof WithMeta)) {
            static::$modelExisting = $modelExisting;
            return;
        }

        static::$modelExisting = $modelExisting->mergeRawMeta();
    }

}
