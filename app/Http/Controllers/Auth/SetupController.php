<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class SetupController extends Controller
{
    public function index()
    {
        // Se esistono già utenti, redirect alla dashboard
        if (User::count() > 0) {
            return redirect()->route('dashboard.index')
                ->with('error', 'Il setup è già stato completato.');
        }

        return view('auth.setup');
    }

    public function store(Request $request)
    {
        // Verifica che non esistano già utenti
        if (User::count() > 0) {
            return redirect()->route('dashboard.index')
                ->with('error', 'Il setup è già stato completato.');
        }

        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Crea l'utente amministratore
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assegna il ruolo admin
        $adminRole = Role::findByName('admin');
        $user->assignRole($adminRole);

        // Login automatico
        auth()->login($user);

        return redirect()->route('dashboard.index')
            ->with('success', 'Setup completato con successo! Benvenuto in Next Gold.');
    }
}
