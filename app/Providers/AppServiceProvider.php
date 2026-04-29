<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL; // ✅ AJOUT

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ✅ FORCE HTTPS
        URL::forceScheme('https');

        View::composer('layouts.app', function ($view) {
            $layoutCurrentUser = session('current_user_id')
                ? User::with('club')->find(session('current_user_id'))
                : User::with('club')->orderBy('id')->first();

            $view->with('layoutCurrentUser', $layoutCurrentUser);
        });
    }
}