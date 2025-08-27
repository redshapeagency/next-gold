<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentItem;
use App\Models\Item;
use App\Models\Client;
use App\Models\StoreSettings;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Services\DocumentNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentController extends Controller
{
    protected $documentNumberService;

    public function __construct(DocumentNumberService $documentNumberService)
    {
        $this->middleware('auth');
        $this->middleware('permission:view documents')->only(['index', 'show']);
        $this->middleware('permission:create documents')->only(['create', 'store']);
        $this->middleware('permission:edit documents')->only(['edit', 'update']);
        $this->middleware('permission:delete documents')->only(['destroy']);
        $this->middleware('permission:confirm documents')->only(['confirm']);
        
        $this->documentNumberService = $documentNumberService;
    }

    public function index(Request $request)
    {
        $query = Document::with(['client', 'creator']);

        // Filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('number', 'ILIKE', '%' . $request->search . '%')
                  ->orWhere('notes', 'ILIKE', '%' . $request->search . '%')
                  ->orWhereHas('client', function ($q2) use ($request) {
                      $q2->where('name', 'ILIKE', '%' . $request->search . '%')
                        ->orWhere('surname', 'ILIKE', '%' . $request->search . '%')
                        ->orWhere('company_name', 'ILIKE', '%' . $request->search . '%');
                  });
            });
        }

        // Date range
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort', 'date');
        $sortDirection = $request->get('direction', 'desc');
        
        $allowedSorts = ['number', 'date', 'type', 'status', 'total_amount', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        }

        $documents = $query->paginate(20)->withQueryString();
        $clients = Client::orderBy('surname')->orderBy('name')->get();

        return view('documents.index', compact('documents', 'clients'));
    }

    public function create(Request $request)
    {
        $type = $request->get('type', 'purchase');
        $clients = Client::orderBy('surname')->orderBy('name')->get();
        $items = Item::with('category')->where('status', 'active')->orderBy('name')->get();

        return view('documents.create', compact('type', 'clients', 'items'));
    }

    public function store(StoreDocumentRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        
        try {
            // Generate document number
            $validated['number'] = $this->documentNumberService->generate($validated['type']);
            $validated['created_by'] = auth()->id();

            $document = Document::create($validated);

            // Add document items
            $totalAmount = 0;
            foreach ($validated['items'] as $itemData) {
                $item = Item::findOrFail($itemData['item_id']);
                
                $documentItem = DocumentItem::create([
                    'document_id' => $document->id,
                    'item_id' => $item->id,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $itemData['quantity'] * $itemData['unit_price'],
                    'notes' => $itemData['notes'] ?? null,
                ]);

                $totalAmount += $documentItem->total_price;
            }

            // Update document total
            $document->update(['total_amount' => $totalAmount]);

            DB::commit();

            return redirect()->route('documents.show', $document)
                ->with('success', 'Documento creato con successo.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Errore durante la creazione del documento: ' . $e->getMessage());
        }
    }

    public function show(Document $document)
    {
        $document->load(['client', 'items.item.category', 'creator']);
        return view('documents.show', compact('document'));
    }

    public function edit(Document $document)
    {
        if ($document->status === 'confirmed') {
            return redirect()->route('documents.show', $document)
                ->with('error', 'Impossibile modificare un documento confermato.');
        }

        $document->load(['client', 'items.item']);
        $clients = Client::orderBy('surname')->orderBy('name')->get();
        $items = Item::with('category')->where('status', 'active')->orderBy('name')->get();

        return view('documents.edit', compact('document', 'clients', 'items'));
    }

    public function update(UpdateDocumentRequest $request, Document $document)
    {
        if ($document->status === 'confirmed') {
            return redirect()->route('documents.show', $document)
                ->with('error', 'Impossibile modificare un documento confermato.');
        }

        $validated = $request->validated();

        DB::beginTransaction();
        
        try {
            $document->update($validated);

            // Delete existing items
            $document->items()->delete();

            // Add new items
            $totalAmount = 0;
            foreach ($validated['items'] as $itemData) {
                $item = Item::findOrFail($itemData['item_id']);
                
                $documentItem = DocumentItem::create([
                    'document_id' => $document->id,
                    'item_id' => $item->id,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $itemData['quantity'] * $itemData['unit_price'],
                    'notes' => $itemData['notes'] ?? null,
                ]);

                $totalAmount += $documentItem->total_price;
            }

            // Update document total
            $document->update(['total_amount' => $totalAmount]);

            DB::commit();

            return redirect()->route('documents.show', $document)
                ->with('success', 'Documento aggiornato con successo.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Errore durante l\'aggiornamento del documento: ' . $e->getMessage());
        }
    }

    public function destroy(Document $document)
    {
        if ($document->status === 'confirmed') {
            return back()->with('error', 'Impossibile eliminare un documento confermato.');
        }

        $document->delete();

        return redirect()->route('documents.index')
            ->with('success', 'Documento eliminato con successo.');
    }

    public function confirm(Document $document)
    {
        if ($document->status === 'confirmed') {
            return back()->with('error', 'Il documento è già confermato.');
        }

        $document->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'confirmed_by' => auth()->id(),
        ]);

        return back()->with('success', 'Documento confermato con successo.');
    }

    public function generatePdf(Document $document)
    {
        $document->load(['client', 'items.item.category', 'creator']);
        $storeSettings = StoreSettings::first();

        $pdf = Pdf::loadView('documents.pdf', compact('document', 'storeSettings'));
        
        $filename = "documento_{$document->number}.pdf";
        
        return $pdf->download($filename);
    }

    public function duplicate(Document $document)
    {
        $document->load(['items.item']);
        
        $newDocument = $document->replicate([
            'number',
            'status',
            'confirmed_at',
            'confirmed_by',
            'created_at',
            'updated_at'
        ]);
        
        $newDocument->number = $this->documentNumberService->generate($document->type);
        $newDocument->date = now()->format('Y-m-d');
        $newDocument->status = 'draft';
        $newDocument->created_by = auth()->id();
        $newDocument->save();

        // Copy items
        foreach ($document->items as $item) {
            $newItem = $item->replicate();
            $newItem->document_id = $newDocument->id;
            $newItem->save();
        }

        return redirect()->route('documents.edit', $newDocument)
            ->with('success', 'Documento duplicato con successo.');
    }
}
