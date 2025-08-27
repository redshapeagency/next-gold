<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ArchiveController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::archived()->with('category');

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('code', 'ILIKE', "%{$search}%");
            });
        }

        $items = $query->paginate(15);

        return view('archive.index', compact('items'));
    }

    public function restore(Item $item)
    {
        if ($item->status !== 'archived') {
            abort(403, 'L\'elemento non è archiviato.');
        }

        $item->update(['status' => 'in_stock']);

        return redirect()->route('archive.index')
            ->with('success', 'Prodotto rimesso in magazzino con successo.');
    }
}
