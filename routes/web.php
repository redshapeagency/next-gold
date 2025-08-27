<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SetupController;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('clients', ClientController::class);
    Route::resource('items', ItemController::class);
    Route::resource('documents', DocumentController::class);
    Route::get('/archive', [ArchiveController::class, 'index'])->name('archive.index');
    Route::post('/archive/{item}/restore', [ArchiveController::class, 'restore'])->name('archive.restore');

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/store', [SettingsController::class, 'updateStore'])->name('store.update');
        Route::post('/account', [SettingsController::class, 'updateAccount'])->name('account.update');
        Route::post('/gold-api', [SettingsController::class, 'updateGoldApi'])->name('gold-api.update');
        Route::get('/users', [SettingsController::class, 'users'])->name('users.index');
        Route::post('/users', [SettingsController::class, 'storeUser'])->name('users.store');
        Route::put('/users/{user}', [SettingsController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [SettingsController::class, 'destroyUser'])->name('users.destroy');
        Route::post('/backup/export', [SettingsController::class, 'exportBackup'])->name('backup.export');
        Route::post('/backup/import', [SettingsController::class, 'importBackup'])->name('backup.import');
        Route::get('/logs/login', [SettingsController::class, 'loginLogs'])->name('logs.login');
        Route::get('/logs/actions', [SettingsController::class, 'actionLogs'])->name('logs.actions');
    });
});

Route::get('/setup', [SetupController::class, 'index'])->name('setup');
Route::post('/setup', [SetupController::class, 'store'])->name('setup.store');

require __DIR__.'/auth.php';
