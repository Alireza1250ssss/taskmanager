<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Client::query()->create([
            'name' => 'desktop',
            'apiCode' => "QYxOYGXzFXIj3E4vTtz6095puBtqZuZw"
        ]);
        Client::query()->create([
            'name' => 'web',
            'apiCode' => "rtvsM32J6zpvQhsMzMgBrTU7dTKi6u12"
        ]);
        Client::query()->create([
            'name' => 'mobile',
            'apiCode' => "df6cw4mHXNsLm4dSLYKT9LJK5UdZMSab"
        ]);
    }
}
