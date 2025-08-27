<?php

namespace App\Observers;

use App\Models\ActionLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class ActionLogObserver
{
    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        $this->logAction('created', $model);
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        $this->logAction('updated', $model, $model->getOriginal());
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->logAction('deleted', $model);
    }

    /**
     * Handle the Model "restored" event.
     */
    public function restored(Model $model): void
    {
        $this->logAction('restored', $model);
    }

    /**
     * Handle the Model "force deleted" event.
     */
    public function forceDeleted(Model $model): void
    {
        $this->logAction('force_deleted', $model);
    }

    protected function logAction(string $action, Model $model, array $original = null): void
    {
        $diff = null;
        if ($original && $action === 'updated') {
            $diff = array_diff_assoc($model->getAttributes(), $original);
        }

        ActionLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model' => get_class($model),
            'model_id' => $model->getKey(),
            'diff' => $diff,
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
