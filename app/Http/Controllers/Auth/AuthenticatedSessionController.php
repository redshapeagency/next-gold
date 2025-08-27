<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        // Se non ci sono utenti, redirect al setup
        if (User::count() === 0) {
            return redirect()->route('setup.index');
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->getCredentials();
        $user = null;

        // Tenta il login
        $success = Auth::attempt($credentials, $request->boolean('remember'));

        if ($success) {
            $user = Auth::user();
            $request->session()->regenerate();
        }

        // Log del tentativo
        LoginLog::create([
            'user_id' => $user?->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'success' => $success,
            'attempted_email' => $request->input('login_type') === 'email' ? $request->input('login') : null,
            'attempted_username' => $request->input('login_type') === 'username' ? $request->input('login') : null,
        ]);

        if (!$success) {
            return back()->withErrors([
                'login' => 'Le credenziali fornite non sono corrette.',
            ])->onlyInput('login');
        }

        return redirect()->intended(route('dashboard.index'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
