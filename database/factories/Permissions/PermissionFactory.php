<?php

namespace Database\Factories\Permissions;

use App\Models\Permissions\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

class PermissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $randomType = $this->faker->randomElement(['route','inputField','outputField','action']);
        $permissions = Permission::query()->whereNotIn('type',['inputField','outputField'])->get()->pluck('permission_id')->toArray();
        if ( ($randomType == 'inputField' || $randomType=='outputFiled') && !empty($permissions)) {
            $array = $this->faker->randomElements($permissions);
            $parentId = reset($array);
        }
        else
            $parentId = null;

        return [
            'key' => $this->faker->unique()->word ,
            'level' => $this->faker->word ,
            'type' =>  $randomType,
            'parent_id' => $parentId
        ];
    }
}
