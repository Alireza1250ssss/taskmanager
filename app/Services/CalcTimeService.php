<?php


namespace App\Services;


use App\Http\ColumnTypes\CalcTime;
use App\Http\ColumnTypes\Contracts\Appendable;
use App\Models\Column;
use App\Models\Task;
use App\Models\TaskLog;
use App\Models\TaskMeta;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CalcTimeService
{
    protected ?TaskLog $taskLog;
    protected ?Task $task;
    protected Appendable $fieldType;
    protected static $calcTimeCols ;

    public function __construct($task,$taskLog)
    {
        $this->task = $task;
        $this->taskLog = $taskLog;
        $this->fieldType = new CalcTime();
    }

    public static function hasCalcTimeField(Task $task): bool
    {
        $calcTimeColumns = Column::query()
            ->where('type','calc-time')->where('card_type_ref_id',$task->card_type_ref_id)
            ->get();
        if ($calcTimeColumns->isNotEmpty())
            self::$calcTimeCols = $calcTimeColumns;
        return $calcTimeColumns->isNotEmpty();
    }

    public function calculate()
    {
        if (empty(self::$calcTimeCols)) return;
        foreach (self::$calcTimeCols as $calcTimeCol){
            ['field' => $field , 'from_value' => $from_value , 'to_value'=> $to_value] = $calcTimeCol->params;
            if ($this->taskLog->column == $field && $this->taskLog->before_value == $from_value && $this->taskLog->after_value == $to_value){
                /** @var Carbon $timeAfter */
                $timeAfter = $this->taskLog->created_at;
                $beforeLog = TaskLog::query()->where([
                    'task_id' => $this->taskLog->task_id,
                    'column' => $this->taskLog->column,
                    'after_value' => $this->taskLog->before_value
                ])->orderBy('created_at','DESC')->first();
                if (empty($beforeLog)) continue;
                /** @var Carbon $timeBefore */
                $timeBefore = $beforeLog->created_at;
//                Log::channel('dump_debug')->debug("timestamp :  ".$timeBefore->timestamp. " date : ".$timeBefore->toDateTimeString());
//                Log::channel('dump_debug')->debug("timestamp :  ".$timeAfter->timestamp. " date : ".$timeAfter->toDateTimeString());
//                Log::channel('dump_debug')->debug("diff in seconds :  " . ($timeBefore->timestamp - $timeAfter->timestamp));
                $diff = $timeBefore->diffInRealMinutes($timeAfter);

                $this->fieldType->append($calcTimeCol,$this->task,$diff);
            }
        }
    }
}
