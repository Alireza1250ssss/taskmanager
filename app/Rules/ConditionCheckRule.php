<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class ConditionCheckRule implements Rule
{
    private ?array $conditions;
    private string $message;

    /**
     * Create a new rule instance.
     *
     * @param $conditions
     */
    public function __construct(?array $conditions)
    {
        $this->conditions = $conditions;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $passedConditions
     * @return bool
     */
    public function passes($attribute, $value, array $passedConditions = []): bool
    {
        $passed = true;
        $conditions = !empty($passedConditions) ? $passedConditions : $this->conditions;
        unset($conditions['relation']);
        unset($conditions['actions']);
        unset($conditions['status']);
        foreach ($conditions as $condition) {

            if (empty($condition) or empty($condition['type'])) {
                $passed = isset($condition['relation']) ? $this->passes($attribute, $value, $condition) : false;
                $this->message = $this->message ?? 'ساختار شرط به درستی رعایت نشده است و یا خالی ست';
                break;
            }
            if (!method_exists($this, $condition['type'])) {
                $passed = false;
                $this->message = 'متود انتخابی در سیستم وجود ندارد';
                break;
            }
            $method = $condition['type'];
            unset($condition['type']);
            $params = $condition;
            $rules = $this->$method();
            $validator = Validator::make($params, $rules)->stopOnFirstFailure();
            if ($validator->fails()) {
                $passed = false;
                $this->message = $validator->errors()->first();
                break;
            }
        }
        return $passed;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->message ?: 'ساختار شرط درست نمی باشد';
    }

    protected function IN(): array
    {
        return
            [
                'field' => 'required',
                'values' => 'bail|required|array|filled',
                'status' => 'boolean'
            ];
    }

    protected function requirement(): array
    {
        return
            [
                'field' => 'required'
            ];
    }

    protected function only(): array
    {
        return
            [
                'fields' => 'required|array|filled',
                'status' => 'boolean'
            ];
    }

    protected function jump(): array
    {
        return
            [
                'field' => 'required',
                'from' => 'required',
                'to' => 'required',
                'status' => 'boolean'
            ];
    }

    protected function edit(): array
    {
        return [
            'field' => 'required',
            'status' => 'boolean'
        ];
    }

    protected function set(): array
    {
        return [
            'field' => 'required',
            'status' => 'boolean'
        ];
    }
}
