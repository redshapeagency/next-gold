<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category_id',
        'material',
        'karat',
        'purity',
        'weight_grams',
        'price_purchase',
        'price_sale',
        'description',
        'photo_path',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'weight_grams' => 'decimal:3',
        'price_purchase' => 'decimal:2',
        'price_sale' => 'decimal:2',
        'karat' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function documentItems(): HasMany
    {
        return $this->hasMany(DocumentItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeInStock($query)
    {
        return $query->where('status', 'in_stock');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }
}
