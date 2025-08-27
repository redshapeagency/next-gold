<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'purity' => 'decimal:3',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function getFormattedWeightAttribute(): string
    {
        return number_format($this->weight_grams, 3) . ' g';
    }

    public function getFormattedPriceUnitAttribute(): string
    {
        return '€ ' . number_format($this->price_unit, 2);
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return '€ ' . number_format($this->subtotal, 2);
    }

    public function getMaterialLabelAttribute(): string
    {
        return match ($this->material) {
            'gold' => 'Oro',
            'silver' => 'Argento',
            'platinum' => 'Platino',
            default => 'Altro',
        };
    }
}
