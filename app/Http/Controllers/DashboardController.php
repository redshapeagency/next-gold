<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Document;
use App\Models\GoldQuote;
use App\Models\Item;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'clients_count' => Client::count(),
            'items_count' => Item::inStock()->count(),
            'documents_count' => Document::confirmed()->count(),
            'archived_items_count' => Item::archived()->count(),
        ];

        $latestGoldQuote = GoldQuote::latest('fetched_at')->first();

        $recentDocuments = Document::with('client')
            ->latest()
            ->take(5)
            ->get();

        $recentItems = Item::with('category')
            ->latest()
            ->take(5)
            ->get();

        // Income/Expense data for last 30 days
        $incomeExpenseData = $this->getIncomeExpenseData();

        return view('dashboard.index', compact(
            'stats',
            'latestGoldQuote',
            'recentDocuments',
            'recentItems',
            'incomeExpenseData'
        ));
    }

    private function getIncomeExpenseData()
    {
        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();

            $income = Document::sale()
                ->confirmed()
                ->whereDate('date', $date)
                ->sum('total_net');

            $expense = Document::purchase()
                ->confirmed()
                ->whereDate('date', $date)
                ->sum('total_net');

            $data[] = [
                'date' => $date,
                'income' => $income,
                'expense' => $expense,
            ];
        }

        return $data;
    }
}
