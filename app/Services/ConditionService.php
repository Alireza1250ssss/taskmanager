<?php


namespace App\Services;


use App\Exceptions\MethodArgumentsException;
use App\Models\Condition;
use App\Models\User;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class ConditionService
{
    public $conditions;
    public static bool $forValidator = false ;
    public $model;
    public $actions;
    private array $results;


    public function __construct($model, $conditions)
    {
        $this->conditions = $conditions->conditions;
        $this->actions = $conditions->actions;
        $this->model = $model;
    }

    public function checkConditions($passedConditions = null)
    {
        if (empty($this->conditions)) return;

        $conditions = $passedConditions ?? $this->conditions;
        $relation = $conditions->relation ;
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

            if ($relation === 'AND' && $result === false){
                $finalResult = false;
                break;
            }
            elseif ($relation !== 'AND' && $result ===true){
                $finalResult = true;
                break;
            }
        }
//        dd($finalResult,$this->results);
        if ($passedConditions)
            return $finalResult;

        if ($finalResult)
            (new ActionOnConditionService($this->actions))->callActions();
    }


    protected function IN(array $args): ?bool
    {

//        $relatingParams = ['field','values'];
//        if (self::$forValidator && $diff = array_diff($relatingParams,$args))
//            throw new MethodArgumentsException(sprintf("پارامتر های %s موجود نیستند !",implode(',',$diff)));

        // prepare parameters
        $field = $args['field'];
        $values = $args['values'];
        $can = $args['can'] ?? true;

        $fieldValue = $this->model->{$field};

        if (empty($fieldValue))
            return null;
        if (!$can)
            return !in_array($fieldValue,$values);
        return in_array($fieldValue,$values);
    }

    protected function jump(array $args) : ?bool
    {
        $field = $args['field'];
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
        if ($fieldValueBefore == $from && $fieldValue == $to && $can )
            return true;
        return false;
    }

    protected function requirement(array $args): ?bool
    {
        $field = $args['field'];

        return !empty($this->model->{$field});
    }
}
