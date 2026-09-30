<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\Setting;
use App\Services\AuditLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeExpiredRodoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rodo:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automated purge and anonymization of recruitment data past the retention period according to RODO.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting automated RODO retention purge...');

        $months = (int) Setting::get('retention_months', 3);
        $cutoffDate = now()->subMonths($months);

        $expiredApplications = Application::with(['jobOffer', 'files'])
            ->whereHas('jobOffer', function ($q) use ($cutoffDate) {
                $q->where('deadline_at', '<=', $cutoffDate);
            })
            ->get();

        $purgedCount = 0;

        foreach ($expiredApplications as $application) {
            // Delete stored files
            foreach ($application->files as $file) {
                if (Storage::disk('local')->exists($file->stored_path)) {
                    Storage::disk('local')->delete($file->stored_path);
                }
            }

            $ref = $application->reference_code;
            $appId = $application->id;

            // Delete application (or anonymize)
            $application->forceDelete();
            $purgedCount++;

            AuditLogger::log('rodo_retention_purge', null, null, [
                'purged_application_id' => $appId,
                'reference_code' => $ref,
                'retention_months' => $months,
            ]);
        }

        $this->info("RODO retention purge finished. Removed {$purgedCount} expired candidate applications.");

        return Command::SUCCESS;
    }
}
