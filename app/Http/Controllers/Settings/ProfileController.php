<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show()
    {
        $user = auth()->user();
        return view('settings.profile.show', compact('user'));
    }

    public function edit()
    {
        $user = auth()->user();
        return view('settings.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'username' => ['required', 'string', 'max:50', 'unique:users,username,' . $user->id, 'regex:/^[a-zA-Z0-9_]+$/'],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
        ]);

        return redirect()->route('settings.profile.show')
            ->with('success', 'Profilo aggiornato con successo.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        auth()->user()->update([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
        ]);

        return back()->with('success', 'Password aggiornata con successo.');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
            'confirm_deletion' => ['required', 'accepted'],
        ]);

        $user = auth()->user();

        // Prevent deleting the last admin
        if ($user->hasRole('admin') && \App\Models\User::role('admin')->count() <= 1) {
            return back()->with('error', 'Non puoi eliminare l\'ultimo amministratore.');
        }

        auth()->logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Account eliminato con successo.');
    }

    public function sessions()
    {
        $user = auth()->user();
        
        // Get user's login logs
        $sessions = $user->loginLogs()
            ->orderBy('login_at', 'desc')
            ->paginate(20);

        return view('settings.profile.sessions', compact('sessions'));
    }

    public function revokeSession(Request $request, $sessionId)
    {
        // In a real implementation, you would revoke the specific session
        // For now, we'll just logout the current user if they're revoking their own session
        
        if ($sessionId === session()->getId()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect('/')->with('success', 'Sessione terminata.');
        }

        return back()->with('info', 'Revoca sessione non implementata per altre sessioni.');
    }

    public function exportData()
    {
        $user = auth()->user();
        
        $userData = [
            'profile' => $user->only(['name', 'email', 'username', 'created_at']),
            'roles' => $user->roles->pluck('name'),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'login_logs' => $user->loginLogs()->get(['ip_address', 'user_agent', 'login_at', 'logout_at']),
            'created_clients' => $user->createdClients()->count(),
            'created_items' => $user->createdItems()->count(),
            'created_documents' => $user->createdDocuments()->count(),
        ];

        $filename = 'dati_utente_' . $user->username . '_' . now()->format('Y-m-d_H-i-s') . '.json';
        
        return response()->json($userData, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ], JSON_PRETTY_PRINT);
    }
}
