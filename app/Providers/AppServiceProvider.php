<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        View::composer('layouts.app', function ($view) {
            $layoutCurrentUser = session('current_user_id')
                ? User::with('club')->find(session('current_user_id'))
                : User::with('club')->orderBy('id')->first();

            $view->with('layoutCurrentUser', $layoutCurrentUser);
        });
    }
}
