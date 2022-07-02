<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleFactory extends Factory
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
            'time_from' => $this->faker->time('H:i'),
            'time_to' => $this->faker->time('H:i'),
            'day' => $this->faker->randomElement(['saturday','sunday','monday','tuesday','wednesday','thursday','friday']),
            'type' => $this->faker->randomElement(['remote','face_to_face']),
            'user_ref_id' => $this->faker->randomElement($users),
        ];
    }
}
