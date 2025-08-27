<?php

namespace App\Services;

use App\Models\Document;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    public function generateNumber(string $type, int $year = null): string
    {
        $year = $year ?? now()->year;
        
        return DB::transaction(function () use ($type, $year) {
            $settings = StoreSetting::current();
            return $settings->getNextDocumentNumber($type, $year);
        });
    }

    public function getNextNumber(string $type, int $year = null): string
    {
        $year = $year ?? now()->year;
        $settings = StoreSetting::current();
        $counters = $settings->doc_number_counters ?? [];
        
        $key = $year . '_' . $type;
        $current = $counters[$key] ?? 0;
        $next = $current + 1;
        
        $typeLabel = strtoupper($type);
        return sprintf('%d-%s-%04d', $year, $typeLabel, $next);
    }

    public function validateUniqueNumber(string $number, ?int $excludeDocumentId = null): bool
    {
        $query = Document::where('number', $number);
        
        if ($excludeDocumentId) {
            $query->where('id', '!=', $excludeDocumentId);
        }
        
        return !$query->exists();
    }
}
