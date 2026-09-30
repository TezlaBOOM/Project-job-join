<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\RecruiterInviteMail;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::orderBy('name')->get();

        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:admin,recruiter,viewer'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $rawPassword = $validated['password'] ?: Str::random(16);

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower(trim($validated['email'])),
            'role' => $validated['role'],
            'password' => Hash::make($rawPassword),
            'is_active' => true,
        ]);

        AuditLogger::log('user_created', $user, null, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        // Generate invitation reset token link
        $token = Str::random(60);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => hash('sha256', $token), 'created_at' => now()]
        );

        $resetUrl = url('/admin/login?invite='.$token);

        try {
            Mail::to($user->email)->send(new RecruiterInviteMail($user, $resetUrl));
        } catch (\Throwable $e) {
            // Log mail failure gracefully
        }

        return back()->with('status', "Użytkownik {$user->name} został dodany i wysłano zaproszenie e-mail.");
    }

    public function updateRole(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $currentUser = auth()->user();

        $request->validate([
            'role' => ['required', 'in:admin,recruiter,viewer'],
        ]);

        $newRole = $request->input('role');

        // Cannot degrade yourself
        if ($user->id === $currentUser->id && $newRole !== 'admin') {
            return back()->withErrors(['error' => 'Nie możesz odebrać samemu sobie uprawnień Administratora.']);
        }

        // Check if last admin
        if ($user->role === 'admin' && $newRole !== 'admin') {
            $otherAdmins = User::where('role', 'admin')->where('is_active', true)->where('id', '!=', $user->id)->count();
            if ($otherAdmins === 0) {
                return back()->withErrors(['error' => 'Nie można zdegradować ostatniego aktywnego Administratora.']);
            }
        }

        $old = $user->toArray();
        $user->role = $newRole;
        $user->save();

        AuditLogger::log('user_role_changed', $user, ['role' => $old['role']], ['role' => $newRole]);

        return back()->with('status', "Rola użytkownika {$user->name} została zmieniona na: {$newRole}.");
    }

    public function toggleActive(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $currentUser = auth()->user();

        if ($user->id === $currentUser->id) {
            return back()->withErrors(['error' => 'Nie możesz dezaktywować własnego konta.']);
        }

        if ($user->isAdmin() && $user->is_active) {
            $otherActiveAdmins = User::where('role', 'admin')->where('is_active', true)->where('id', '!=', $user->id)->count();
            if ($otherActiveAdmins === 0) {
                return back()->withErrors(['error' => 'Nie można dezaktywować ostatniego aktywnego Administratora.']);
            }
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        AuditLogger::log('user_status_toggled', $user, null, ['is_active' => $user->is_active]);

        return back()->with('status', "Status konta {$user->name} został zmieniony.");
    }

    public function resetPassword(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $request->validate([
            'new_password' => ['required', 'string', 'min:8'],
        ]);

        $user->password = Hash::make($request->input('new_password'));
        $user->password_changed_at = now();
        $user->save();

        AuditLogger::log('user_password_reset', $user);

        return back()->with('status', "Hasło użytkownika {$user->name} zostało zresetowane.");
    }
}
