<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailTemplateController extends Controller
{
    public function index(): View
    {
        $templates = EmailTemplate::orderBy('key')->get();

        return view('admin.email_templates.index', compact('templates'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $template = EmailTemplate::findOrFail($id);

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body_text' => ['required', 'string'],
        ]);

        $old = $template->toArray();
        $template->update([
            'subject' => $validated['subject'],
            'body_text' => $validated['body_text'],
            'body_html' => nl2br(e($validated['body_text'])),
        ]);

        AuditLogger::log('email_template_updated', $template, $old, $template->toArray());

        return back()->with('status', "Szablon „{$template->key}” został zaktualizowany.");
    }
}
