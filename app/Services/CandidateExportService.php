<?php

namespace App\Services;

use App\Models\Announcement;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CandidateExportService
{
    /**
     * Export candidates of an announcement as a CSV streamed response.
     */
    public function exportCsv(Announcement $announcement): StreamedResponse
    {
        $announcement->load(['candidateApplications.evaluationsFormal', 'candidateApplications.evaluationsMerit']);

        $filename = "kandydaci_nabor_{$announcement->id}_".date('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($announcement) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fwrite($file, "\xEF\xBB\xBF");

            // CSV Header
            fputcsv($file, ['ID', 'Imie', 'Nazwisko', 'Email', 'Telefon', 'Status', 'Ocena Formalna', 'Punkty Merytoryczne', 'Data Zgloszenia']);

            foreach ($announcement->candidateApplications as $app) {
                $formalResult = $app->evaluationsFormal->first()?->result ?? 'Brak';
                $meritScore = $app->evaluationsMerit->first()?->score ?? 'Brak';

                fputcsv($file, [
                    $app->id,
                    $app->first_name,
                    $app->last_name,
                    $app->email,
                    $app->phone ?? '',
                    $app->status,
                    $formalResult,
                    $meritScore,
                    $app->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
