<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_IN_STOCK = 'in_stock';
    const STATUS_ARCHIVED = 'archived';

    const MATERIAL_GOLD = 'gold';
    const MATERIAL_SILVER = 'silver';
    const MATERIAL_PLATINUM = 'platinum';
    const MATERIAL_OTHER = 'other';

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
        'purity' => 'decimal:3',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function documentItems()
    {
        return $this->hasMany(DocumentItem::class);
    }

    public function scopeInStock($query)
    {
        return $query->where('status', self::STATUS_IN_STOCK);
    }

    public function scopeArchived($query)
    {
        return $query->where('status', self::STATUS_ARCHIVED);
    }

    public function scopeByMaterial($query, $material)
    {
        return $query->where('material', $material);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'ILIKE', "%{$search}%")
              ->orWhere('code', 'ILIKE', "%{$search}%")
              ->orWhere('description', 'ILIKE', "%{$search}%");
        });
    }

    public function getFormattedWeightAttribute(): string
    {
        return number_format($this->weight_grams, 3) . ' g';
    }

    public function getFormattedPricePurchaseAttribute(): string
    {
        return '€ ' . number_format($this->price_purchase, 2);
    }

    public function getFormattedPriceSaleAttribute(): string
    {
        return '€ ' . number_format($this->price_sale, 2);
    }

    public function getMaterialLabelAttribute(): string
    {
        return match ($this->material) {
            self::MATERIAL_GOLD => 'Oro',
            self::MATERIAL_SILVER => 'Argento',
            self::MATERIAL_PLATINUM => 'Platino',
            default => 'Altro',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_IN_STOCK => 'Disponibile',
            self::STATUS_ARCHIVED => 'Archiviato',
            default => 'Sconosciuto',
        };
    }
}
