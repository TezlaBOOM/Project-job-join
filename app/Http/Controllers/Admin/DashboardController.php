<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\JobOffer;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $activeOffersCount = JobOffer::where('status', 'published')
            ->where('deadline_at', '>=', now())
            ->count();

        $newApplicationsCount = Application::where('status', Application::STATUS_NEW)->count();

        $endingSoonOffersCount = JobOffer::where('status', 'published')
            ->where('deadline_at', '>=', now())
            ->where('deadline_at', '<=', now()->addDays(7))
            ->count();

        $totalApplicationsCount = Application::count();

        $recentApplications = Application::with('jobOffer')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $endingSoonOffers = JobOffer::where('status', 'published')
            ->where('deadline_at', '>=', now())
            ->where('deadline_at', '<=', now()->addDays(7))
            ->orderBy('deadline_at', 'asc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'activeOffersCount',
            'newApplicationsCount',
            'endingSoonOffersCount',
            'totalApplicationsCount',
            'recentApplications',
            'endingSoonOffers'
        ));
    }
}
