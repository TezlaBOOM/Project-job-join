<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Protocol;
use App\Models\User;

class ProtocolGeneratorService
{
    /**
     * Generate or update the official recruitment protocol for an announcement.
     */
    public function generate(Announcement $announcement, User $generatedBy): Protocol
    {
        $announcement->load(['candidateApplications.evaluationsFormal', 'candidateApplications.evaluationsMerit', 'tenant']);

        $totalApplications = $announcement->candidateApplications->count();
        $formalPassed = $announcement->candidateApplications->filter(fn ($app) => $app->status !== 'rejected' && ! $app->is_withdrawn)->count();
        $selectedCandidates = $announcement->candidateApplications->filter(fn ($app) => $app->status === 'selected');

        $selectedText = $selectedCandidates->isEmpty()
            ? 'W wyniku przeprowadzonego naboru nie wyłoniono kandydata na stanowisko.'
            : 'Wyłoniony kandydat: '.$selectedCandidates->map(fn ($c) => "{$c->first_name} {$c->last_name}")->implode(', ').'.';

        $summary = "PROTOKÓŁ Z PRZEPROWADZONEGO NABORU NA STANOWISKO: {$announcement->title}\n".
                   "Jednostka JST: {$announcement->tenant->name}\n".
                   "Kod stanowiska: {$announcement->position_code}\n".
                   "Liczba nadesłanych ofert: {$totalApplications}\n".
                   "Liczba ofert spełniających wymogi formalne: {$formalPassed}\n".
                   "Wynik naboru: {$selectedText}\n".
                   'Sporządzono dnia: '.now()->format('d.m.Y H:i');

        return Protocol::updateOrCreate(
            ['announcement_id' => $announcement->id],
            [
                'generated_by' => $generatedBy->id,
                'summary' => $summary,
                'is_published' => true,
            ]
        );
    }
}
