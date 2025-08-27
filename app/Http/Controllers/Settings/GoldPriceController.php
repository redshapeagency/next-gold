<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\GoldPriceService;
use App\Models\GoldQuote;
use Illuminate\Http\Request;

class GoldPriceController extends Controller
{
    protected $goldPriceService;

    public function __construct(GoldPriceService $goldPriceService)
    {
        $this->middleware('auth');
        $this->middleware('permission:manage settings');
        $this->goldPriceService = $goldPriceService;
    }

    public function index(Request $request)
    {
        $query = GoldQuote::query();

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $quotes = $query->orderBy('date', 'desc')->paginate(20);
        $latestQuote = GoldQuote::latest('date')->first();

        return view('settings.gold-price.index', compact('quotes', 'latestQuote'));
    }

    public function update()
    {
        try {
            $quote = $this->goldPriceService->fetchLatestPrice();
            
            if ($quote) {
                return back()->with('success', 'Quotazione oro aggiornata con successo.');
            } else {
                return back()->with('error', 'Impossibile aggiornare la quotazione oro.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Errore durante l\'aggiornamento: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'price_per_gram_24k' => 'required|numeric|min:0',
            'price_per_gram_22k' => 'nullable|numeric|min:0',
            'price_per_gram_18k' => 'nullable|numeric|min:0',
            'price_per_gram_14k' => 'nullable|numeric|min:0',
            'source' => 'nullable|string|max:100',
        ]);

        // Check if quote already exists for this date
        $existingQuote = GoldQuote::whereDate('date', $request->date)->first();
        
        if ($existingQuote) {
            return back()->with('error', 'Esiste già una quotazione per questa data.');
        }

        GoldQuote::create([
            'date' => $request->date,
            'price_per_gram_24k' => $request->price_per_gram_24k,
            'price_per_gram_22k' => $request->price_per_gram_22k,
            'price_per_gram_18k' => $request->price_per_gram_18k,
            'price_per_gram_14k' => $request->price_per_gram_14k,
            'source' => $request->source ?: 'manual',
        ]);

        return back()->with('success', 'Quotazione oro aggiunta con successo.');
    }

    public function edit(GoldQuote $goldQuote)
    {
        return view('settings.gold-price.edit', compact('goldQuote'));
    }

    public function updateQuote(Request $request, GoldQuote $goldQuote)
    {
        $request->validate([
            'date' => 'required|date',
            'price_per_gram_24k' => 'required|numeric|min:0',
            'price_per_gram_22k' => 'nullable|numeric|min:0',
            'price_per_gram_18k' => 'nullable|numeric|min:0',
            'price_per_gram_14k' => 'nullable|numeric|min:0',
            'source' => 'nullable|string|max:100',
        ]);

        // Check if another quote exists for this date (excluding current)
        $existingQuote = GoldQuote::whereDate('date', $request->date)
            ->where('id', '!=', $goldQuote->id)
            ->first();
        
        if ($existingQuote) {
            return back()->with('error', 'Esiste già una quotazione per questa data.');
        }

        $goldQuote->update([
            'date' => $request->date,
            'price_per_gram_24k' => $request->price_per_gram_24k,
            'price_per_gram_22k' => $request->price_per_gram_22k,
            'price_per_gram_18k' => $request->price_per_gram_18k,
            'price_per_gram_14k' => $request->price_per_gram_14k,
            'source' => $request->source ?: $goldQuote->source,
        ]);

        return redirect()->route('settings.gold-price.index')
            ->with('success', 'Quotazione oro aggiornata con successo.');
    }

    public function destroy(GoldQuote $goldQuote)
    {
        $goldQuote->delete();

        return back()->with('success', 'Quotazione oro eliminata con successo.');
    }

    public function history()
    {
        $quotes = GoldQuote::orderBy('date', 'desc')->paginate(50);
        
        // Calculate statistics
        $stats = [
            'total_quotes' => GoldQuote::count(),
            'highest_price' => GoldQuote::max('price_per_gram_24k'),
            'lowest_price' => GoldQuote::min('price_per_gram_24k'),
            'average_price' => GoldQuote::avg('price_per_gram_24k'),
        ];

        return view('settings.gold-price.history', compact('quotes', 'stats'));
    }

    public function chart(Request $request)
    {
        $days = $request->get('days', 30);
        
        $quotes = GoldQuote::where('date', '>=', now()->subDays($days))
            ->orderBy('date')
            ->get(['date', 'price_per_gram_24k', 'price_per_gram_22k', 'price_per_gram_18k', 'price_per_gram_14k']);

        $chartData = [
            'labels' => $quotes->pluck('date')->map(fn($date) => $date->format('d/m')),
            'datasets' => [
                [
                    'label' => '24K',
                    'data' => $quotes->pluck('price_per_gram_24k'),
                    'borderColor' => '#FFD700',
                    'backgroundColor' => 'rgba(255, 215, 0, 0.1)',
                ],
                [
                    'label' => '22K',
                    'data' => $quotes->pluck('price_per_gram_22k'),
                    'borderColor' => '#FFA500',
                    'backgroundColor' => 'rgba(255, 165, 0, 0.1)',
                ],
                [
                    'label' => '18K',
                    'data' => $quotes->pluck('price_per_gram_18k'),
                    'borderColor' => '#FF8C00',
                    'backgroundColor' => 'rgba(255, 140, 0, 0.1)',
                ],
                [
                    'label' => '14K',
                    'data' => $quotes->pluck('price_per_gram_14k'),
                    'borderColor' => '#FF6347',
                    'backgroundColor' => 'rgba(255, 99, 71, 0.1)',
                ],
            ]
        ];

        return response()->json($chartData);
    }
}
