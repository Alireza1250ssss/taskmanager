<?php


namespace App\Http\ColumnTypes\Contracts;


use App\Models\Column;
use App\Models\Task;

interface Appendable
{
    public function append(Column $calcTimeCol,Task $task,$diff);

    public function cut();
}
