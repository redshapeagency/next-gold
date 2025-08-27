<?php

namespace App\Http\Controllers;

use App\Models\ActionLog;
use App\Models\GoldQuote;
use App\Models\LoginLog;
use App\Models\StoreSetting;
use App\Models\User;
use App\Services\BackupService;
use App\Services\GoldPriceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = StoreSetting::getInstance();
        $user = auth()->user();

        return view('settings.index', compact('settings', 'user'));
    }

    public function updateStore(Request $request)
    {
        $request->validate([
            'business_name' => 'nullable|string|max:255',
            'vat_number' => 'nullable|string|max:20',
            'tax_code' => 'nullable|string|max:16',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $settings = StoreSetting::getInstance();
        $data = $request->only([
            'business_name', 'vat_number', 'tax_code', 'address',
            'city', 'zip', 'country', 'phone', 'email'
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $settings->update($data);

        return redirect()->route('settings.index')
            ->with('success', 'Impostazioni negozio aggiornate con successo.');
    }

    public function updateAccount(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . auth()->id(),
            'email' => 'required|email|max:255|unique:users,email,' . auth()->id(),
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = auth()->user();
        $data = $request->only(['first_name', 'last_name', 'username', 'email']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('settings.index')
            ->with('success', 'Profilo aggiornato con successo.');
    }

    public function updateGoldApi(Request $request)
    {
        $request->validate([
            'provider' => 'required|string|max:255',
            'api_url' => 'nullable|url',
            'api_key' => 'nullable|string|max:255',
            'unit' => 'required|in:g,oz',
            'currency' => 'required|string|max:3',
            'polling_interval' => 'nullable|integer|min:10|max:3600',
        ]);

        $settings = StoreSetting::getInstance();
        $counters = $settings->doc_number_counters ?? [];

        $settings->update([
            'doc_number_counters' => $counters,
        ]);

        // Test connection if URL and key provided
        if ($request->api_url && $request->api_key) {
            try {
                $goldService = new GoldPriceService();
                $quote = $goldService->fetchPrice();
                $message = 'Connessione testata con successo. Prezzo attuale: ' . $quote['bid'] . ' ' . $quote['ask'];
            } catch (\Exception $e) {
                $message = 'Errore nella connessione: ' . $e->getMessage();
            }
        } else {
            $message = 'Impostazioni salvate. La connessione verrà testata al prossimo fetch.';
        }

        return redirect()->route('settings.index')
            ->with('success', $message);
    }

    public function users()
    {
        $users = User::with('roles')->paginate(15);

        return view('settings.users', compact('users'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->role);

        return redirect()->route('settings.users')
            ->with('success', 'Utente creato con successo.');
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|exists:roles,name',
        ]);

        $user->update($request->only(['first_name', 'last_name', 'username', 'email']));
        $user->syncRoles([$request->role]);

        return redirect()->route('settings.users')
            ->with('success', 'Utente aggiornato con successo.');
    }

    public function destroyUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('settings.users')
                ->with('error', 'Non puoi eliminare il tuo stesso account.');
        }

        $user->delete();

        return redirect()->route('settings.users')
            ->with('success', 'Utente eliminato con successo.');
    }

    public function exportBackup()
    {
        $backupService = new BackupService();
        $backup = $backupService->export();

        return response()->json($backup)
            ->header('Content-Disposition', 'attachment; filename="backup-' . date('Y-m-d-H-i-s') . '.json"');
    }

    public function importBackup(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:json',
            'mode' => 'required|in:append,replace',
        ]);

        $backupService = new BackupService();
        $result = $backupService->import($request->file('backup_file'), $request->mode);

        return redirect()->route('settings.index')
            ->with('success', 'Backup importato con successo. ' . $result['message']);
    }

    public function loginLogs(Request $request)
    {
        $query = LoginLog::with('user');

        if ($request->has('user_id') && !empty($request->user_id)) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('outcome') && !empty($request->outcome)) {
            $query->where('outcome', $request->outcome);
        }

        $logs = $query->latest()->paginate(50);
        $users = User::all();

        return view('settings.login-logs', compact('logs', 'users'));
    }

    public function actionLogs(Request $request)
    {
        $query = ActionLog::with('user');

        if ($request->has('user_id') && !empty($request->user_id)) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('action') && !empty($request->action)) {
            $query->where('action', $request->action);
        }

        $logs = $query->latest()->paginate(50);
        $users = User::all();

        return view('settings.action-logs', compact('logs', 'users'));
    }
}
