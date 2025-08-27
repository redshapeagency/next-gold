<?php

namespace App\Observers;

use App\Models\ActionLog;
use App\Models\Client;

class ClientObserver
{
    public function created(Client $client): void
    {
        $this->logAction('created', $client);
    }

    public function updated(Client $client): void
    {
        $this->logAction('updated', $client, $client->getDirty());
    }

    public function deleted(Client $client): void
    {
        $this->logAction('deleted', $client);
    }

    protected function logAction(string $action, Client $client, array $changes = []): void
    {
        if (!auth()->check()) {
            return;
        }

        $diff = [];
        if ($action === 'updated' && !empty($changes)) {
            foreach ($changes as $field => $newValue) {
                $oldValue = $client->getOriginal($field);
                $diff[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        ActionLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model' => Client::class,
            'model_id' => $client->id,
            'diff' => !empty($diff) ? $diff : null,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
