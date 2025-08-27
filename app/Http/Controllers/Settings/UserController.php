<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:manage users');
    }

    public function index(Request $request)
    {
        $query = User::with('roles');

        // Search filter
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'ILIKE', '%' . $request->search . '%')
                  ->orWhere('email', 'ILIKE', '%' . $request->search . '%')
                  ->orWhere('username', 'ILIKE', '%' . $request->search . '%');
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereNull('email_verified_at');
            } elseif ($request->status === 'inactive') {
                $query->whereNotNull('email_verified_at');
            }
        }

        $users = $query->orderBy('name')->paginate(20)->withQueryString();
        $roles = Role::orderBy('name')->get();

        return view('settings.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('settings.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'username' => ['required', 'string', 'max:50', 'unique:users', 'regex:/^[a-zA-Z0-9_]+$/'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'email_verified_at' => now(),
        ]);

        $user->assignRole($request->role);

        return redirect()->route('settings.users.index')
            ->with('success', 'Utente creato con successo.');
    }

    public function show(User $user)
    {
        $user->load('roles');
        return view('settings.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        return view('settings.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'username' => ['required', 'string', 'max:50', 'unique:users,username,' . $user->id, 'regex:/^[a-zA-Z0-9_]+$/'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'exists:roles,name'],
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        $user->syncRoles([$request->role]);

        return redirect()->route('settings.users.index')
            ->with('success', 'Utente aggiornato con successo.');
    }

    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Non puoi eliminare il tuo account.');
        }

        // Prevent deleting the last admin
        if ($user->hasRole('admin') && User::role('admin')->count() <= 1) {
            return back()->with('error', 'Non puoi eliminare l\'ultimo amministratore.');
        }

        $user->delete();

        return redirect()->route('settings.users.index')
            ->with('success', 'Utente eliminato con successo.');
    }

    public function toggleStatus(User $user)
    {
        // Prevent disabling yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Non puoi disattivare il tuo account.');
        }

        $user->update([
            'email_verified_at' => $user->email_verified_at ? null : now()
        ]);

        $status = $user->email_verified_at ? 'attivato' : 'disattivato';
        
        return back()->with('success', "Utente {$status} con successo.");
    }

    public function resetPassword(User $user)
    {
        // Generate temporary password
        $tempPassword = 'temp' . rand(1000, 9999);
        
        $user->update([
            'password' => Hash::make($tempPassword),
            'password_changed_at' => null, // Force password change on next login
        ]);

        return back()->with('success', "Password reimpostata. Nuova password temporanea: {$tempPassword}");
    }
}
