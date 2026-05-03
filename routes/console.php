<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Command\Command;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('demo:reset {--password= : Mot de passe DEMO_RESET_PASSWORD}', function () {
    if (app()->environment('production')) {
        $this->error('Reset démo interdit en production.');

        return Command::FAILURE;
    }

    $expectedPassword = config('demo.reset_password');

    if (blank($expectedPassword)) {
        $this->error('DEMO_RESET_PASSWORD doit être renseigné dans l’environnement.');

        return Command::FAILURE;
    }

    $providedPassword = $this->option('password') ?: $this->secret('Mot de passe reset démo');

    if (! hash_equals((string) $expectedPassword, (string) $providedPassword)) {
        $this->error('Mot de passe reset démo incorrect.');

        return Command::FAILURE;
    }

    if (! $this->confirm('Cette action supprime toutes les données et recharge le seed. Continuer ?')) {
        $this->warn('Reset démo annulé.');

        return Command::SUCCESS;
    }

    $this->call('migrate:fresh', [
        '--seed' => true,
        '--force' => true,
    ]);

    $this->info('Démo réinitialisée.');

    return Command::SUCCESS;
})->purpose('Réinitialiser la base de démonstration avec protection par mot de passe');
