<?php

namespace Database\Factories;

use App\Models\Stage;
use App\Models\Status;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $users = User::all()->pluck('user_id')->toArray();
        $teams = Team::all()->pluck('team_id')->toArray();
        $statuses = Status::all()->pluck('status_id')->toArray();
        $stages = Stage::all()->pluck('stage_id')->toArray();
        return [
            'title' => $this->faker->word(),
            'description' => $this->faker->text(),
            'user_ref_id'=> $this->faker->randomElement($users),
            'team_ref_id' => $this->faker->randomElement($teams) ,
            'status_ref_id' => $this->faker->randomElement($statuses) ,
            'stage_ref_id' => $this->faker->randomElement($stages) ,
        ];
    }
}
