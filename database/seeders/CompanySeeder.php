<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Company::factory()->count(10)
            ->has(
                Project::factory()->count(random_int(2,4))
                    ->has(
                        Team::factory()->count(random_int(1,3))
                    )
            )
            ->create();
    }
}
