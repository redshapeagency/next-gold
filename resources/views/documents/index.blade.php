@extends('layouts.app')

@section('title', 'Gestione Documenti')

@section('header')
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestione Documenti</h1>
            <p class="mt-2 text-sm text-gray-700">Gestisci acquisti e vendite</p>
        </div>
        @can('create documents')
            <div class="mt-4 sm:mt-0 flex space-x-3">
                <a href="{{ route('documents.create', ['type' => 'purchase']) }}" class="btn btn-success">
                    <i class="fas fa-plus mr-2"></i>
                    Nuovo Acquisto
                </a>
                <a href="{{ route('documents.create', ['type' => 'sale']) }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>
                    Nuova Vendita
                </a>
            </div>
        @endcan
    </div>
@endsection

@section('content')
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <form method="GET" action="{{ route('documents.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Cerca</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                       placeholder="Numero, cliente, note..." 
                       class="form-input">
            </div>
            
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                <select name="type" id="type" class="form-select">
                    <option value="">Tutti i tipi</option>
                    <option value="purchase" {{ request('type') === 'purchase' ? 'selected' : '' }}>Acquisto</option>
                    <option value="sale" {{ request('type') === 'sale' ? 'selected' : '' }}>Vendita</option>
                </select>
            </div>
            
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Stato</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Tutti gli stati</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Bozza</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confermato</option>
                </select>
            </div>
            
            <div>
                <label for="client_id" class="block text-sm font-medium text-gray-700 mb-2">Cliente</label>
                <select name="client_id" id="client_id" class="form-select">
                    <option value="">Tutti i clienti</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                            {{ $client->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex items-end space-x-2">
                <button type="submit" class="btn btn-outline-primary flex-1">
                    <i class="fas fa-search mr-2"></i>
                    Filtra
                </button>
                <a href="{{ route('documents.index') }}" class="btn btn-outline-gray">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-file-alt text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Totale Documenti</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $documents->total() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-shopping-cart text-green-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Acquisti del Mese</p>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ App\Models\Document::where('type', 'purchase')->whereMonth('date', now()->month)->count() }}
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <i class="fas fa-handshake text-purple-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Vendite del Mese</p>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ App\Models\Document::where('type', 'sale')->whereMonth('date', now()->month)->count() }}
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <i class="fas fa-euro-sign text-yellow-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Fatturato Mese</p>
                    <p class="text-2xl font-bold text-gray-900">
                        €{{ number_format(App\Models\Document::where('type', 'sale')->where('status', 'confirmed')->whereMonth('date', now()->month)->sum('total_amount'), 2) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'number', 'direction' => request('sort') === 'number' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                               class="hover:text-gray-700">
                                Numero
                                @if(request('sort') === 'number')
                                    <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'date', 'direction' => request('sort') === 'date' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                               class="hover:text-gray-700">
                                Data
                                @if(request('sort') === 'date')
                                    <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tipo
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Cliente
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'total_amount', 'direction' => request('sort') === 'total_amount' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                               class="hover:text-gray-700">
                                Importo
                                @if(request('sort') === 'total_amount')
                                    <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Stato
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Azioni
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($documents as $document)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                                {{ $document->number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $document->date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($document->type === 'purchase')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-shopping-cart mr-1"></i>
                                        Acquisto
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-handshake mr-1"></i>
                                        Vendita
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $document->client->full_name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                €{{ number_format($document->total_amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($document->status === 'confirmed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i>
                                        Confermato
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-edit mr-1"></i>
                                        Bozza
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('documents.show', $document) }}" class="text-blue-600 hover:text-blue-900" title="Visualizza">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('edit documents')
                                        @if($document->status === 'draft')
                                            <a href="{{ route('documents.edit', $document) }}" class="text-yellow-600 hover:text-yellow-900" title="Modifica">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                    @endcan
                                    <a href="{{ route('documents.pdf', $document) }}" class="text-purple-600 hover:text-purple-900" title="PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    @can('create documents')
                                        <a href="{{ route('documents.duplicate', $document) }}" class="text-green-600 hover:text-green-900" title="Duplica">
                                            <i class="fas fa-copy"></i>
                                        </a>
                                    @endcan
                                    @can('delete documents')
                                        @if($document->status === 'draft')
                                            <button type="button" 
                                                    onclick="confirmDelete('{{ $document->number }}', '{{ route('documents.destroy', $document) }}')"
                                                    class="text-red-600 hover:text-red-900" title="Elimina">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-file-alt text-4xl mb-4"></i>
                                <p>Nessun documento trovato</p>
                                @can('create documents')
                                    <div class="mt-4 space-x-3">
                                        <a href="{{ route('documents.create', ['type' => 'purchase']) }}" class="text-blue-600 hover:text-blue-800">
                                            Crea il primo acquisto
                                        </a>
                                        <span class="text-gray-400">o</span>
                                        <a href="{{ route('documents.create', ['type' => 'sale']) }}" class="text-blue-600 hover:text-blue-800">
                                            Crea la prima vendita
                                        </a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($documents->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $documents->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
function confirmDelete(documentNumber, deleteUrl) {
    if (confirm(`Sei sicuro di voler eliminare il documento "${documentNumber}"?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = deleteUrl;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
