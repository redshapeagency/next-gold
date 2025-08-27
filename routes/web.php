<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\SetupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\Settings\SettingsController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\GoldPriceController;
use App\Http\Controllers\Settings\BackupController;
use App\Http\Controllers\Settings\UserController;
use Illuminate\Support\Facades\Route;
use App\Models\User;

Route::get('/', function () {
    if (User::count() === 0) {
        return redirect()->route('setup.index');
    }
    return redirect()->route('login');
});

// Setup routes (only when no users exist)
Route::middleware(['guest'])->group(function () {
    Route::get('/setup', [SetupController::class, 'index'])->name('setup.index');
    Route::post('/setup', [SetupController::class, 'store'])->name('setup.store');
});

// Authentication routes
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Clients
    Route::resource('clients', ClientController::class);

    // Items/Inventory
    Route::resource('items', ItemController::class);
    Route::post('/items/{item}/restore', [ItemController::class, 'restore'])->name('items.restore');

    // Documents
    Route::resource('documents', DocumentController::class);
    Route::get('/documents/{document}/pdf', [DocumentController::class, 'downloadPdf'])->name('documents.pdf');

    // Archive
    Route::get('/archive', [ArchiveController::class, 'index'])->name('archive.index');

    // Settings (admin only)
    Route::middleware(['can:access settings'])->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        
        // Store settings
        Route::get('/store', [SettingsController::class, 'store'])->name('store');
        Route::put('/store', [SettingsController::class, 'updateStore'])->name('store.update');
        
        // Profile
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
        
        // Gold Price API
        Route::get('/gold-price', [GoldPriceController::class, 'index'])->name('gold-price');
        Route::put('/gold-price', [GoldPriceController::class, 'update'])->name('gold-price.update');
        Route::post('/gold-price/test', [GoldPriceController::class, 'test'])->name('gold-price.test');
        
        // Users management
        Route::resource('users', UserController::class)->except(['show']);
        
        // Backup & Export/Import
        Route::get('/backup', [BackupController::class, 'index'])->name('backup');
        Route::post('/backup/export', [BackupController::class, 'export'])->name('backup.export');
        Route::post('/backup/import', [BackupController::class, 'import'])->name('backup.import');
        Route::post('/backup/preview', [BackupController::class, 'preview'])->name('backup.preview');
        
        // System info
        Route::get('/system', [SettingsController::class, 'system'])->name('system');
        
        // Logs
        Route::get('/logs/login', [SettingsController::class, 'loginLogs'])->name('logs.login');
        Route::get('/logs/actions', [SettingsController::class, 'actionLogs'])->name('logs.actions');
    });
});
