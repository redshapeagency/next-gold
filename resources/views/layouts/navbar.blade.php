<!-- Top Navigation -->
<header class="bg-white shadow-lg">
  <div class="mx-auto px-6 sm:px-6 lg:px-8">
    <div class="flex justify-between h-16">
      <div class="flex items-center">
        <!-- Mobile menu button -->
        <button @click="sidebarOpen = !sidebarOpen" 
                class="lg:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-gold-500">
          <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>

        <!-- Breadcrumbs -->
        <nav class="ml-4 lg:ml-0" aria-label="Breadcrumb">
          <ol class="flex items-center space-x-4">
            @hasSection('breadcrumbs')
              @yield('breadcrumbs')
            @else
              <li>
                <div class="flex items-center">
                  <a href="{{ route('dashboard.index') }}" class="text-gray-400 hover:text-gray-500">
                    <svg class="flex-shrink-0 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    <span class="sr-only">Home</span>
                  </a>
                </div>
              </li>
            @endif
          </ol>
        </nav>
      </div>

      <div class="flex items-center space-x-4">
        <!-- Gold Price Display -->
        @if(isset($goldPrice) && $goldPrice)
        <div class="hidden md:flex items-center space-x-2 text-sm">
          <svg class="w-5 h-5 text-gold-500" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span class="text-gray-600">Oro:</span>
          <span class="font-semibold text-gold-600">{{ $goldPrice->formatted_bid }}/g</span>
        </div>
        @endif

        <!-- User dropdown -->
        <div class="relative" x-data="{ userMenuOpen: false }">
          <button @click="userMenuOpen = !userMenuOpen" 
                  class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gold-500">
            <div class="flex items-center space-x-3">
              <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gold-500 flex items-center justify-center">
                <span class="text-white font-medium text-sm">
                  {{ substr(auth()->user()->first_name, 0, 1) }}{{ substr(auth()->user()->last_name, 0, 1) }}
                </span>
              </div>
              <div class="hidden md:block text-left">
                <div class="text-sm font-medium text-gray-700">{{ auth()->user()->full_name }}</div>
                <div class="text-xs text-gray-500">{{ auth()->user()->getRoleNames()->first() }}</div>
              </div>
              <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
              </svg>
            </div>
          </button>

          <!-- User menu dropdown -->
          <div x-show="userMenuOpen" 
               x-transition:enter="transition ease-out duration-100"
               x-transition:enter-start="transform opacity-0 scale-95"
               x-transition:enter-end="transform opacity-100 scale-100"
               x-transition:leave="transition ease-in duration-75"
               x-transition:leave-start="transform opacity-100 scale-100"
               x-transition:leave-end="transform opacity-0 scale-95"
               @click.away="userMenuOpen = false"
               class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 z-50">
            <div class="py-1">
              <a href="{{ route('settings.profile') }}" 
                 class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Il mio profilo
              </a>
            </div>
            <div class="py-1">
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" 
                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                  </svg>
                  Logout
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</header>
