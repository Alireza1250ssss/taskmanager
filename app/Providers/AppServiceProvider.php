<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
        if ($this->app->isLocal()){
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // setting macro for github Authorization token and base uri (to be used in getting commit message)
        Http::macro('github' , function ($userName,$repoName,$accessToken){
            return Http::withToken($accessToken)
                ->baseUrl("https://api.github.com/repos/{$userName}/{$repoName}");
        });
    }
}
