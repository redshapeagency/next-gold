<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Document;
use App\Models\DocumentItem;
use App\Models\Item;
use App\Services\DocumentNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::with('client');

        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $documents = $query->paginate(15);

        return view('documents.index', compact('documents'));
    }

    public function create(Request $request)
    {
        $clients = Client::all();
        $type = $request->get('type', 'sale');

        return view('documents.create', compact('clients', 'type'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:purchase,sale',
            'client_id' => 'required|exists:clients,id',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.material' => 'required|in:gold,argento,platino,altro',
            'items.*.karat' => 'nullable|integer',
            'items.*.purity' => 'nullable|numeric',
            'items.*.weight_grams' => 'required|numeric|min:0.001',
            'items.*.price_unit' => 'required|numeric|min:0',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $documentNumberService = new DocumentNumberService();
            $number = $documentNumberService->generateNumber($request->type, $request->date);

            $totalGross = 0;
            $totalNet = 0;

            foreach ($request->items as $itemData) {
                $subtotal = $itemData['price_unit'] * $itemData['qty'];
                $totalGross += $subtotal;
                $totalNet += $subtotal;
            }

            $document = Document::create([
                'type' => $request->type,
                'number' => $number,
                'date' => $request->date,
                'client_id' => $request->client_id,
                'total_gross' => $totalGross,
                'total_net' => $totalNet,
                'status' => 'draft',
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            foreach ($request->items as $itemData) {
                DocumentItem::create([
                    'document_id' => $document->id,
                    'name' => $itemData['name'],
                    'material' => $itemData['material'],
                    'karat' => $itemData['karat'],
                    'purity' => $itemData['purity'],
                    'weight_grams' => $itemData['weight_grams'],
                    'price_unit' => $itemData['price_unit'],
                    'qty' => $itemData['qty'],
                    'subtotal' => $itemData['price_unit'] * $itemData['qty'],
                ]);
            }
        });

        return redirect()->route('documents.index')
            ->with('success', 'Documento creato con successo.');
    }

    public function show(Document $document)
    {
        $document->load('client', 'documentItems', 'creator', 'updater');

        return view('documents.show', compact('document'));
    }

    public function edit(Document $document)
    {
        if ($document->status !== 'draft') {
            abort(403, 'Non puoi modificare un documento confermato.');
        }

        $clients = Client::all();

        return view('documents.edit', compact('document', 'clients'));
    }

    public function update(Request $request, Document $document)
    {
        if ($document->status !== 'draft') {
            abort(403, 'Non puoi modificare un documento confermato.');
        }

        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.material' => 'required|in:gold,argento,platino,altro',
            'items.*.karat' => 'nullable|integer',
            'items.*.purity' => 'nullable|numeric',
            'items.*.weight_grams' => 'required|numeric|min:0.001',
            'items.*.price_unit' => 'required|numeric|min:0',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $document) {
            $totalGross = 0;
            $totalNet = 0;

            foreach ($request->items as $itemData) {
                $subtotal = $itemData['price_unit'] * $itemData['qty'];
                $totalGross += $subtotal;
                $totalNet += $subtotal;
            }

            $document->update([
                'date' => $request->date,
                'client_id' => $request->client_id,
                'total_gross' => $totalGross,
                'total_net' => $totalNet,
                'updated_by' => auth()->id(),
            ]);

            $document->documentItems()->delete();

            foreach ($request->items as $itemData) {
                DocumentItem::create([
                    'document_id' => $document->id,
                    'name' => $itemData['name'],
                    'material' => $itemData['material'],
                    'karat' => $itemData['karat'],
                    'purity' => $itemData['purity'],
                    'weight_grams' => $itemData['weight_grams'],
                    'price_unit' => $itemData['price_unit'],
                    'qty' => $itemData['qty'],
                    'subtotal' => $itemData['price_unit'] * $itemData['qty'],
                ]);
            }
        });

        return redirect()->route('documents.show', $document)
            ->with('success', 'Documento aggiornato con successo.');
    }

    public function confirm(Document $document)
    {
        if ($document->status !== 'draft') {
            abort(403, 'Il documento è già confermato.');
        }

        DB::transaction(function () use ($document) {
            $document->update(['status' => 'confirmed']);

            if ($document->type === 'sale') {
                foreach ($document->documentItems as $documentItem) {
                    if ($documentItem->item) {
                        $documentItem->item->update(['status' => 'archived']);
                    }
                }
            } elseif ($document->type === 'purchase') {
                foreach ($document->documentItems as $documentItem) {
                    Item::create([
                        'code' => 'AUTO-' . time() . '-' . rand(100, 999),
                        'name' => $documentItem->name,
                        'material' => $documentItem->material,
                        'karat' => $documentItem->karat,
                        'purity' => $documentItem->purity,
                        'weight_grams' => $documentItem->weight_grams,
                        'price_purchase' => $documentItem->price_unit,
                        'price_sale' => $documentItem->price_unit * 1.2, // Markup del 20%
                        'status' => 'in_stock',
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                    ]);
                }
            }
        });

        return redirect()->route('documents.show', $document)
            ->with('success', 'Documento confermato con successo.');
    }

    public function pdf(Document $document)
    {
        $pdf = Pdf::loadView('documents.pdf', compact('document'));

        return $pdf->download($document->number . '.pdf');
    }

    public function destroy(Document $document)
    {
        if ($document->status === 'confirmed') {
            abort(403, 'Non puoi eliminare un documento confermato.');
        }

        $document->delete();

        return redirect()->route('documents.index')
            ->with('success', 'Documento eliminato con successo.');
    }
}
