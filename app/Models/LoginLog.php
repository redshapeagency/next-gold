<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip',
        'user_agent',
        'success',
        'attempted_email',
        'attempted_username',
    ];

    protected $casts = [
        'success' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->success ? 'Successo' : 'Fallito';
    }
}
