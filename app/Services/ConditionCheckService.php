<?php


namespace App\Services;


use App\Exceptions\PermissionException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Log;

class ConditionCheckService
{
    protected  $conditions;
    protected array $actions;
    protected string $access;
    public static ?\Throwable $conException;

    public static function checkForConditions($rolePermission, $modelItem): bool
    {
        $service = new static();

        try {
            $service->prepareToCheck($rolePermission);
            $conditionService = new ConditionService($modelItem, $service->conditions);
            $result = $conditionService->checkConditions();
//            dd($conditionService->results,$result,$service->access,ConditionService::$messages);
            if ($service->access === 'reject' and $result)
                $conditionService->CheckOnlyForReject();
            $service->prepareActions($result);
            $actionService = new ActionsService($service->actions);
            $actionService->callActions();
        } catch (AuthorizationException|PermissionException $throwable){
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
        if (!is_object($conditionParams)){

            Log::channel('dump_debug')->debug(
                "json encode fail to get object \n\r"
                .$rolePermission->pivot->condition_params." \n\r".
                serialize($conditionParams)."\n\r"
            );
            throw new AuthorizationException("دسترسی ندارید");
        }
        $this->conditions = $conditionParams->conditions;
        $this->actions = $conditionParams->actions;
    }

    protected function prepareActions(bool $result)
    {
        foreach ($this->actions as &$action) {
            if ($action->type == 'permission')
                $action->value = ($this->access == 'reject') ? $result : !$result;
            if (!$action->value){
                $action->data =  ConditionService::$messages[$result];
            }
        }
    }

}
