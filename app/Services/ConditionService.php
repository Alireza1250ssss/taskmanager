<?php


namespace App\Services;


use App\Models\Condition;
use Illuminate\Auth\Access\AuthorizationException;

class ConditionService
{
    public iterable $conditions;
    public $model;
    protected array $mapMethods = [
        'can_change_stage_ref_id_from_{stage_id1}_to_{stage_id2}' => 'allowStageChangeBetween',
        '{field}_is_required_when_stage_ref_id_is_being_set_to_{stage_id}' => 'StageFieldRequirement'
    ];

    public function __construct($model, $conditions)
    {
        $this->conditions = $conditions;
        $this->model = $model;
    }

    public function checkConditions()
    {
        if (empty($this->conditions)) return;

        foreach ($this->conditions as $i =>$condition) {
            $conditionRecord = Condition::find($condition->condition_id);
            if (!array_key_exists($conditionRecord->key, $this->mapMethods)) continue;
            $method = $this->mapMethods[$conditionRecord->key];
            call_user_func_array([$this, $method], $condition->params);
        }
    }


    protected function allowStageChangeBetween($stageIdBefore, $stageIdAfter)
    {
        $modelBefore = get_class($this->model)::find($this->model->task_id);

        $isStageChanging = $this->model->stage_ref_id !== $modelBefore->stage_ref_id;
        if (!$isStageChanging)
            return true;

        if ($modelBefore->stage_ref_id === $stageIdBefore && $this->model->stage_ref_id === $stageIdAfter) {
            return true;
        } else
            throw new AuthorizationException("امکان تعویض به این استیج را ندارید !");
    }

    protected function StageFieldRequirement($field , $stageId)
    {
        $modelBefore = get_class($this->model)::find($this->model->task_id);
        if ($modelBefore->stage_ref_id == $this->model->stage_ref_id || $this->model->stage_ref_id != $stageId)
            return true;

        if (!empty($this->model->{$field}))
            return true;
        throw new AuthorizationException(sprintf("%s برای تغییر به این استیج الزامیست",$field));
    }
}
