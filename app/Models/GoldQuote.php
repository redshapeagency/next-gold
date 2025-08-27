<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoldQuote extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'bid',
        'ask',
        'unit',
        'currency',
        'fetched_at',
    ];

    protected $casts = [
        'bid' => 'decimal:2',
        'ask' => 'decimal:2',
        'fetched_at' => 'datetime',
    ];

    public function scopeLatest($query)
    {
        return $query->orderBy('fetched_at', 'desc');
    }
}
