<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'item_id',
        'name',
        'material',
        'karat',
        'purity',
        'weight_grams',
        'price_unit',
        'qty',
        'subtotal',
    ];

    protected $casts = [
        'weight_grams' => 'decimal:3',
        'price_unit' => 'decimal:2',
        'qty' => 'integer',
        'subtotal' => 'decimal:2',
        'karat' => 'integer',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
