<?php


namespace App\Services;


use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ConditionService
{
    public $conditions;
    private string $access;
    private array $allowedFields = [];
    public $model;
    public $actions;
    private array $results;


    public function __construct($model, $conditions, $access = 'accept')
    {

        $this->conditions = $conditions->conditions;
        // prepare the way permissions must operate based on access field
        foreach ($conditions->actions as &$action)
            $action->value = ($action->type == 'permission' && $access == 'accept') ? false : true;

        $this->actions = $conditions->actions;
        $this->access = $access;
        $this->model = $model;
    }

    /**
     * @param null $passedConditions
     * @return bool|void
     * @throws AuthorizationException
     */
    public function checkConditions($passedConditions = null)
    {
        if (empty($this->conditions)) return;

        $conditions = $passedConditions ?? $this->conditions;
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
//        dd($finalResult, $this->results, $this->access, $this->actions);
        if ($passedConditions)
            return $finalResult;

        if (!$finalResult and $this->access == 'reject') {
            foreach ($this->actions as &$action)
                if (($action->type == 'permission'))
                    $action->value =   false ;
            (new ActionOnConditionService($this->actions))->callActions();
        } elseif ($finalResult and $this->access == 'accept')
            (new ActionOnConditionService($this->actions))->callActions();

        if ($this->access === 'reject')
            $this->CheckOnlyForReject();
    }


    protected function IN(array $args): ?bool
    {

//        $relatingParams = ['field','values'];
//        if (self::$forValidator && $diff = array_diff($relatingParams,$args))
//            throw new MethodArgumentsException(sprintf("پارامتر های %s موجود نیستند !",implode(',',$diff)));

        // prepare parameters
        $field = $args['field'];
        $this->allowedFields[] = $field;
        $values = $args['values'];
        $can = $args['can'] ?? true;

        $fieldValue = $this->model->{$field};

        if (empty($fieldValue))
            return null;
        if (!$can)
            return !in_array($fieldValue, $values);
        return in_array($fieldValue, $values);
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
            return null;
        if ($fieldValueBefore == $from && $fieldValue == $to && $can)
            return true;
        return false;
    }

    protected function requirement(array $args): ?bool
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
    protected function CheckOnlyForReject()
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
