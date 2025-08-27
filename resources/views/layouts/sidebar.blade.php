<!-- Sidebar -->
<div x-data="{ sidebarOpen: false }" class="flex">
  <!-- Mobile sidebar overlay -->
  <div x-show="sidebarOpen" 
       x-transition:enter="transition-opacity ease-linear duration-300"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition-opacity ease-linear duration-300"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0"
       class="fixed inset-0 z-40 lg:hidden">
    <div class="fixed inset-0 bg-gray-600 bg-opacity-75" @click="sidebarOpen = false"></div>
  </div>

  <!-- Sidebar -->
  <div :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0">
    
    <div class="flex items-center justify-center h-16 bg-gold-600 text-white">
      <div class="flex items-center">
        <svg class="w-8 h-8 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="text-xl font-semibold">Next Gold</span>
      </div>
    </div>

    <nav class="mt-5">
      <div class="px-2 space-y-1">
        <!-- Dashboard -->
        <a href="{{ route('dashboard.index') }}" 
           class="@if(request()->routeIs('dashboard.*')) bg-gold-100 text-gold-900 @else text-gray-700 hover:bg-gray-100 @endif group flex items-center px-2 py-2 text-sm font-medium rounded-md">
          <svg class="@if(request()->routeIs('dashboard.*')) text-gold-500 @else text-gray-400 group-hover:text-gray-500 @endif mr-3 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6a2 2 0 01-2 2H10a2 2 0 01-2-2V5z"/>
          </svg>
          Dashboard
        </a>

        <!-- Clienti -->
        <a href="{{ route('clients.index') }}" 
           class="@if(request()->routeIs('clients.*')) bg-gold-100 text-gold-900 @else text-gray-700 hover:bg-gray-100 @endif group flex items-center px-2 py-2 text-sm font-medium rounded-md">
          <svg class="@if(request()->routeIs('clients.*')) text-gold-500 @else text-gray-400 group-hover:text-gray-500 @endif mr-3 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
          </svg>
          Clienti
        </a>

        <!-- Magazzino -->
        <a href="{{ route('items.index') }}" 
           class="@if(request()->routeIs('items.*')) bg-gold-100 text-gold-900 @else text-gray-700 hover:bg-gray-100 @endif group flex items-center px-2 py-2 text-sm font-medium rounded-md">
          <svg class="@if(request()->routeIs('items.*')) text-gold-500 @else text-gray-400 group-hover:text-gray-500 @endif mr-3 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
          </svg>
          Magazzino
        </a>

        <!-- Documenti -->
        <a href="{{ route('documents.index') }}" 
           class="@if(request()->routeIs('documents.*')) bg-gold-100 text-gold-900 @else text-gray-700 hover:bg-gray-100 @endif group flex items-center px-2 py-2 text-sm font-medium rounded-md">
          <svg class="@if(request()->routeIs('documents.*')) text-gold-500 @else text-gray-400 group-hover:text-gray-500 @endif mr-3 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          Documenti
        </a>

        <!-- Archivio -->
        <a href="{{ route('archive.index') }}" 
           class="@if(request()->routeIs('archive.*')) bg-gold-100 text-gold-900 @else text-gray-700 hover:bg-gray-100 @endif group flex items-center px-2 py-2 text-sm font-medium rounded-md">
          <svg class="@if(request()->routeIs('archive.*')) text-gold-500 @else text-gray-400 group-hover:text-gray-500 @endif mr-3 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
          </svg>
          Archivio
        </a>

        @can('access settings')
        <!-- Impostazioni -->
        <div class="mt-8">
          <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
            Amministrazione
          </h3>
          <div class="mt-2 space-y-1">
            <a href="{{ route('settings.index') }}" 
               class="@if(request()->routeIs('settings.*')) bg-gold-100 text-gold-900 @else text-gray-700 hover:bg-gray-100 @endif group flex items-center px-2 py-2 text-sm font-medium rounded-md">
              <svg class="@if(request()->routeIs('settings.*')) text-gold-500 @else text-gray-400 group-hover:text-gray-500 @endif mr-3 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              Impostazioni
            </a>
          </div>
        </div>
        @endcan
      </div>
    </nav>
  </div>
</div>
