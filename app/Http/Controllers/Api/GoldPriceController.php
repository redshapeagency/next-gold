<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GoldQuote;
use App\Services\GoldPriceService;
use Illuminate\Http\Request;

class GoldPriceController extends Controller
{
    protected $goldPriceService;

    public function __construct(GoldPriceService $goldPriceService)
    {
        $this->middleware('auth:sanctum');
        $this->goldPriceService = $goldPriceService;
    }

    public function current()
    {
        $latestQuote = GoldQuote::latest('date')->first();

        if (!$latestQuote) {
            return response()->json([
                'message' => 'Nessuna quotazione disponibile'
            ], 404);
        }

        return response()->json([
            'data' => [
                'date' => $latestQuote->date,
                'prices' => [
                    '24k' => $latestQuote->price_per_gram_24k,
                    '22k' => $latestQuote->price_per_gram_22k,
                    '18k' => $latestQuote->price_per_gram_18k,
                    '14k' => $latestQuote->price_per_gram_14k,
                ],
                'source' => $latestQuote->source,
                'updated_at' => $latestQuote->updated_at,
            ]
        ]);
    }

    public function history(Request $request)
    {
        $request->validate([
            'days' => 'nullable|integer|min:1|max:365',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $query = GoldQuote::query();

        if ($request->filled('days')) {
            $query->where('date', '>=', now()->subDays($request->days));
        }

        $quotes = $query->orderBy('date', 'desc')
            ->limit($request->get('limit', 30))
            ->get();

        return response()->json([
            'data' => $quotes->map(function ($quote) {
                return [
                    'date' => $quote->date,
                    'prices' => [
                        '24k' => $quote->price_per_gram_24k,
                        '22k' => $quote->price_per_gram_22k,
                        '18k' => $quote->price_per_gram_18k,
                        '14k' => $quote->price_per_gram_14k,
                    ],
                    'source' => $quote->source,
                ];
            })
        ]);
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'weight' => 'required|numeric|min:0',
            'karat' => 'required|in:24,22,18,14',
        ]);

        $latestQuote = GoldQuote::latest('date')->first();

        if (!$latestQuote) {
            return response()->json([
                'message' => 'Nessuna quotazione disponibile'
            ], 404);
        }

        $pricePerGram = match ($request->karat) {
            '24' => $latestQuote->price_per_gram_24k,
            '22' => $latestQuote->price_per_gram_22k,
            '18' => $latestQuote->price_per_gram_18k,
            '14' => $latestQuote->price_per_gram_14k,
            default => $latestQuote->price_per_gram_24k,
        };

        $totalValue = $request->weight * $pricePerGram;

        return response()->json([
            'data' => [
                'weight' => $request->weight,
                'karat' => $request->karat . 'k',
                'price_per_gram' => $pricePerGram,
                'total_value' => round($totalValue, 2),
                'currency' => 'EUR',
                'quote_date' => $latestQuote->date,
            ]
        ]);
    }

    public function update()
    {
        try {
            $quote = $this->goldPriceService->fetchLatestPrice();
            
            if ($quote) {
                return response()->json([
                    'message' => 'Quotazione aggiornata con successo',
                    'data' => [
                        'date' => $quote->date,
                        'prices' => [
                            '24k' => $quote->price_per_gram_24k,
                            '22k' => $quote->price_per_gram_22k,
                            '18k' => $quote->price_per_gram_18k,
                            '14k' => $quote->price_per_gram_14k,
                        ],
                        'source' => $quote->source,
                    ]
                ]);
            } else {
                return response()->json([
                    'message' => 'Impossibile aggiornare la quotazione'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'aggiornamento',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
