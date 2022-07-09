<?php

namespace Database\Seeders;

use App\Models\Permissions\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;

class PermissionUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = Permission::all();
        $users = User::all();
        $users->each(function ($user , $key) use ($permissions){
           $user->permissions()->sync($permissions->random(random_int(0,5)));
        });
    }
}
