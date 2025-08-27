<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemRequest;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view items')->only(['index', 'show']);
        $this->middleware('permission:create items')->only(['create', 'store']);
        $this->middleware('permission:edit items')->only(['edit', 'update']);
        $this->middleware('permission:delete items')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Item::with(['category', 'creator']);

        // Filtri
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->inStock(); // Default: solo disponibili
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('material')) {
            $query->byMaterial($request->material);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Range prezzi
        if ($request->filled('price_min')) {
            $query->where('price_sale', '>=', $request->price_min);
        }

        if ($request->filled('price_max')) {
            $query->where('price_sale', '<=', $request->price_max);
        }

        // Ordinamento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $items = $query->paginate(20)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('items.index', compact('items', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('items.create', compact('categories'));
    }

    public function store(ItemRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();
        $data['code'] = $this->generateItemCode();

        // Upload foto
        if ($request->hasFile('photo')) {
            $data['photo_path'] = $this->uploadPhoto($request->file('photo'));
        }

        $item = Item::create($data);

        return redirect()->route('items.show', $item)
            ->with('success', 'Prodotto creato con successo.');
    }

    public function show(Item $item)
    {
        $item->load(['category', 'creator', 'updater']);
        return view('items.show', compact('item'));
    }

    public function edit(Item $item)
    {
        $categories = Category::orderBy('name')->get();
        return view('items.edit', compact('item', 'categories'));
    }

    public function update(ItemRequest $request, Item $item)
    {
        $data = $request->validated();
        $data['updated_by'] = auth()->id();

        // Upload foto
        if ($request->hasFile('photo')) {
            // Elimina la foto precedente
            if ($item->photo_path) {
                Storage::delete($item->photo_path);
            }
            $data['photo_path'] = $this->uploadPhoto($request->file('photo'));
        }

        $item->update($data);

        return redirect()->route('items.show', $item)
            ->with('success', 'Prodotto aggiornato con successo.');
    }

    public function destroy(Item $item)
    {
        // Verifica che non sia in documenti confermati
        if ($item->documentItems()->whereHas('document', function ($q) {
            $q->where('status', 'confirmed');
        })->exists()) {
            return back()->with('error', 'Impossibile eliminare un prodotto presente in documenti confermati.');
        }

        // Elimina la foto
        if ($item->photo_path) {
            Storage::delete($item->photo_path);
        }

        $item->delete();

        return redirect()->route('items.index')
            ->with('success', 'Prodotto eliminato con successo.');
    }

    public function restore(Item $item)
    {
        if ($item->status !== Item::STATUS_ARCHIVED) {
            return back()->with('error', 'Il prodotto non è archiviato.');
        }

        $item->update(['status' => Item::STATUS_IN_STOCK]);

        return back()->with('success', 'Prodotto rimesso in magazzino.');
    }

    protected function generateItemCode(): string
    {
        do {
            $code = 'ITM-' . strtoupper(Str::random(8));
        } while (Item::where('code', $code)->exists());

        return $code;
    }

    protected function uploadPhoto($file): string
    {
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
        $path = "items/{$filename}";

        // Ridimensiona l'immagine
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->getRealPath());
        $image->resize(800, 600, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        Storage::put($path, $image->encode());

        return $path;
    }
}
