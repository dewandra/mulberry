<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use App\Observers\ClientObserver;
use App\Observers\ProjectObserver;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Project::observe(ProjectObserver::class);
        Client::observe(ClientObserver::class);
        User::observe(UserObserver::class);
        Vite::prefetch(concurrency: 3);
    }
}
