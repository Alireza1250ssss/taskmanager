<?php


namespace App\Http\ColumnTypes;


use App\Http\ColumnTypes\Contracts\Appendable;
use App\Models\Column;
use App\Models\Task;
use App\Models\TaskMeta;

class CalcTime extends CustomField implements Appendable
{

    public function validation(): array
    {
        return [
            'type_args.field' => 'required',
            'type_args.from_value' => 'required',
            'type_args.to_value' => 'required',
        ];
    }

    public function validationMessages(): array
    {
        return [
            'type_args.field' => 'فیلد مورد نظر',
            'type_args.from_value' => 'مقدار شروع',
            'type_args.to_value' => 'مقدار پایان'
        ];
    }

    public function extractColumn(array $data): array
    {
        return [
            'params' => array_filter($data, fn($key) => in_array($key, ['field', 'from_value', 'to_value']), ARRAY_FILTER_USE_KEY),
            'nullable' => true
        ];
    }

    public function updateOrCreate(int $taskID, $value)
    {

    }

    public function append(Column $calcTimeCol, Task $task, $diff)
    {
        $lastRecord = TaskMeta::query()->where([
            'column_ref_id' => $calcTimeCol->column_id,
            'task_ref_id' => $task->task_id
        ])->first();
        if (!empty($lastRecord)) {
            $data = json_decode($lastRecord->task_value);
            $data[] = $diff;
        } else {
            $data[] = $diff;
        }
        TaskMeta::query()->updateOrCreate(
            ['column_ref_id' => $calcTimeCol->column_id, 'task_ref_id' => $task->task_id],
            ['task_key' => $calcTimeCol->title, 'task_value' => json_encode($data)]
        );
    }

    public function cut()
    {
        // TODO: Implement cut() method.
    }
}
