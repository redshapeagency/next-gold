<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Category;
use App\Models\Item;
use App\Models\Document;
use App\Models\DocumentItem;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BackupService
{
    protected $hmacSecret;

    public function __construct()
    {
        $this->hmacSecret = config('app.backup_hmac_secret') ?? env('BACKUP_HMAC_SECRET');
        
        if (!$this->hmacSecret) {
            throw new \Exception('BACKUP_HMAC_SECRET non configurato');
        }
    }

    public function export(): array
    {
        $data = [
            'version' => '1.0',
            'exported_at' => now()->toISOString(),
            'app_version' => config('app.version', '1.0.0'),
            'data' => [
                'store_settings' => StoreSetting::all()->toArray(),
                'categories' => Category::all()->toArray(),
                'clients' => Client::withTrashed()->with(['creator', 'updater'])->get()->toArray(),
                'items' => Item::withTrashed()->with(['category', 'creator', 'updater'])->get()->toArray(),
                'documents' => Document::with(['client', 'creator', 'updater'])->get()->toArray(),
                'document_items' => DocumentItem::with(['document', 'item'])->get()->toArray(),
            ],
        ];

        // Genera la firma HMAC
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE);
        $signature = hash_hmac('sha256', $payload, $this->hmacSecret);

        return [
            'signature' => $signature,
            'payload' => $data,
        ];
    }

    public function import(array $backupData, array $options = []): array
    {
        // Verifica la firma
        if (!$this->verifySignature($backupData)) {
            throw new \Exception('Firma del backup non valida');
        }

        $data = $backupData['payload']['data'];
        $results = [];

        try {
            \DB::beginTransaction();

            // Import store settings
            if (!empty($data['store_settings'])) {
                $results['store_settings'] = $this->importStoreSettings($data['store_settings'], $options);
            }

            // Import categories
            if (!empty($data['categories'])) {
                $results['categories'] = $this->importCategories($data['categories'], $options);
            }

            // Import clients
            if (!empty($data['clients'])) {
                $results['clients'] = $this->importClients($data['clients'], $options);
            }

            // Import items
            if (!empty($data['items'])) {
                $results['items'] = $this->importItems($data['items'], $options);
            }

            // Import documents
            if (!empty($data['documents'])) {
                $results['documents'] = $this->importDocuments($data['documents'], $options);
            }

            // Import document items
            if (!empty($data['document_items'])) {
                $results['document_items'] = $this->importDocumentItems($data['document_items'], $options);
            }

            \DB::commit();

            Log::info('Backup import completed successfully', $results);

            return $results;
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Backup import failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    protected function verifySignature(array $backupData): bool
    {
        if (!isset($backupData['signature'], $backupData['payload'])) {
            return false;
        }

        $payload = json_encode($backupData['payload'], JSON_UNESCAPED_UNICODE);
        $expectedSignature = hash_hmac('sha256', $payload, $this->hmacSecret);

        return hash_equals($expectedSignature, $backupData['signature']);
    }

    protected function importStoreSettings(array $data, array $options): array
    {
        $imported = 0;
        $updated = 0;

        foreach ($data as $item) {
            $existing = StoreSetting::find($item['id']);

            if ($existing) {
                if ($options['replace'] ?? false) {
                    $existing->update($item);
                    $updated++;
                }
            } else {
                StoreSetting::create($item);
                $imported++;
            }
        }

        return compact('imported', 'updated');
    }

    protected function importCategories(array $data, array $options): array
    {
        $imported = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($data as $item) {
            $existing = Category::where('slug', $item['slug'])->first();

            if ($existing) {
                if ($options['replace'] ?? false) {
                    $existing->update($item);
                    $updated++;
                } else {
                    $skipped++;
                }
            } else {
                Category::create($item);
                $imported++;
            }
        }

        return compact('imported', 'updated', 'skipped');
    }

    protected function importClients(array $data, array $options): array
    {
        $imported = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($data as $item) {
            $existing = Client::withTrashed()->where('tax_code', $item['tax_code'])->first();

            if ($existing) {
                if ($options['replace'] ?? false) {
                    $existing->update($item);
                    if ($existing->trashed() && !$item['deleted_at']) {
                        $existing->restore();
                    }
                    $updated++;
                } else {
                    $skipped++;
                }
            } else {
                Client::create($item);
                $imported++;
            }
        }

        return compact('imported', 'updated', 'skipped');
    }

    protected function importItems(array $data, array $options): array
    {
        $imported = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($data as $item) {
            $existing = Item::withTrashed()->where('code', $item['code'])->first();

            if ($existing) {
                if ($options['replace'] ?? false) {
                    $existing->update($item);
                    if ($existing->trashed() && !$item['deleted_at']) {
                        $existing->restore();
                    }
                    $updated++;
                } else {
                    $skipped++;
                }
            } else {
                Item::create($item);
                $imported++;
            }
        }

        return compact('imported', 'updated', 'skipped');
    }

    protected function importDocuments(array $data, array $options): array
    {
        $imported = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($data as $item) {
            $existing = Document::where('number', $item['number'])->first();

            if ($existing) {
                if ($options['replace'] ?? false) {
                    $existing->update($item);
                    $updated++;
                } else {
                    $skipped++;
                }
            } else {
                Document::create($item);
                $imported++;
            }
        }

        return compact('imported', 'updated', 'skipped');
    }

    protected function importDocumentItems(array $data, array $options): array
    {
        $imported = 0;
        $updated = 0;

        foreach ($data as $item) {
            $existing = DocumentItem::where('document_id', $item['document_id'])
                ->where('item_id', $item['item_id'])
                ->first();

            if ($existing) {
                $existing->update($item);
                $updated++;
            } else {
                DocumentItem::create($item);
                $imported++;
            }
        }

        return compact('imported', 'updated');
    }

    public function previewImport(array $backupData): array
    {
        if (!$this->verifySignature($backupData)) {
            throw new \Exception('Firma del backup non valida');
        }

        $data = $backupData['payload']['data'];
        $preview = [];

        foreach ($data as $table => $items) {
            $preview[$table] = [
                'total_items' => count($items),
                'sample' => array_slice($items, 0, 3), // Prime 3 righe come anteprima
            ];
        }

        return $preview;
    }
}
