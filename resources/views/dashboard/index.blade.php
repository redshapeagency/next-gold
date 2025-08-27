@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumbs')
<li>
  <div class="flex items-center">
    <span class="text-gray-500">Dashboard</span>
  </div>
</li>
@endsection

@section('content')
<div class="space-y-6">
  <!-- Header -->
  <div class="md:flex md:items-center md:justify-between">
    <div class="flex-1 min-w-0">
      <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
        Dashboard
      </h2>
      <p class="mt-1 text-sm text-gray-500">
        Panoramica generale del tuo negozio di compro oro
      </p>
    </div>
    <div class="mt-4 flex md:mt-0 md:ml-4">
      <span class="text-sm text-gray-500">
        Ultimo aggiornamento: {{ now()->format('d/m/Y H:i') }}
      </span>
    </div>
  </div>

  <!-- KPI Cards -->
  <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
    <!-- Clienti -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
      <div class="p-5">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
            </svg>
          </div>
          <div class="ml-5 w-0 flex-1">
            <dl>
              <dt class="text-sm font-medium text-gray-500 truncate">Clienti Totali</dt>
              <dd class="text-lg font-medium text-gray-900">{{ $stats['clients_count'] }}</dd>
            </dl>
          </div>
        </div>
      </div>
      <div class="bg-gray-50 px-5 py-3">
        <div class="text-sm">
          <a href="{{ route('clients.index') }}" class="font-medium text-gold-700 hover:text-gold-900">
            Visualizza tutti
          </a>
        </div>
      </div>
    </div>

    <!-- Magazzino -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
      <div class="p-5">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
          </div>
          <div class="ml-5 w-0 flex-1">
            <dl>
              <dt class="text-sm font-medium text-gray-500 truncate">Prodotti Disponibili</dt>
              <dd class="text-lg font-medium text-gray-900">{{ $stats['items_in_stock'] }}</dd>
            </dl>
          </div>
        </div>
      </div>
      <div class="bg-gray-50 px-5 py-3">
        <div class="text-sm">
          <a href="{{ route('items.index') }}" class="font-medium text-gold-700 hover:text-gold-900">
            Vai al magazzino
          </a>
        </div>
      </div>
    </div>

    <!-- Acquisti Recenti -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
      <div class="p-5">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div class="ml-5 w-0 flex-1">
            <dl>
              <dt class="text-sm font-medium text-gray-500 truncate">Acquisti (30gg)</dt>
              <dd class="text-lg font-medium text-gray-900">{{ $stats['recent_purchases'] }}</dd>
            </dl>
          </div>
        </div>
      </div>
      <div class="bg-gray-50 px-5 py-3">
        <div class="text-sm">
          <a href="{{ route('documents.index', ['type' => 'purchase']) }}" class="font-medium text-green-700 hover:text-green-900">
            Visualizza acquisti
          </a>
        </div>
      </div>
    </div>

    <!-- Vendite Recenti -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
      <div class="p-5">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
            </svg>
          </div>
          <div class="ml-5 w-0 flex-1">
            <dl>
              <dt class="text-sm font-medium text-gray-500 truncate">Vendite (30gg)</dt>
              <dd class="text-lg font-medium text-gray-900">{{ $stats['recent_sales'] }}</dd>
            </dl>
          </div>
        </div>
      </div>
      <div class="bg-gray-50 px-5 py-3">
        <div class="text-sm">
          <a href="{{ route('documents.index', ['type' => 'sale']) }}" class="font-medium text-blue-700 hover:text-blue-900">
            Visualizza vendite
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Prezzo Oro -->
    @if($goldPrice)
    <div class="bg-white shadow rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
          Prezzo Oro Corrente
        </h3>
        <div class="grid grid-cols-2 gap-4">
          <div class="text-center p-4 bg-gold-50 rounded-lg">
            <div class="text-2xl font-bold text-gold-600">{{ $goldPrice->formatted_bid }}</div>
            <div class="text-sm text-gray-600">Acquisto (Bid)</div>
          </div>
          <div class="text-center p-4 bg-gold-50 rounded-lg">
            <div class="text-2xl font-bold text-gold-600">{{ $goldPrice->formatted_ask }}</div>
            <div class="text-sm text-gray-600">Vendita (Ask)</div>
          </div>
        </div>
        <div class="mt-3 text-xs text-gray-500 text-center">
          Aggiornato: {{ $goldPrice->fetched_at->format('d/m/Y H:i') }}
          · Provider: {{ ucfirst($goldPrice->provider) }}
        </div>
      </div>
    </div>
    @else
    <div class="bg-white shadow rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
          Prezzo Oro Corrente
        </h3>
        <div class="text-center py-8">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
          </svg>
          <p class="mt-2 text-sm text-gray-500">
            Prezzo oro non disponibile
          </p>
          <p class="text-xs text-gray-400">
            Configura le API nelle impostazioni
          </p>
        </div>
      </div>
    </div>
    @endif

    <!-- Grafico Entrate/Uscite -->
    <div class="bg-white shadow rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
          Entrate e Uscite (Ultimi 30 giorni)
        </h3>
        <div style="height: 300px;">
          <canvas id="revenueChart" width="400" height="200"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Ultimi Documenti -->
    <div class="bg-white shadow rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
          Ultimi Documenti
        </h3>
        @if($recentDocuments->count() > 0)
          <div class="space-y-3">
            @foreach($recentDocuments as $document)
            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
              <div class="flex-1">
                <div class="flex items-center space-x-2">
                  <span class="px-2 py-1 text-xs rounded-full 
                    @if($document->type === 'purchase') bg-green-100 text-green-800 @else bg-blue-100 text-blue-800 @endif">
                    {{ $document->type_label }}
                  </span>
                  <span class="text-sm font-medium">{{ $document->number }}</span>
                </div>
                <div class="text-sm text-gray-600">
                  {{ $document->client->full_name }} · {{ $document->date->format('d/m/Y') }}
                </div>
              </div>
              <div class="text-right">
                <div class="text-sm font-medium">{{ $document->formatted_total_gross }}</div>
              </div>
            </div>
            @endforeach
          </div>
        @else
          <div class="text-center py-6">
            <p class="text-sm text-gray-500">Nessun documento trovato</p>
          </div>
        @endif
      </div>
    </div>

    <!-- Ultimi Prodotti -->
    <div class="bg-white shadow rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
          Ultimi Prodotti Inseriti
        </h3>
        @if($recentItems->count() > 0)
          <div class="space-y-3">
            @foreach($recentItems as $item)
            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
              <div class="flex-1">
                <div class="text-sm font-medium">{{ $item->name }}</div>
                <div class="text-sm text-gray-600">
                  {{ $item->material_label }}
                  @if($item->karat) · {{ $item->karat }}kt @endif
                  · {{ $item->formatted_weight }}
                </div>
              </div>
              <div class="text-right">
                <div class="text-sm font-medium">{{ $item->formatted_price_sale }}</div>
              </div>
            </div>
            @endforeach
          </div>
        @else
          <div class="text-center py-6">
            <p class="text-sm text-gray-500">Nessun prodotto trovato</p>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Grafico Entrate/Uscite
const ctx = document.getElementById('revenueChart').getContext('2d');
const revenueData = @json($revenueData);

new Chart(ctx, {
  type: 'line',
  data: {
    labels: revenueData.map(item => {
      const date = new Date(item.date);
      return date.toLocaleDateString('it-IT', { day: '2-digit', month: '2-digit' });
    }),
    datasets: [{
      label: 'Vendite',
      data: revenueData.map(item => item.sales),
      borderColor: 'rgb(59, 130, 246)',
      backgroundColor: 'rgba(59, 130, 246, 0.1)',
      tension: 0.1
    }, {
      label: 'Acquisti',
      data: revenueData.map(item => item.purchases),
      borderColor: 'rgb(16, 185, 129)',
      backgroundColor: 'rgba(16, 185, 129, 0.1)',
      tension: 0.1
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'top',
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          callback: function(value) {
            return '€' + value.toLocaleString('it-IT');
          }
        }
      }
    }
  }
});
</script>
@endpush
@endsection
