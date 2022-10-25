<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
//            CompanySeeder::class,
//            UserSeeder::class,
//            ScheduleSeeder::class,
//            LeaveSeeder::class,
            StageSeeder::class ,
            StatusSeeder::class ,
            ClientSeeder::class
//            CommentSeeder::class ,
        ]);
    }
}
