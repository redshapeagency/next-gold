@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Clienti</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['clients_count'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Prodotti in Magazzino</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['items_count'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Documenti</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['documents_count'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Archivio</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['archived_items_count'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gold Price Card -->
    @if($latestGoldQuote)
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Prezzo Oro Attuale</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ number_format($latestGoldQuote->bid, 2) }} €/g</div>
                    <div class="text-sm text-gray-500">Bid</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ number_format($latestGoldQuote->ask, 2) }} €/g</div>
                    <div class="text-sm text-gray-500">Ask</div>
                </div>
                <div class="text-center">
                    <div class="text-sm text-gray-500">Aggiornato</div>
                    <div class="text-lg font-medium">{{ $latestGoldQuote->fetched_at->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Documents -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Documenti Recenti</h3>
            </div>
            <ul role="list" class="divide-y divide-gray-200">
                @forelse($recentDocuments as $document)
                <li>
                    <a href="{{ route('documents.show', $document) }}" class="block hover:bg-gray-50">
                        <div class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <p class="text-sm font-medium text-indigo-600 truncate">{{ $document->number }}</p>
                                    <p class="ml-2 flex-shrink-0 flex">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $document->type === 'sale' ? 'green' : 'blue' }}-100 text-{{ $document->type === 'sale' ? 'green' : 'blue' }}-800">
                                            {{ ucfirst($document->type) }}
                                        </span>
                                    </p>
                                </div>
                                <div class="ml-2 flex-shrink-0 flex">
                                    <p class="text-sm text-gray-500">{{ $document->date->format('d/m/Y') }}</p>
                                </div>
                            </div>
                            <div class="mt-2 sm:flex sm:justify-between">
                                <div class="sm:flex">
                                    <p class="flex items-center text-sm text-gray-500">
                                        {{ $document->client->full_name }}
                                    </p>
                                </div>
                                <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                    <p class="text-sm font-medium text-gray-900">{{ number_format($document->total_net, 2) }} €</p>
                                </div>
                            </div>
                        </div>
                    </a>
                </li>
                @empty
                <li>
                    <div class="px-4 py-4 sm:px-6 text-center text-gray-500">
                        Nessun documento recente
                    </div>
                </li>
                @endforelse
            </ul>
        </div>

        <!-- Recent Items -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Prodotti Recenti</h3>
            </div>
            <ul role="list" class="divide-y divide-gray-200">
                @forelse($recentItems as $item)
                <li>
                    <a href="{{ route('items.show', $item) }}" class="block hover:bg-gray-50">
                        <div class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <p class="text-sm font-medium text-indigo-600 truncate">{{ $item->code }}</p>
                                    <p class="ml-2 flex-shrink-0 flex">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $item->status === 'in_stock' ? 'green' : 'gray' }}-100 text-{{ $item->status === 'in_stock' ? 'green' : 'gray' }}-800">
                                            {{ $item->status === 'in_stock' ? 'In Magazzino' : 'Archiviato' }}
                                        </span>
                                    </p>
                                </div>
                                <div class="ml-2 flex-shrink-0 flex">
                                    <p class="text-sm text-gray-500">{{ $item->created_at->format('d/m/Y') }}</p>
                                </div>
                            </div>
                            <div class="mt-2 sm:flex sm:justify-between">
                                <div class="sm:flex">
                                    <p class="flex items-center text-sm text-gray-500">
                                        {{ $item->name }}
                                    </p>
                                </div>
                                <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                    <p class="text-sm font-medium text-gray-900">{{ number_format($item->weight_grams, 2) }}g</p>
                                </div>
                            </div>
                        </div>
                    </a>
                </li>
                @empty
                <li>
                    <div class="px-4 py-4 sm:px-6 text-center text-gray-500">
                        Nessun prodotto recente
                    </div>
                </li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
