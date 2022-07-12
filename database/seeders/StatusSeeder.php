<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Status::create(['name' => 'accepted']);
        Status::create(['name' => 'rejected']);
        Status::create(['name' => 'stopped']);
        Status::create(['name' => 'continued']);
        Status::create(['name' => 'waiting']);
    }
}
