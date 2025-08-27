<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Item;
use App\Models\Document;
use App\Services\GoldPrice\GoldPriceService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $goldPriceService;

    public function __construct(GoldPriceService $goldPriceService)
    {
        $this->goldPriceService = $goldPriceService;
    }

    public function index()
    {
        // KPI principali
        $stats = [
            'clients_count' => Client::count(),
            'items_in_stock' => Item::inStock()->count(),
            'items_archived' => Item::archived()->count(),
            'recent_purchases' => Document::byType('purchase')->confirmed()->whereDate('date', '>=', now()->subDays(30))->count(),
            'recent_sales' => Document::byType('sale')->confirmed()->whereDate('date', '>=', now()->subDays(30))->count(),
        ];

        // Prezzo oro corrente
        $goldPrice = $this->goldPriceService->getLatestPrice();

        // Entrate/Uscite ultimi 30 giorni
        $revenueData = $this->getRevenueData(30);

        // Ultimi documenti
        $recentDocuments = Document::with('client')
            ->latest()
            ->limit(5)
            ->get();

        // Ultimi prodotti inseriti
        $recentItems = Item::with('category')
            ->inStock()
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'stats',
            'goldPrice',
            'revenueData',
            'recentDocuments',
            'recentItems'
        ));
    }

    protected function getRevenueData(int $days): array
    {
        $from = now()->subDays($days);
        $to = now();

        $purchases = Document::byType('purchase')
            ->confirmed()
            ->betweenDates($from, $to)
            ->selectRaw('DATE(date) as date, SUM(total_gross) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $sales = Document::byType('sale')
            ->confirmed()
            ->betweenDates($from, $to)
            ->selectRaw('DATE(date) as date, SUM(total_gross) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $data = [];
        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $data[] = [
                'date' => $date,
                'purchases' => $purchases->get($date)?->total ?? 0,
                'sales' => $sales->get($date)?->total ?? 0,
            ];
        }

        return $data;
    }
}
