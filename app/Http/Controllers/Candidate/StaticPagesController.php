<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\ConsentText;
use App\Models\Setting;
use Illuminate\View\View;

class StaticPagesController extends Controller
{
    public function accessibility(): View
    {
        $officeName = Setting::get('office_name', 'Urząd Miasta');
        $declaration = Setting::get('accessibility_declaration');

        return view('candidate.pages.accessibility', compact('officeName', 'declaration'));
    }

    public function privacy(): View
    {
        $officeName = Setting::get('office_name', 'Urząd Miasta');
        $consent = ConsentText::orderBy('active_from', 'desc')->first();

        return view('candidate.pages.privacy', compact('officeName', 'consent'));
    }
}
