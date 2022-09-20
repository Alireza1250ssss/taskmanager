<?php


namespace App\Services;


use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
     * @param null $passedConditions
     * @return bool|void
     * @throws AuthorizationException
     */
    public function checkConditions($passedConditions = null)
    {
        $conditions = $passedConditions ?? $this->conditions;

        if (empty($conditions)) return true;

        $relation = $conditions->relation;

        $this->results[] = $relation;

        $finalResult = $relation === 'AND';
        unset($conditions->relation);

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
//        if ($passedConditions)
        return $finalResult;

    }


    protected function IN(array $args): ?bool
    {
        // prepare parameters
        $field = $args['field'];
        $this->allowedFields[] = $field;
        $values = $args['values'];
        $can = $args['can'] ?? true;

        $fieldValue = $this->model->{$field};

        if (empty($fieldValue))
            return null;
        if (!$can)
            $result = !in_array($fieldValue, $values);
        else
            $result = in_array($fieldValue, $values);

        $message = __("conditions." . __FUNCTION__ . "." . ($result ? 'true' : 'false'), [
            'field' => $field,
            'values' => implode(',', $values)
        ]);
        self::$messages[$result][] = $message;
        return $result;
    }

    protected function jump(array $args): ?bool
    {
        $field = $args['field'];
        $this->allowedFields[] = $field;
        $from = $args['from'];
        $to = $args['to'];
        $can = $args['can'] ?? true;

        $fieldValue = $this->model->{$field};
        $modelBefore = get_class($this->model)::find($this->model->{$this->model->getPrimaryKey()});
        if (!$modelBefore)
            throw new ModelNotFoundException('موجودیت در هنگام بررسی شرط یافت نشد');
        $fieldValueBefore = $modelBefore->{$field};
        if ($fieldValueBefore == $fieldValue)
            $result = null;
        elseif ($fieldValueBefore == $from && $fieldValue == $to && $can)
            $result = true;
        else $result = false;
        if (is_bool($result)) {
            $message = __("conditions." . __FUNCTION__ . "." . ($result ? 'true' : 'false'), [
                'field' => $field,
                'to' => $to,
                'from' => $from
            ]);
            self::$messages[$result][] = $message;
        }
        return $result;
    }

    protected function requirement(array $args): ?bool
    {
        $field = $args['field'];
        $this->allowedFields[] = $field;

        $modelBefore = get_class($this->model)::find($this->model->{$this->model->getPrimaryKey()});
        $result = !empty($this->model->{$field}) || !empty($modelBefore->{$field});
        $message = __("conditions." . __FUNCTION__ . "." . ($result ? 'true' : 'false'), [
            'field' => $field,
        ]);
        self::$messages[$result][] = $message;
        return $result;
    }

    protected function edit(array $args): bool
    {
        $field = $args['field'];
        $this->allowedFields[] = $field;
        return in_array($field, array_keys($this->model->getDirty()));
    }

    protected function set(array $args): bool
    {
        $field = $args['field'];
        $this->allowedFields[] = $field;
        return !empty($this->model->{$field});
    }

    protected function only(array $args = []): ?bool
    {
        $fields = $args['fields'] ?? $this->allowedFields;
        $can = $args['can'] ?? true;

        if (!array_diff(array_keys($this->model->getDirty()), $fields) && $can)
            return true;
        return false;
    }


    // helper methods

    /**
     * @throws AuthorizationException
     */
    public function CheckOnlyForReject()
    {
        if (!$this->only())
            throw new AuthorizationException('فیلد هایی غیر از فیلد های مجاز وارد کرده اید');
        /*$conditions = (array) $this->conditions;
        $conditions[] = (object) [
            'type' => 'only'
        ];
        $this->conditions = (object) $conditions;*/
    }
}
