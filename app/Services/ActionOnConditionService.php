<?php


namespace App\Services;


use Illuminate\Auth\Access\AuthorizationException;

class ActionOnConditionService
{
    public ?array $actions;

    public function __construct($actions)
    {
        $this->actions = $actions;
    }

    public function callActions()
    {
        if (empty($this->actions)) return;

        foreach ($this->actions as $action)
        {
            if (!method_exists($this,$action->type)) continue;
            $params = [(array)$action];
            call_user_func_array([$this,$action->type],$params);
        }
    }

    protected function permission(array $args)
    {
        if ($args['value'] === false)
            throw new AuthorizationException($args['message'] ?? __('apiResponse.forbidden'));
    }
}
