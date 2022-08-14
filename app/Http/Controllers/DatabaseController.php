<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;

class DatabaseController extends Controller
{
    public function migrateStatus(): string
    {
        Artisan::call("migrate:status",[]);
        return Artisan::output();
    }

    public function migrateFresh(): string
    {
        Artisan::call("migrate:fresh");
        return Artisan::output();
    }

    public function dbSeed(): string
    {
        Artisan::call("db:seed");
        return Artisan::output();
    }
}
