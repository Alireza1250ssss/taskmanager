<?php

namespace Database\Factories;

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
        return [
            'title' => $this->faker->word(),
            'description' => $this->faker->text(),
            'user_ref_id'=> $this->faker->randomElement($users)
        ];
    }
}
