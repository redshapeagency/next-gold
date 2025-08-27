@extends('layouts.app')

@section('title', 'Gestione Articoli')

@section('header')
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestione Articoli</h1>
            <p class="mt-2 text-sm text-gray-700">Gestisci l'inventario dei tuoi prodotti in oro</p>
        </div>
        @can('create items')
            <div class="mt-4 sm:mt-0">
                <a href="{{ route('items.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>
                    Nuovo Articolo
                </a>
            </div>
        @endcan
    </div>
@endsection

@section('content')
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <form method="GET" action="{{ route('items.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Cerca</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                       placeholder="Codice, nome, descrizione..." 
                       class="form-input">
            </div>
            
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">Categoria</label>
                <select name="category_id" id="category_id" class="form-select">
                    <option value="">Tutte le categorie</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Stato</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Tutti gli stati</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Attivo</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inattivo</option>
                    <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archiviato</option>
                </select>
            </div>
            
            <div class="flex items-end space-x-2">
                <button type="submit" class="btn btn-outline-primary flex-1">
                    <i class="fas fa-search mr-2"></i>
                    Filtra
                </button>
                <a href="{{ route('items.index') }}" class="btn btn-outline-gray">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'code', 'direction' => request('sort') === 'code' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                               class="hover:text-gray-700">
                                Codice
                                @if(request('sort') === 'code')
                                    <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Foto
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('sort') === 'name' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                               class="hover:text-gray-700">
                                Nome
                                @if(request('sort') === 'name')
                                    <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Categoria
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'weight', 'direction' => request('sort') === 'weight' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                               class="hover:text-gray-700">
                                Peso (g)
                                @if(request('sort') === 'weight')
                                    <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'price', 'direction' => request('sort') === 'price' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                               class="hover:text-gray-700">
                                Prezzo
                                @if(request('sort') === 'price')
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
                    @forelse($items as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                                {{ $item->code }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->photo)
                                    <img src="{{ Storage::url($item->photo) }}" alt="{{ $item->name }}" 
                                         class="h-10 w-10 rounded-lg object-cover">
                                @else
                                    <div class="h-10 w-10 rounded-lg bg-gray-200 flex items-center justify-center">
                                        <i class="fas fa-image text-gray-400"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $item->name }}</div>
                                @if($item->description)
                                    <div class="text-sm text-gray-500 truncate max-w-xs">
                                        {{ Str::limit($item->description, 50) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item->category->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($item->weight, 2) }}
                                @if($item->karat)
                                    <span class="text-xs text-gray-500">({{ $item->karat }}K)</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                €{{ number_format($item->price, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Attivo
                                    </span>
                                @elseif($item->status === 'inactive')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Inattivo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Archiviato
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('items.show', $item) }}" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('edit items')
                                        <a href="{{ route('items.edit', $item) }}" class="text-yellow-600 hover:text-yellow-900">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('delete items')
                                        <button type="button" 
                                                onclick="confirmDelete('{{ $item->name }}', '{{ route('items.destroy', $item) }}')"
                                                class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-box-open text-4xl mb-4"></i>
                                <p>Nessun articolo trovato</p>
                                @can('create items')
                                    <p class="mt-2">
                                        <a href="{{ route('items.create') }}" class="text-blue-600 hover:text-blue-800">
                                            Crea il primo articolo
                                        </a>
                                    </p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $items->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
function confirmDelete(itemName, deleteUrl) {
    if (confirm(`Sei sicuro di voler eliminare l'articolo "${itemName}"?`)) {
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
