<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        View::composer('layouts.app', function ($view) {
            $layoutCurrentUser = session('current_user_id')
                ? User::with('club')->find(session('current_user_id'))
                : User::with('club')->orderBy('id')->first();

            $view->with('layoutCurrentUser', $layoutCurrentUser);
        });
    }
}
