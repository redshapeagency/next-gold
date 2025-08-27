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

    /**
     * Get the singleton instance.
     */
    public static function getInstance(): self
    {
        return self::firstOrCreate([]);
    }
}
