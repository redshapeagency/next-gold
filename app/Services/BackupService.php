<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Category;
use App\Models\Item;
use App\Models\Document;
use App\Models\DocumentItem;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\Hash;

class BackupService
{
    protected string $secretKey;

    public function __construct()
    {
        $this->secretKey = config('app.backup_secret_key', 'default-secret-key');
    }

    public function export(): array
    {
        $data = [
            'timestamp' => now()->toISOString(),
            'version' => '1.0',
            'data' => [
                'categories' => Category::all()->toArray(),
                'clients' => Client::all()->toArray(),
                'items' => Item::all()->toArray(),
                'documents' => Document::all()->toArray(),
                'document_items' => DocumentItem::all()->toArray(),
                'store_settings' => StoreSetting::getInstance()->toArray(),
            ],
        ];

        $json = json_encode($data);
        $signature = hash_hmac('sha256', $json, $this->secretKey);

        return [
            'data' => $data,
            'signature' => $signature,
        ];
    }

    public function import($file, string $mode = 'append'): array
    {
        $content = file_get_contents($file->getRealPath());
        $backup = json_decode($content, true);

        if (!$this->verifySignature($backup)) {
            throw new \Exception('Invalid backup signature');
        }

        $data = $backup['data'];

        if ($mode === 'replace') {
            $this->replaceData($data);
        } else {
            $this->appendData($data);
        }

        return [
            'message' => 'Import completed successfully',
            'imported' => [
                'categories' => count($data['categories'] ?? []),
                'clients' => count($data['clients'] ?? []),
                'items' => count($data['items'] ?? []),
                'documents' => count($data['documents'] ?? []),
            ],
        ];
    }

    protected function verifySignature(array $backup): bool
    {
        $data = $backup['data'];
        $signature = $backup['signature'];

        $json = json_encode($data);
        $expectedSignature = hash_hmac('sha256', $json, $this->secretKey);

        return hash_equals($expectedSignature, $signature);
    }

    protected function replaceData(array $data): void
    {
        // Clear existing data
        DocumentItem::truncate();
        Document::truncate();
        Item::truncate();
        Client::truncate();
        Category::truncate();

        // Import new data
        $this->importCategories($data['categories'] ?? []);
        $this->importClients($data['clients'] ?? []);
        $this->importItems($data['items'] ?? []);
        $this->importDocuments($data['documents'] ?? []);
        $this->importStoreSettings($data['store_settings'] ?? []);
    }

    protected function appendData(array $data): void
    {
        $this->importCategories($data['categories'] ?? []);
        $this->importClients($data['clients'] ?? []);
        $this->importItems($data['items'] ?? []);
        $this->importDocuments($data['documents'] ?? []);
        $this->importStoreSettings($data['store_settings'] ?? []);
    }

    protected function importCategories(array $categories): void
    {
        foreach ($categories as $category) {
            unset($category['id'], $category['created_at'], $category['updated_at']);
            Category::firstOrCreate($category);
        }
    }

    protected function importClients(array $clients): void
    {
        foreach ($clients as $client) {
            unset($client['id'], $client['created_at'], $client['updated_at']);
            Client::create($client);
        }
    }

    protected function importItems(array $items): void
    {
        foreach ($items as $item) {
            unset($item['id'], $item['created_at'], $item['updated_at']);
            Item::create($item);
        }
    }

    protected function importDocuments(array $documents): void
    {
        foreach ($documents as $document) {
            $items = $document['document_items'] ?? [];
            unset($document['id'], $document['created_at'], $document['updated_at'], $document['document_items']);

            $createdDocument = Document::create($document);

            foreach ($items as $item) {
                unset($item['id'], $item['document_id'], $item['created_at'], $item['updated_at']);
                $createdDocument->documentItems()->create($item);
            }
        }
    }

    protected function importStoreSettings(array $settings): void
    {
        if (!empty($settings)) {
            unset($settings['id'], $settings['created_at'], $settings['updated_at']);
            StoreSetting::getInstance()->update($settings);
        }
    }
}
