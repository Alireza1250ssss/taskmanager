<?php

namespace App\Observers;

use App\Models\Leave;

class SetLeaveScheduleObserver
{


    /**
     * Handle the Leave "updated" event.
     *
     * @param Leave $leave
     * @return void
     */
    public function updated(Leave $leave)
    {
        if ($leave->status === 'accepted'){
            collect($leave->params)->each(function ($scheduleItem , $key) use($leave){
                $preparedDate = array_merge($scheduleItem , ['user_ref_id' => $leave->user_ref_id]);
                $leave->schedules()->create($preparedDate);
            });
        }
    }



}
