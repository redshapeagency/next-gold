<?php

namespace App\Observers;

use App\Models\ActionLog;
use App\Models\Item;

class ItemObserver
{
    public function created(Item $item): void
    {
        $this->logAction('created', $item);
    }

    public function updated(Item $item): void
    {
        $this->logAction('updated', $item, $item->getDirty());
    }

    public function deleted(Item $item): void
    {
        $this->logAction('deleted', $item);
    }

    protected function logAction(string $action, Item $item, array $changes = []): void
    {
        if (!auth()->check()) {
            return;
        }

        $diff = [];
        if ($action === 'updated' && !empty($changes)) {
            foreach ($changes as $field => $newValue) {
                $oldValue = $item->getOriginal($field);
                $diff[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        ActionLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model' => Item::class,
            'model_id' => $item->id,
            'diff' => !empty($diff) ? $diff : null,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
