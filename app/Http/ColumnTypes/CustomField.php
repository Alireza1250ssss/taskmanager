<?php


namespace App\Http\ColumnTypes;


use App\Models\Column;
use App\Models\Company;
use App\Models\CardType;
use App\Models\TaskMeta;
use Illuminate\Auth\Access\AuthorizationException;

abstract class CustomField
{
    protected ?Column $relatedColumn;

    public function __construct(Column $column)
    {
        $this->relatedColumn = $column;
    }

    abstract public function validation(): array;
    abstract public function validationMessages(): array;

    public function updateOrCreate(int $taskID ,$value)
    {
        TaskMeta::query()->updateOrCreate(
            ['task_ref_id' => $taskID, 'column_ref_id' => $this->relatedColumn->column_id],
            ['task_value' => $value, 'task_key' => $this->relatedColumn->title]
        );
    }
}
