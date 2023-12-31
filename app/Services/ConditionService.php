<?php

namespace App\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class ConditionService
{
    public $conditions;
    public array $allowedFields = [];
    public $model;
    public array $results;
    public static array $messages = [];


    public function __construct($model, $conditions)
    {
        $this->conditions = $conditions;
        $this->model = $model;
    }

    /**
     * main method checking conditions passed and return the final bool result
     * @param null $passedConditions
     * @return bool
     */
    public function checkConditions($passedConditions = null): bool
    {
        $conditions = $passedConditions ?? $this->conditions;

        if (empty($conditions)) return true;

        $relation = $conditions->relation;

        $this->results[] = $relation;

        $finalResult = $relation === 'AND';
        unset($conditions->relation);
        $status = $conditions->status ?? true;
        unset($conditions->status);

        foreach ($conditions as $i => $condition) {
            if (!empty($condition->relation))
                $result = $this->checkConditions($condition);
            else {
                $method = $condition->type;
                if (!method_exists($this, $method)) continue;
                unset($condition->type);
                $params = [(array)$condition];
                $result = call_user_func_array([$this, $method], $params);
                $this->results[] = $result;
            }

            if ($result === null) continue;

            if ($relation === 'AND' && $result === false) {
                $finalResult = false;
                break;
            } elseif ($relation !== 'AND' && $result === true) {
                $finalResult = true;
                break;
            }
        }
//        dd($finalResult, $this->results, self::$messages, $this->actions);

        return $status ? $finalResult : !$finalResult;
    }


    protected function IN(array $args): bool
    {
        // prepare parameters
        $field = $args['field'];
        $this->allowedFields[] = $field;
        $values = $args['values'];
        $was = $args['was'] ?? false;
        $status = $args['status'] ?? true;

        $model = !$was ? ConditionCheckService::getPersistingModel() : ConditionCheckService::getExistingModel();
        $fieldValue = $model->{$field};

        $result = in_array($fieldValue, $values);
        $returningResult = $status ? $result : !$result;
        $message = __("conditions." . __FUNCTION__ . "." . ($result ? 'true' : 'false'), [
            'field' => $args['message_field'] ?? $field,
            'values' => implode(',',$args['message_values'] ?? $values)
        ]);
        self::$messages[$returningResult][] = $message;
        return $returningResult;
    }

    protected function jump(array $args): bool
    {
        $field = $args['field'];
        $this->allowedFields[] = $field;
        $from = $args['from'];
        $to = $args['to'];
        $status = $args['status'] ?? true;

        $modelPersisting = ConditionCheckService::getPersistingModel();
        $fieldValue = $modelPersisting->{$field};
        $modelBefore = ConditionCheckService::getExistingModel();
        if (!$modelBefore)
            throw new ModelNotFoundException('موجودیت در هنگام بررسی شرط یافت نشد');
        $fieldValueBefore = $modelBefore->{$field};

        if ($fieldValueBefore == $from && $fieldValue == $to)
            $result = true;
        else $result = false;
        $returningResult = $status ? $result : !$result;
        $message = __("conditions." . __FUNCTION__ . "." . ($result ? 'true' : 'false'), [
            'field' => $args['message_field'] ?? $field,
            'to' => $args['message_to'] ?? $to,
            'from' => $args['message_from'] ?? $from
        ]);
        self::$messages[$returningResult][] = $message;

        return $returningResult;
    }

    protected function requirement(array $args): ?bool
    {
        $field = $args['field'];
        $status = $args['status'] ?? true;
        $this->allowedFields[] = $field;

        $modelExisting = ConditionCheckService::getExistingModel();
        $modelPersisting = ConditionCheckService::getPersistingModel();
        $result = !empty($modelPersisting->{$field}) || !empty($modelExisting->{$field});
        $returningResult = $status ? $result : !$result;
        $message = __("conditions." . __FUNCTION__ . "." . ($result ? 'true' : 'false'), [
            'field' => $args['message_field'] ?? $field,
        ]);
        self::$messages[$returningResult][] = $message;

        return $returningResult;
    }

    protected function edit(array $args): bool
    {
        $field = $args['field'];
        $status = $args['status'];
        $this->allowedFields[] = $field;

        $result = in_array($field, array_keys(ConditionCheckService::$dirties));
        $returningResult = $status ? $result : !$result;
        $message = __("conditions." . __FUNCTION__ . "." . ($result ? 'true' : 'false'), [
            'field' => $args['message_field'] ?? $field,
        ]);
        self::$messages[$returningResult][] = $message;
        return $returningResult;
    }

    protected function set(array $args): bool
    {
        $field = $args['field'];
        $was = $args['was'] ?? false;
        $status = $args['status'] ?? true;
        $this->allowedFields[] = $field;
        $model = !$was ? ConditionCheckService::getPersistingModel() : ConditionCheckService::getExistingModel();
        $isset = !empty($model->{$field});
        $returningResult = $status ? $isset : !$isset;
        $message = __("conditions." . 'requirement' . "." . ($isset ? 'true' : 'false'), [
            'field' => $args['message_field'] ?? $field,
        ]);
        self::$messages[$returningResult][] = $message;

        return $returningResult;
    }

    protected function clientIn(array $args) : bool
    {
        $status = $args['status'] ?? true;
        $clientType = $args['client_type'];
        if (!is_array($clientType))
            $clientType = [$clientType];

        $requestClient = request()->get('ClientName','web');

        $result = false;
        if (in_array($requestClient,$clientType))
            $result = true;
        $returningResult = $status ? $result : !$result;
        $message = __("conditions." . __FUNCTION__ . "." . ($result ? 'true' : 'false'), [
            'client' => implode(',',$clientType),
        ]);
        self::$messages[$returningResult][] = $message;

        return $returningResult;
    }

    protected function boolean(array $args)
    {
        return $args['value'];
    }

    protected function only(array $args = []): bool
    {
        $fields = $args['fields'] ?? $this->allowedFields;
        $status = $args['status'] ?? true;

        if (!array_diff(array_keys(ConditionCheckService::$dirties), $fields) && $status)
            return true;
        return false;
    }

    protected function belongsToAuthUser(array $args = []): ?bool
    {
        $relation = 'user';
        $status = $args['status'] ?? true;
        $modelPersisting = ConditionCheckService::getPersistingModel();
        if (!$modelPersisting->isRelation($relation) || !($modelPersisting->$relation() instanceof BelongsTo))
            return null;
        $relationInstance = $modelPersisting->$relation;
        $result = (!is_null($relationInstance) && $relationInstance->is(auth()->user()));
        $returningResult = $status ? $result : !$result;
        $message = __("conditions." . __FUNCTION__ . "." . ($result ? 'true' : 'false'));
        self::$messages[$returningResult][] = $message;

        return $returningResult;
    }
}
