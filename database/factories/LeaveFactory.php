<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveFactory extends Factory
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
            'user_ref_id' => $this->faker->randomElement($users),
            'time_from' => $this->faker->time('H:i'),
            'time_to' => $this->faker->time('H:i'),
            'status' => $this->faker->randomElement(['refused','pending']) ,
            'type' => $this->faker->randomElement(['remote','off','alternative'])
        ];
    }
}
