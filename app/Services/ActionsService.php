<?php

namespace App\Services;

use App\Exceptions\PermissionException;
use Illuminate\Database\Eloquent\Model;

class ActionsService
{
    public ?array $actions;
    public array $allowedFields = [];
    protected ?Model $model;
    // used in action that are unlocking an access for a rejected permission
    public bool $unlockAccess = false;

    public function __construct($actions,$service,$model = null)
    {
        $this->actions = $actions;
        $this->conditionCheckService = $service;
        $this->model = $model;
    }

    public function callActions()
    {
        if (empty($this->actions)) return;

        foreach ($this->actions as $action) {
            if (!method_exists($this, $action->type)) continue;
            $params = [(array)$action];
            call_user_func_array([$this, $action->type], $params);
        }
    }

    protected function permission(array $args)
    {
        $value = $args['value'] ?? false;
        $result = true;
        if (!empty($args['when'])){
            $conditionService = new ConditionService($this->model,$args['when']);
            ConditionService::$messages = [];
            $result = $conditionService->checkConditions();
            $this->allowedFields = array_merge($this->allowedFields,$conditionService->allowedFields);
        }
        if ($value === false and  $result === true)
            throw new PermissionException(
                $args['message'] ?? __('apiResponse.forbidden'),
                403,
                null,
                ConditionService::$messages[$result] ?? []
            );
        if ($value == true and $result == true)
            $this->unlockAccess = true;
    }

    protected function validation(array $args)
    {
        $value = $args['value'] ?? false;
        $result = true;
        if (!empty($args['when'])){
            $conditionService = new ConditionService($this->model,$args['when']);
            ConditionService::$messages = [];
            $result = $conditionService->checkConditions();
            $this->allowedFields = array_merge($this->allowedFields,$conditionService->allowedFields);
        }
        if ($value == false and $result == true)
            throw new PermissionException(
                $args['message'] ?? __('apiResponse.forbidden'),
                403,
                null,
                ConditionService::$messages[$result] ?? []
            );
        if ($value == true and $result == true)
            $this->unlockAccess = true;
    }
}
