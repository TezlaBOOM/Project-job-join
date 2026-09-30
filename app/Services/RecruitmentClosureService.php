<?php

namespace App\Services;

use App\Models\Application;
use App\Models\JobOffer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RecruitmentClosureService
{
    /**
     * Complete the recruitment process for a job offer:
     * - Changes or ensures the job offer status is 'completed'.
     * - Keeps the job offer record and its configuration intact.
     * - Keeps the winning / qualified candidate(s) data, answers, notes, and CV files intact.
     * - Permanently purges personal data and uploaded files (CVs, attachments) of candidates who did not qualify.
     * - Records an audit log entry.
     *
     * @return array{
     *     offer: JobOffer,
     *     previous_status: string,
     *     purged_candidates_count: int,
     *     purged_files_count: int,
     *     qualified_candidates_count: int,
     *     purged_reference_codes: array<int, string>,
     *     qualified_reference_codes: array<int, string>
     * }
     */
    public function completeRecruitment(JobOffer $offer, ?int $performedByUserId = null): array
    {
        $previousStatus = $offer->status;

        // 1. Update offer status to completed if not already completed
        if ($offer->status !== JobOffer::STATUS_COMPLETED) {
            $offer->update(['status' => JobOffer::STATUS_COMPLETED]);
        }

        // 2. Qualified candidates (winners) to keep
        $qualifiedApplications = $offer->applications()
            ->where('status', Application::STATUS_QUALIFIED)
            ->get();

        // 3. Unqualified candidates to purge (including soft-deleted if any)
        $unqualifiedApplications = $offer->applications()
            ->withTrashed()
            ->where('status', '!=', Application::STATUS_QUALIFIED)
            ->with('files')
            ->get();

        $purgedCount = 0;
        $purgedFilesCount = 0;
        $purgedReferenceCodes = [];

        DB::transaction(function () use (
            $unqualifiedApplications,
            &$purgedCount,
            &$purgedFilesCount,
            &$purgedReferenceCodes
        ) {
            foreach ($unqualifiedApplications as $application) {
                // Delete physical files from disk
                foreach ($application->files as $file) {
                    if (Storage::disk('local')->exists($file->stored_path)) {
                        Storage::disk('local')->delete($file->stored_path);
                    }
                    $purgedFilesCount++;
                }

                // Delete candidate folder if exists
                $appDir = 'applications/'.$application->id;
                if (Storage::disk('local')->exists($appDir)) {
                    Storage::disk('local')->deleteDirectory($appDir);
                }

                $purgedReferenceCodes[] = $application->reference_code;

                // Force delete the application row.
                // Database foreign key cascades remove application_files and application_status_history,
                // and set mail_logs.application_id to null.
                $application->forceDelete();
                $purgedCount++;
            }
        });

        // 4. Audit Log
        AuditLogger::log(
            action: 'recruitment_completed_unqualified_purged',
            auditable: $offer,
            oldValues: ['status' => $previousStatus],
            newValues: [
                'status' => JobOffer::STATUS_COMPLETED,
                'purged_candidates_count' => $purgedCount,
                'purged_files_count' => $purgedFilesCount,
                'qualified_candidates_count' => $qualifiedApplications->count(),
                'purged_reference_codes' => $purgedReferenceCodes,
                'qualified_reference_codes' => $qualifiedApplications->pluck('reference_code')->toArray(),
            ],
            userId: $performedByUserId
        );

        return [
            'offer' => $offer,
            'previous_status' => $previousStatus,
            'purged_candidates_count' => $purgedCount,
            'purged_files_count' => $purgedFilesCount,
            'qualified_candidates_count' => $qualifiedApplications->count(),
            'purged_reference_codes' => $purgedReferenceCodes,
            'qualified_reference_codes' => $qualifiedApplications->pluck('reference_code')->toArray(),
        ];
    }
}
