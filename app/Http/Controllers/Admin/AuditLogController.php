<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::with('user');

        // If recruiter (not admin), view only own operations
        if (! auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('action')) {
            $query->where('action', 'like', '%'.trim($request->input('action')).'%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(30)->withQueryString();
        $users = User::orderBy('name')->get();

        return view('admin.logs.index', compact('logs', 'users'));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $query = AuditLog::with('user');

        if (! auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('action')) {
            $query->where('action', 'like', '%'.trim($request->input('action')).'%');
        }

        $logs = $query->orderBy('created_at', 'desc')->limit(5000)->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="audit_logs_'.date('Y-m-d_His').'.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($logs) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($output, ['ID', 'Data i czas', 'Użytkownik', 'Akcja', 'Typ obiektu', 'ID obiektu', 'Adres IP', 'Przeglądarka'], ';');

            foreach ($logs as $log) {
                fputcsv($output, [
                    $log->id,
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user ? $log->user->name : 'System / Gość',
                    $log->action,
                    $log->auditable_type ?: '—',
                    $log->auditable_id ?: '—',
                    $log->ip,
                    $log->user_agent,
                ], ';');
            }

            fclose($output);
        }, 200, $headers);
    }
}
