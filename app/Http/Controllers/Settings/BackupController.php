<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    protected $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->middleware('auth');
        $this->middleware('permission:manage backups');
        $this->backupService = $backupService;
    }

    public function index()
    {
        $backups = collect();
        
        if (Storage::disk('backups')->exists('/')) {
            $files = Storage::disk('backups')->files('/');
            
            $backups = collect($files)
                ->filter(fn($file) => str_ends_with($file, '.zip'))
                ->map(function ($file) {
                    $size = Storage::disk('backups')->size($file);
                    $lastModified = Storage::disk('backups')->lastModified($file);
                    
                    return [
                        'name' => basename($file),
                        'path' => $file,
                        'size' => $this->formatBytes($size),
                        'size_bytes' => $size,
                        'date' => date('d/m/Y H:i', $lastModified),
                        'timestamp' => $lastModified,
                    ];
                })
                ->sortByDesc('timestamp')
                ->values();
        }

        $totalSize = $backups->sum('size_bytes');
        $stats = [
            'total_backups' => $backups->count(),
            'total_size' => $this->formatBytes($totalSize),
            'latest_backup' => $backups->first()['date'] ?? 'Nessun backup',
        ];

        return view('settings.backup.index', compact('backups', 'stats'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'type' => 'required|in:full,database,files',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $backupPath = $this->backupService->createBackup($request->type, $request->description);
            
            return back()->with('success', 'Backup creato con successo: ' . basename($backupPath));
        } catch (\Exception $e) {
            return back()->with('error', 'Errore durante la creazione del backup: ' . $e->getMessage());
        }
    }

    public function download($filename)
    {
        $path = $filename;
        
        if (!Storage::disk('backups')->exists($path)) {
            return back()->with('error', 'File di backup non trovato.');
        }

        return Storage::disk('backups')->download($path);
    }

    public function destroy($filename)
    {
        $path = $filename;
        
        if (!Storage::disk('backups')->exists($path)) {
            return back()->with('error', 'File di backup non trovato.');
        }

        Storage::disk('backups')->delete($path);
        
        return back()->with('success', 'Backup eliminato con successo.');
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:zip|max:1048576', // 1GB max
            'confirm_restore' => 'required|accepted',
        ]);

        try {
            // Store uploaded file temporarily
            $uploadedFile = $request->file('backup_file');
            $tempPath = $uploadedFile->storeAs('temp', 'restore_' . time() . '.zip', 'local');
            
            // Verify backup integrity
            if (!$this->backupService->verifyBackup(storage_path('app/' . $tempPath))) {
                Storage::disk('local')->delete($tempPath);
                return back()->with('error', 'File di backup non valido o corrotto.');
            }

            // Restore backup
            $this->backupService->restoreBackup(storage_path('app/' . $tempPath));
            
            // Clean up
            Storage::disk('local')->delete($tempPath);
            
            return back()->with('success', 'Backup ripristinato con successo. Riavvia l\'applicazione se necessario.');
        } catch (\Exception $e) {
            return back()->with('error', 'Errore durante il ripristino: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $request->validate([
            'tables' => 'nullable|array',
            'tables.*' => 'string',
            'format' => 'required|in:sql,csv,json',
        ]);

        try {
            $exportPath = $this->backupService->exportData(
                $request->tables ?: [],
                $request->format
            );
            
            return response()->download($exportPath)->deleteFileAfterSend();
        } catch (\Exception $e) {
            return back()->with('error', 'Errore durante l\'esportazione: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:sql,csv,json|max:102400', // 100MB max
            'import_type' => 'required|in:replace,append',
            'confirm_import' => 'required|accepted',
        ]);

        try {
            $uploadedFile = $request->file('import_file');
            $tempPath = $uploadedFile->storeAs('temp', 'import_' . time() . '.' . $uploadedFile->getClientOriginalExtension(), 'local');
            
            $this->backupService->importData(
                storage_path('app/' . $tempPath),
                $uploadedFile->getClientOriginalExtension(),
                $request->import_type === 'replace'
            );
            
            // Clean up
            Storage::disk('local')->delete($tempPath);
            
            return back()->with('success', 'Dati importati con successo.');
        } catch (\Exception $e) {
            return back()->with('error', 'Errore durante l\'importazione: ' . $e->getMessage());
        }
    }

    public function schedule(Request $request)
    {
        $request->validate([
            'enabled' => 'boolean',
            'frequency' => 'required_if:enabled,true|in:daily,weekly,monthly',
            'time' => 'required_if:enabled,true|date_format:H:i',
            'retention_days' => 'required_if:enabled,true|integer|min:1|max:365',
            'backup_type' => 'required_if:enabled,true|in:full,database,files',
        ]);

        // Update store settings
        $settings = \App\Models\StoreSettings::first();
        $settings->update([
            'backup_enabled' => $request->boolean('enabled'),
            'backup_frequency' => $request->frequency,
            'backup_time' => $request->time,
            'backup_retention_days' => $request->retention_days,
            'backup_type' => $request->backup_type,
        ]);

        return back()->with('success', 'Impostazioni backup programmate aggiornate.');
    }

    public function test()
    {
        try {
            // Create a test backup
            $backupPath = $this->backupService->createBackup('database', 'Test backup');
            
            // Verify the backup
            $isValid = $this->backupService->verifyBackup($backupPath);
            
            if ($isValid) {
                // Clean up test backup
                unlink($backupPath);
                return back()->with('success', 'Test backup completato con successo.');
            } else {
                return back()->with('error', 'Test backup fallito: backup non valido.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Test backup fallito: ' . $e->getMessage());
        }
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
