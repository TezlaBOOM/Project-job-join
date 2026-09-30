<?php

namespace App\Console\Commands;

use App\Models\JobOffer;
use App\Services\RecruitmentClosureService;
use Illuminate\Console\Command;

class CompleteRecruitmentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recruitment:complete 
                            {offer? : ID lub public_id naboru do zakonczenia}
                            {--all-completed : Oczysc niezakwalifikowanych kandydatow we wszystkich zakonczonych naborach}
                            {--force : Wykonaj operacje bez pytania o potwierdzenie}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Konczy proces rekrutacji i trwale usuwa dane osobowe oraz pliki CV niezakwalifikowanych kandydatow (pozostawia oferte i dane wygranego kandydata).';

    /**
     * Execute the console command.
     */
    public function handle(RecruitmentClosureService $service): int
    {
        $offerIdentifier = $this->argument('offer');
        $allCompleted = $this->option('all-completed');
        $force = $this->option('force');

        if (! $offerIdentifier && ! $allCompleted) {
            $this->error('Musisz podać identyfikator naboru (ID / public_id) lub użyć opcji --all-completed.');

            return Command::INVALID;
        }

        if ($allCompleted) {
            $offers = JobOffer::where('status', JobOffer::STATUS_COMPLETED)->get();

            if ($offers->isEmpty()) {
                $this->info('Brak naborów ze statusem zakończonym.');

                return Command::SUCCESS;
            }

            if (! $force && ! $this->confirm("Czy na pewno chcesz usunąć dane i pliki CV niezakwalifikowanych kandydatów dla {$offers->count()} zakończonych naborów?")) {
                $this->info('Operacja anulowana.');

                return Command::SUCCESS;
            }

            $totalPurgedCandidates = 0;
            $totalPurgedFiles = 0;

            foreach ($offers as $offer) {
                $result = $service->completeRecruitment($offer);
                $totalPurgedCandidates += $result['purged_candidates_count'];
                $totalPurgedFiles += $result['purged_files_count'];
                $this->line("Nabór: {$offer->title} -> Usunięto kandydatów: {$result['purged_candidates_count']}, pliki: {$result['purged_files_count']}. Zachowano zakwalifikowanych: {$result['qualified_candidates_count']}.");
            }

            $this->info("Zakończono. Łącznie usunięto {$totalPurgedCandidates} niezakwalifikowanych kandydatów oraz {$totalPurgedFiles} plików CV.");

            return Command::SUCCESS;
        }

        $offer = is_numeric($offerIdentifier)
            ? JobOffer::find($offerIdentifier)
            : JobOffer::where('public_id', $offerIdentifier)->first();

        if (! $offer) {
            $this->error("Nie znaleziono naboru dla identyfikatora: {$offerIdentifier}");

            return Command::FAILURE;
        }

        $qualifiedCount = $offer->qualifiedApplications()->count();
        $unqualifiedCount = $offer->unqualifiedApplications()->count();

        $this->info("Nabór: {$offer->title} (ID: {$offer->id}, Public ID: {$offer->public_id})");
        $this->line("- Zakwalifikowani kandydaci (wygrani - zostaną ZACHOWANI): {$qualifiedCount}");
        $this->line("- Niezakwalifikowani kandydaci (dane osobowe i CV zostaną TRWALE USUNIĘTE): {$unqualifiedCount}");
        $this->line('- Oferta pracy: ZOSTANIE ZACHOWANA (status: completed)');

        if ($qualifiedCount === 0) {
            $this->warn('UWAGA: Żaden kandydat nie posiada statusu zakwalifikowany (qualified)! Wszystkie zgłoszenia zostaną usunięte.');
        }

        if (! $force && ! $this->confirm('Czy chcesz kontynuować i zamknąć proces rekrutacji?')) {
            $this->info('Operacja anulowana.');

            return Command::SUCCESS;
        }

        $result = $service->completeRecruitment($offer);

        $this->info("Proces rekrutacji dla „{$offer->title}” został pomyślnie zakończony.");
        $this->line("Usunięto {$result['purged_candidates_count']} niezakwalifikowanych kandydatów i {$result['purged_files_count']} plików CV.");
        $this->line("Zachowano {$result['qualified_candidates_count']} zakwalifikowanych kandydatów.");

        return Command::SUCCESS;
    }
}
