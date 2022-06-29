<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\Team;
use Illuminate\Database\Seeder;

class TaskTeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tasks = Task::all();
        $teams = Team::all();
        $tasks->each(function ($item , $key) use ($teams){
           $item->teams()->sync($teams->random(random_int(2,4)));
        });
    }
}
