<?php

namespace App\Services;

use App\Models\StoreSetting;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    public function generateNumber(string $type, string $date): string
    {
        $year = date('Y', strtotime($date));
        $typeUpper = strtoupper($type);

        return DB::transaction(function () use ($type, $year, $typeUpper) {
            $settings = StoreSetting::getInstance();
            $counters = $settings->doc_number_counters ?? [];

            if (!isset($counters[$year])) {
                $counters[$year] = [
                    'sale' => 0,
                    'purchase' => 0,
                ];
            }

            if (!isset($counters[$year][$type])) {
                $counters[$year][$type] = 0;
            }

            $counters[$year][$type]++;
            $number = $counters[$year][$type];

            $settings->update(['doc_number_counters' => $counters]);

            return sprintf('%s-%s-%03d', $year, $typeUpper, $number);
        });
    }
}
