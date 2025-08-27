<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  <title>@yield('title', config('app.name', 'Next Gold'))</title>
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
  
  <!-- Scripts -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  
  @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-100">
  <div id="app" class="min-h-screen">
    @auth
      <div class="flex h-screen bg-gray-100">
        <!-- Sidebar -->
        @include('layouts.sidebar')
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
          <!-- Top Navigation -->
          @include('layouts.navbar')
          
          <!-- Page Content -->
          <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
            <div class="container mx-auto px-6 py-8">
              @if (session('success'))
                <x-alert type="success" :message="session('success')" />
              @endif
              
              @if (session('error'))
                <x-alert type="error" :message="session('error')" />
              @endif
              
              @if (session('warning'))
                <x-alert type="warning" :message="session('warning')" />
              @endif
              
              @yield('content')
            </div>
          </main>
        </div>
      </div>
    @else
      <!-- Guest Layout -->
      <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
          @yield('content')
        </div>
      </div>
    @endauth
  </div>
  
  @stack('scripts')
</body>
</html>
