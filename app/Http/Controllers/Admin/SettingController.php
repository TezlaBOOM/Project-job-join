<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConsentText;
use App\Models\Setting;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = [
            'office_name' => Setting::get('office_name', 'Urząd Miasta'),
            'office_address' => Setting::get('office_address', 'ul. Urzędowa 1, 00-001 Miasto'),
            'office_email' => Setting::get('office_email', 'kadry@miasto.gov.pl'),
            'office_phone' => Setting::get('office_phone', '12 345 67 89'),
            'bip_url' => Setting::get('bip_url', 'https://bip.miasto.gov.pl'),
            'home_intro' => Setting::get('home_intro', 'Realizuj projekty ważne dla mieszkańców naszego miasta...'),
            'retention_months' => Setting::get('retention_months', '3'),
            'file_max_mb' => Setting::get('file_max_mb', '5'),
            'accessibility_declaration' => Setting::get('accessibility_declaration', ''),
            'admin_2fa_enabled' => Setting::get('admin_2fa_enabled', '0') === '1',
        ];

        $latestConsent = ConsentText::orderBy('active_from', 'desc')->first();

        return view('admin.settings.index', compact('settings', 'latestConsent'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'office_name' => ['required', 'string', 'max:150'],
            'office_address' => ['required', 'string', 'max:255'],
            'office_email' => ['required', 'email', 'max:150'],
            'office_phone' => ['required', 'string', 'max:50'],
            'bip_url' => ['nullable', 'url', 'max:255'],
            'home_intro' => ['required', 'string'],
            'retention_months' => ['required', 'integer', 'min:1', 'max:60'],
            'file_max_mb' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        foreach ($validated as $k => $v) {
            Setting::set($k, (string) $v);
        }

        Setting::set('admin_2fa_enabled', $request->boolean('admin_2fa_enabled') ? '1' : '0');

        // Handle new RODO version if updated
        if ($request->filled('rodo_content') && $request->filled('rodo_version')) {
            $latest = ConsentText::orderBy('active_from', 'desc')->first();
            if (! $latest || $latest->content !== $request->input('rodo_content') || $latest->version !== $request->input('rodo_version')) {
                ConsentText::create([
                    'version' => $request->input('rodo_version'),
                    'content' => $request->input('rodo_content'),
                    'active_from' => now(),
                ]);
            }
        }

        AuditLogger::log('settings_updated', null, null, $validated);

        return back()->with('status', 'Ustawienia portalu rekrutacyjnego zostały zaktualizowane.');
    }
}
