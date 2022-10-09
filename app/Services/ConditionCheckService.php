<?php


namespace App\Services;


use App\Exceptions\PermissionException;
use Illuminate\Auth\Access\AuthorizationException;

class ConditionCheckService
{
    protected $conditions;
    public bool $isAllowed = false;
    public array $allowedFields = [];
    protected array $actions;
    protected string $access;
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
            }

            if ($service->access === 'reject'){
                $service->checkRejectWasAllowed();
                $service->CheckOnlyForReject($modelItem);
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
        $this->actions = $conditionParams->actions ?? [];
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

    protected function CheckOnlyForReject($model){
        if (array_diff(array_keys($model->getDirty()), $this->allowedFields))
            throw new AuthorizationException('فیلد هایی غیر از فیلد های مجاز وارد کرده اید');
    }
}
