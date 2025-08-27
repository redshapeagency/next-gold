<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    const TYPE_PURCHASE = 'purchase';
    const TYPE_SALE = 'sale';

    const STATUS_DRAFT = 'draft';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'type',
        'number',
        'date',
        'client_id',
        'total_gross',
        'total_net',
        'notes',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date' => 'date',
        'total_gross' => 'decimal:2',
        'total_net' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function items()
    {
        return $this->hasMany(DocumentItem::class);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeBetweenDates($query, $from, $to)
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_PURCHASE => 'Acquisto',
            self::TYPE_SALE => 'Vendita',
            default => 'Sconosciuto',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Bozza',
            self::STATUS_CONFIRMED => 'Confermato',
            self::STATUS_CANCELLED => 'Annullato',
            default => 'Sconosciuto',
        };
    }

    public function getFormattedTotalGrossAttribute(): string
    {
        return '€ ' . number_format($this->total_gross, 2);
    }

    public function getFormattedTotalNetAttribute(): string
    {
        return '€ ' . number_format($this->total_net, 2);
    }
}
