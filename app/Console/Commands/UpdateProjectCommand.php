<?php

namespace App\Console\Commands;

use App\Services\AuditLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;

class UpdateProjectCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update
                            {--no-git : Pomiń pobieranie zmian z repozytorium git}
                            {--branch= : Wskaż inną gałąź Git (domyślnie z .env lub main)}
                            {--repo= : Wskaż alternatywny adres repozytorium GitHub}
                            {--seed : Uruchom seedery po wykonaniu migracji}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatyczna aktualizacja portalu z GitHub: tryb konserwacji, git pull, migracje, assety i cache.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('================================================================');
        $this->info('  🏛️  PORTAL REKRUTACYJNY - AKTUALIZACJA Z GITHUB');
        $this->info('================================================================');

        $baseDir = base_path();
        $repoUrl = $this->option('repo') ?: config('updater.github_repository', env('GITHUB_REPOSITORY', 'https://github.com/TezlaBOOM/Project-job-join.git'));
        $branch = $this->option('branch') ?: config('updater.branch', env('GITHUB_BRANCH', 'main'));
        $token = config('updater.token', env('GITHUB_TOKEN', ''));

        $this->line("Katalog aplikacji: {$baseDir}");
        if (! $this->option('no-git')) {
            $this->line("Źródło aktualizacji: <info>{$repoUrl}</info> (gałąź: <info>{$branch}</info>) [z .env]");
        }

        try {
            // 1. Tryb konserwacji
            $this->comment('Krok 1/6: Włączanie trybu konserwacji...');
            Artisan::call('down', [
                '--refresh' => 15,
                '--secret' => 'rekrutacja-bypass-key',
            ]);
            $this->line('        Aplikacja przeszła w tryb konserwacji.');

            // 2. Git fetch & merge z GitHub
            if (! $this->option('no-git')) {
                $this->comment("Krok 2/6: Pobieranie najnowszych zmian z GitHub ({$branch})...");

                // Budowa bezpiecznego URL dla autoryzacji
                $fetchUrl = $repoUrl;
                if (! empty($token) && str_starts_with($repoUrl, 'https://')) {
                    $fetchUrl = preg_replace('#https://([^@]+@)?#', "https://{$token}@", $repoUrl);
                }

                $envVars = [
                    'GIT_TERMINAL_PROMPT' => '0',
                ];

                // Sprawdzenie katalogu .git
                if (! is_dir($baseDir.'/.git')) {
                    $this->line('        Inicjalizacja lokalnego repozytorium git...');
                    (new Process(['git', 'init'], $baseDir))->run();
                    (new Process(['git', 'remote', 'add', 'origin', $repoUrl], $baseDir))->run();
                }

                // Aktualizacja adresu origin jeśli uległ zmianie
                $getOriginProcess = new Process(['git', 'config', '--get', 'remote.origin.url'], $baseDir);
                $getOriginProcess->run();
                $currentOrigin = trim($getOriginProcess->getOutput());
                if ($currentOrigin !== $repoUrl) {
                    $setOrigin = new Process(['git', 'remote', 'set-url', 'origin', $repoUrl], $baseDir);
                    $setOrigin->run();
                }

                // Zabezpieczenie lokalnych modyfikacji
                $dirtyProcess = new Process(['git', 'status', '--porcelain'], $baseDir);
                $dirtyProcess->run();
                $isDirty = ! empty(trim($dirtyProcess->getOutput()));
                if ($isDirty) {
                    $this->line('        Zapisywanie lokalnych zmian (git stash)...');
                    (new Process(['git', 'stash', 'push', '-m', 'updater_stash'], $baseDir))->run();
                }

                // Pobranie zmian z GitHub
                $fetchProcess = new Process(['git', 'fetch', $fetchUrl, $branch, '--depth=50'], $baseDir, $envVars);
                $fetchProcess->setTimeout(180);
                $fetchProcess->run();

                if (! $fetchProcess->isSuccessful()) {
                    $errorMsg = trim($fetchProcess->getErrorOutput());
                    $this->warn('        Nie udało się pobrać kodu z GitHub: '.$errorMsg);
                    if (empty($token)) {
                        $this->warn('        Wskazówka: Jeśli repozytorium jest prywatne, ustaw GITHUB_TOKEN w pliku .env.');
                    }
                    $this->line('        Kontynuacja procedury aktualizacji na lokalnej kopii plików...');
                } else {
                    $mergeProcess = new Process(['git', 'merge', 'FETCH_HEAD', '--no-edit', '-m', 'Aktualizacja z GitHub'], $baseDir);
                    $mergeProcess->run();
                    $this->line('        Kod źródłowy pomyślnie zaktualizowany z repozytorium.');
                }

                if ($isDirty) {
                    (new Process(['git', 'stash', 'pop'], $baseDir))->run();
                }
            } else {
                $this->comment('Krok 2/6: Pominięto krok pobierania z Git (--no-git).');
            }

            // 3. Migracje bazy danych
            $this->comment('Krok 3/6: Wykonywanie migracji bazy danych...');
            Artisan::call('migrate', ['--force' => true]);
            $this->line('        '.trim(Artisan::output()));

            if ($this->option('seed')) {
                $this->comment('        Wykonywanie seederów bazy danych...');
                Artisan::call('db:seed', ['--force' => true]);
                $this->line('        '.trim(Artisan::output()));
            }

            // 4. Kompilacja assetów frontendowych
            $this->comment('Krok 4/6: Kompilacja zasobów frontendowych (Vite / NPM)...');
            $npmProcess = new Process(['npm', 'run', 'build'], $baseDir);
            $npmProcess->setTimeout(120);
            $npmProcess->run();
            if ($npmProcess->isSuccessful()) {
                $this->line('        Zasoby Vite skompilowane.');
            } else {
                $this->warn('        Ostrzeżenie Vite: '.trim($npmProcess->getErrorOutput()));
            }

            if (file_exists($baseDir.'/resources/css/app.css')) {
                @mkdir($baseDir.'/public/css', 0755, true);
                @copy($baseDir.'/resources/css/app.css', $baseDir.'/public/css/app.css');
            }

            // 5. Czyszczenie i optymalizacja pamięci podręcznej
            $this->comment('Krok 5/6: Optymalizacja pamięci podręcznej...');
            Artisan::call('optimize:clear');
            Artisan::call('storage:link');
            Artisan::call('view:cache');

            if (app()->environment('production')) {
                Artisan::call('config:cache');
                Artisan::call('route:cache');
            }

            Artisan::call('queue:restart');
            $this->line('        Pamięć podręczna odświeżona.');

            // Rejestracja w audycie
            AuditLogger::log('app_updated_successfully', null, null, [
                'user' => get_current_user(),
                'repo' => $repoUrl,
                'branch' => $branch,
            ]);

            $this->info('================================================================');
            $this->info('  ✅  Aktualizacja zakończona sukcesem!');
            $this->info('================================================================');

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Wystąpił błąd podczas aktualizacji: '.$e->getMessage());

            return Command::FAILURE;
        } finally {
            // 6. Wyłączenie trybu konserwacji
            $this->comment('Krok 6/6: Przywracanie działania aplikacji (php artisan up)...');
            Artisan::call('up');
            $this->line('        Aplikacja jest ponownie online.');
        }
    }
}
