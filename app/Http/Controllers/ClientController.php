<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientRequest;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view clients')->only(['index', 'show']);
        $this->middleware('permission:create clients')->only(['create', 'store']);
        $this->middleware('permission:edit clients')->only(['edit', 'update']);
        $this->middleware('permission:delete clients')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Client::with(['creator', 'updater']);

        // Ricerca
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Ordinamento
        $sortBy = $request->get('sort_by', 'last_name');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        $clients = $query->paginate(15)->withQueryString();

        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(ClientRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        $client = Client::create($data);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Cliente creato con successo.');
    }

    public function show(Client $client)
    {
        $client->load(['creator', 'updater', 'documents.items']);
        
        // Documenti del cliente
        $documents = $client->documents()
            ->with(['items'])
            ->latest()
            ->paginate(10);

        return view('clients.show', compact('client', 'documents'));
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(ClientRequest $request, Client $client)
    {
        $data = $request->validated();
        $data['updated_by'] = auth()->id();

        $client->update($data);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Cliente aggiornato con successo.');
    }

    public function destroy(Client $client)
    {
        // Verifica che non abbia documenti attivi
        if ($client->documents()->exists()) {
            return back()->with('error', 'Impossibile eliminare un cliente con documenti associati.');
        }

        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Cliente eliminato con successo.');
    }
}
