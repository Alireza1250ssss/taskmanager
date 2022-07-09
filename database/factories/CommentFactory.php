<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $users = User::all()->pluck('user_id')->toArray();
        $tasks = Task::all()->pluck('task_id')->toArray();
        return [
            'content' => $this->faker->realText ,
            'user_ref_id' => $this->faker->randomElement($users) ,
            'commentable_id' => $this->faker->randomElement($tasks) ,
            'commentable_type' => Task::class ,
        ];
    }
}
