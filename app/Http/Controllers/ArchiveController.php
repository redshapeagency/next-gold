<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ArchiveController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view archive')->only(['index', 'show']);
        $this->middleware('permission:export archive')->only(['export']);
    }

    public function index(Request $request)
    {
        $documentsQuery = Document::with(['client', 'creator'])
            ->where('status', 'confirmed');

        $itemsQuery = Item::with(['category', 'creator'])
            ->where('status', 'archived');

        // Filters for documents
        if ($request->filled('doc_type')) {
            $documentsQuery->where('type', $request->doc_type);
        }

        if ($request->filled('doc_date_from')) {
            $documentsQuery->whereDate('date', '>=', $request->doc_date_from);
        }

        if ($request->filled('doc_date_to')) {
            $documentsQuery->whereDate('date', '<=', $request->doc_date_to);
        }

        if ($request->filled('doc_search')) {
            $documentsQuery->where(function ($q) use ($request) {
                $q->where('number', 'ILIKE', '%' . $request->doc_search . '%')
                  ->orWhere('notes', 'ILIKE', '%' . $request->doc_search . '%')
                  ->orWhereHas('client', function ($q2) use ($request) {
                      $q2->where('name', 'ILIKE', '%' . $request->doc_search . '%')
                        ->orWhere('surname', 'ILIKE', '%' . $request->doc_search . '%')
                        ->orWhere('company_name', 'ILIKE', '%' . $request->doc_search . '%');
                  });
            });
        }

        // Filters for items
        if ($request->filled('item_category')) {
            $itemsQuery->where('category_id', $request->item_category);
        }

        if ($request->filled('item_search')) {
            $itemsQuery->where(function ($q) use ($request) {
                $q->where('code', 'ILIKE', '%' . $request->item_search . '%')
                  ->orWhere('name', 'ILIKE', '%' . $request->item_search . '%')
                  ->orWhere('description', 'ILIKE', '%' . $request->item_search . '%');
            });
        }

        // Pagination
        $documents = $documentsQuery->orderBy('date', 'desc')->paginate(10, ['*'], 'docs_page');
        $items = $itemsQuery->orderBy('created_at', 'desc')->paginate(10, ['*'], 'items_page');

        // Statistics
        $stats = [
            'total_documents' => Document::where('status', 'confirmed')->count(),
            'total_items' => Item::where('status', 'archived')->count(),
            'purchases_value' => Document::where('type', 'purchase')
                ->where('status', 'confirmed')
                ->sum('total_amount'),
            'sales_value' => Document::where('type', 'sale')
                ->where('status', 'confirmed')
                ->sum('total_amount'),
        ];

        return view('archive.index', compact('documents', 'items', 'stats'));
    }

    public function documents(Request $request)
    {
        $query = Document::with(['client', 'creator'])
            ->where('status', 'confirmed');

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
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

        $documents = $query->orderBy('date', 'desc')->paginate(20);

        return view('archive.documents', compact('documents'));
    }

    public function items(Request $request)
    {
        $query = Item::with(['category', 'creator'])
            ->where('status', 'archived');

        // Apply filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('code', 'ILIKE', '%' . $request->search . '%')
                  ->orWhere('name', 'ILIKE', '%' . $request->search . '%')
                  ->orWhere('description', 'ILIKE', '%' . $request->search . '%');
            });
        }

        $items = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('archive.items', compact('items'));
    }

    public function export(Request $request)
    {
        $type = $request->get('type', 'all'); // all, documents, items
        $format = $request->get('format', 'zip'); // zip, csv

        switch ($type) {
            case 'documents':
                return $this->exportDocuments($format, $request);
            case 'items':
                return $this->exportItems($format, $request);
            default:
                return $this->exportAll($format, $request);
        }
    }

    private function exportDocuments($format, $request)
    {
        $query = Document::with(['client', 'items.item', 'creator'])
            ->where('status', 'confirmed');

        // Apply date filters
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $documents = $query->orderBy('date', 'desc')->get();

        if ($format === 'csv') {
            return $this->exportDocumentsCsv($documents);
        }

        return $this->exportDocumentsZip($documents);
    }

    private function exportItems($format, $request)
    {
        $items = Item::with(['category', 'creator'])
            ->where('status', 'archived')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($format === 'csv') {
            return $this->exportItemsCsv($items);
        }

        return $this->exportItemsZip($items);
    }

    private function exportAll($format, $request)
    {
        if ($format === 'csv') {
            // Create a ZIP with both CSV files
            $zipFileName = 'archivio_completo_' . now()->format('Y-m-d_H-i-s') . '.zip';
            $zipPath = storage_path('app/temp/' . $zipFileName);

            $zip = new ZipArchive;
            if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                // Add documents CSV
                $documentsData = $this->getDocumentsCsvData();
                $zip->addFromString('documenti.csv', $documentsData);

                // Add items CSV
                $itemsData = $this->getItemsCsvData();
                $zip->addFromString('articoli.csv', $itemsData);

                $zip->close();

                return response()->download($zipPath)->deleteFileAfterSend();
            }
        }

        // Default ZIP export
        return $this->exportAllZip($request);
    }

    private function exportDocumentsCsv($documents)
    {
        $csvData = "Numero,Data,Tipo,Cliente,Importo Totale,Note,Creato da,Data Creazione\n";
        
        foreach ($documents as $document) {
            $clientName = $document->client 
                ? ($document->client->company_name ?: $document->client->full_name)
                : '';
            
            $csvData .= sprintf(
                "%s,%s,%s,%s,%.2f,%s,%s,%s\n",
                $document->number,
                $document->date,
                $document->type === 'purchase' ? 'Acquisto' : 'Vendita',
                str_replace(',', ';', $clientName),
                $document->total_amount,
                str_replace(',', ';', $document->notes ?? ''),
                $document->creator->name ?? '',
                $document->created_at->format('Y-m-d H:i:s')
            );
        }

        $fileName = 'documenti_archivio_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    private function exportItemsCsv($items)
    {
        $csvData = "Codice,Nome,Categoria,Descrizione,Peso,Prezzo,Data Creazione\n";
        
        foreach ($items as $item) {
            $csvData .= sprintf(
                "%s,%s,%s,%s,%.2f,%.2f,%s\n",
                $item->code,
                str_replace(',', ';', $item->name),
                $item->category->name ?? '',
                str_replace(',', ';', $item->description ?? ''),
                $item->weight,
                $item->price,
                $item->created_at->format('Y-m-d H:i:s')
            );
        }

        $fileName = 'articoli_archivio_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    private function exportDocumentsZip($documents)
    {
        $zipFileName = 'documenti_archivio_' . now()->format('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($documents as $document) {
                $documentData = [
                    'document' => $document->toArray(),
                    'client' => $document->client?->toArray(),
                    'items' => $document->items->toArray(),
                ];
                
                $filename = "documento_{$document->number}.json";
                $zip->addFromString($filename, json_encode($documentData, JSON_PRETTY_PRINT));
            }
            
            $zip->close();
            
            return response()->download($zipPath)->deleteFileAfterSend();
        }

        return back()->with('error', 'Errore durante la creazione del file ZIP.');
    }

    private function exportItemsZip($items)
    {
        $zipFileName = 'articoli_archivio_' . now()->format('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($items as $item) {
                $filename = "articolo_{$item->code}.json";
                $zip->addFromString($filename, json_encode($item->toArray(), JSON_PRETTY_PRINT));
                
                // Add photo if exists
                if ($item->photo && Storage::exists($item->photo)) {
                    $photoPath = Storage::path($item->photo);
                    $photoName = "foto_{$item->code}." . pathinfo($item->photo, PATHINFO_EXTENSION);
                    $zip->addFile($photoPath, $photoName);
                }
            }
            
            $zip->close();
            
            return response()->download($zipPath)->deleteFileAfterSend();
        }

        return back()->with('error', 'Errore durante la creazione del file ZIP.');
    }

    private function exportAllZip($request)
    {
        $zipFileName = 'archivio_completo_' . now()->format('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            // Export documents
            $documents = Document::with(['client', 'items.item', 'creator'])
                ->where('status', 'confirmed')
                ->orderBy('date', 'desc')
                ->get();

            foreach ($documents as $document) {
                $documentData = [
                    'document' => $document->toArray(),
                    'client' => $document->client?->toArray(),
                    'items' => $document->items->toArray(),
                ];
                
                $filename = "documenti/documento_{$document->number}.json";
                $zip->addFromString($filename, json_encode($documentData, JSON_PRETTY_PRINT));
            }

            // Export items
            $items = Item::with(['category', 'creator'])
                ->where('status', 'archived')
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($items as $item) {
                $filename = "articoli/articolo_{$item->code}.json";
                $zip->addFromString($filename, json_encode($item->toArray(), JSON_PRETTY_PRINT));
                
                // Add photo if exists
                if ($item->photo && Storage::exists($item->photo)) {
                    $photoPath = Storage::path($item->photo);
                    $photoName = "foto/foto_{$item->code}." . pathinfo($item->photo, PATHINFO_EXTENSION);
                    $zip->addFile($photoPath, $photoName);
                }
            }
            
            $zip->close();
            
            return response()->download($zipPath)->deleteFileAfterSend();
        }

        return back()->with('error', 'Errore durante la creazione del file ZIP.');
    }
}
