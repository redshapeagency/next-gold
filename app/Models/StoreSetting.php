<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_name',
        'vat_number',
        'tax_code',
        'address',
        'city',
        'zip',
        'country',
        'phone',
        'email',
        'logo_path',
        'doc_number_counters',
        'currency',
        'locale',
    ];

    protected $casts = [
        'doc_number_counters' => 'array',
    ];

    public static function current()
    {
        return static::first() ?? static::create([
            'business_name' => 'Next Gold',
            'currency' => 'EUR',
            'locale' => 'it_IT',
            'country' => 'Italia',
            'doc_number_counters' => [],
        ]);
    }

    public function getNextDocumentNumber(string $type, int $year = null): string
    {
        $year = $year ?? now()->year;
        $counters = $this->doc_number_counters ?? [];
        
        $key = $year . '_' . $type;
        $current = $counters[$key] ?? 0;
        $next = $current + 1;
        
        $counters[$key] = $next;
        $this->update(['doc_number_counters' => $counters]);
        
        $typeLabel = strtoupper($type);
        return sprintf('%d-%s-%04d', $year, $typeLabel, $next);
    }
}
