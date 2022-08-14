<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name ,
            'sprint_period' => $this->faker->randomNumber() ,
            'sprint_start_date' => $this->faker->date() ,
        ];
    }
}
