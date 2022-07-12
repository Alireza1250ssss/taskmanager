<?php

namespace Database\Seeders;

use App\Models\Stage;
use Illuminate\Database\Seeder;

class StageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Stage::create(['name'=> 'backlog']);
        Stage::create(['name'=> 'todo']);
        Stage::create(['name'=> 'doing']);
        Stage::create(['name'=> 'review']);
        Stage::create(['name'=> 'done']);
    }
}
