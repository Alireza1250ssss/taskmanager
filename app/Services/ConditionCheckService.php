<?php

namespace App\Services;

use App\Exceptions\PermissionException;
use Illuminate\Auth\Access\AuthorizationException;

class ConditionCheckService
{
    protected $conditions;
    // put this to false by default for "reject" type permissions to be true by conditions
    public bool $isAllowed = false;
    // boolean to check only allowed field (which are defined on the condition access is getting unlocked) is entered
    public bool $isOnlyAllowedFields = false;
    public array $allowedFields = [];
    protected string $access; // "reject" or "accept"
    public static ?\Throwable $conException;

    public static function checkForConditions($rolePermission, $modelItem): bool
    {
        $service = new static();

        try {
            $service->prepareToCheck($rolePermission);

            foreach ($service->conditions as $condition) {
                $conditionService = new ConditionService($modelItem, $condition->when);
                $result = $conditionService->checkConditions();
                $service->allowedFields = array_merge($service->allowedFields,$conditionService->allowedFields);
//              dd($conditionService->results,$result,$service->access,ConditionService::$messages);
                if ($result === false) continue;

                $service->prepareActions($result, $condition->then);
                $actionService = new ActionsService($condition->then, $service, $modelItem);
                $actionService->callActions();
                $service->allowedFields = array_merge($service->allowedFields,$actionService->allowedFields);
                if ($actionService->unlockAccess === true){
                    $service->isAllowed = true;
                    // check for only allowed fields if the access hasn't got unlocked so far
                    if (!array_diff(array_keys($modelItem->getDirty()), $service->allowedFields) && !$service->isOnlyAllowedFields)
                        $service->isOnlyAllowedFields = true;
                }
            }

            if ($service->access === 'reject'){
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

    protected function prepareToCheck($rolePermission)
    {
        $this->access = $rolePermission->pivot->access;
        if (empty($rolePermission->pivot->condition_params) && $this->access === 'reject') {
            throw new AuthorizationException("دسترسی ندارید");
        }

        $conditionParams = json_decode($rolePermission->pivot->condition_params);
        $this->conditions = $conditionParams->conditions ?? [];
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

    protected function CheckOnlyForReject(){
        if (!$this->isOnlyAllowedFields)
            throw new AuthorizationException('فیلد هایی غیر از فیلد های مجاز وارد کرده اید');
    }
}
