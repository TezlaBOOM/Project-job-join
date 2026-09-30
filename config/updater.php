<?php

return [
    /*
    |--------------------------------------------------------------------------
    | GitHub Repository Configuration for Auto-Updater
    |--------------------------------------------------------------------------
    |
    | Konfiguracja repozytorium GitHub wykorzystywanego przez automatyczny
    | updater (skrypt update.sh oraz komendę artisan app:update).
    | Adres repozytorium pobierany jest ze zmiennej GITHUB_REPOSITORY w pliku .env.
    |
    */
    'github_repository' => env('GITHUB_REPOSITORY', env('GITHUB_REPO_URL', 'https://github.com/TezlaBOOM/Project-job-join.git')),

    /*
    | Domyślna gałąź do pobierania aktualizacji (np. main lub production).
    */
    'branch' => env('GITHUB_BRANCH', 'main'),

    /*
    | Opcjonalny GitHub Personal Access Token (PAT) dla prywatnych repozytoriów.
    */
    'token' => env('GITHUB_TOKEN', null),
];
