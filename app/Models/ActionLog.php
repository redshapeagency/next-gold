<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'model',
        'model_id',
        'diff',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'diff' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByModel($query, $model)
    {
        return $query->where('model', $model);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function getFormattedDiffAttribute(): string
    {
        if (!$this->diff) {
            return '';
        }

        $output = [];
        foreach ($this->diff as $field => $changes) {
            if (is_array($changes) && isset($changes['old'], $changes['new'])) {
                $old = $changes['old'] ?? 'vuoto';
                $new = $changes['new'] ?? 'vuoto';
                $output[] = "{$field}: {$old} → {$new}";
            }
        }

        return implode(', ', $output);
    }
}
