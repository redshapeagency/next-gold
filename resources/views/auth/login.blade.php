@extends('layouts.app')

@section('title', 'Login - Next Gold')

@section('content')
<div class="text-center mb-6">
  <div class="flex justify-center mb-4">
    <div class="w-16 h-16 bg-gold-500 rounded-full flex items-center justify-center">
      <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
    </div>
  </div>
  <h2 class="text-2xl font-bold text-gray-900">Next Gold</h2>
  <p class="text-gray-600">Accedi al tuo account</p>
</div>

<form method="POST" action="{{ route('login') }}" class="space-y-4">
  @csrf
  
  <x-form.input 
    label="Email o Username" 
    name="login" 
    type="text"
    :value="old('login')"
    required 
    placeholder="inserisci email o username"
    autofocus />
    
  <x-form.input 
    label="Password" 
    name="password" 
    type="password"
    required />
    
  <div class="flex items-center justify-between">
    <div class="flex items-center">
      <input id="remember" name="remember" type="checkbox" 
             class="h-4 w-4 text-gold-600 focus:ring-gold-500 border-gray-300 rounded">
      <label for="remember" class="ml-2 block text-sm text-gray-900">
        Ricordami
      </label>
    </div>
  </div>
  
  <x-button type="submit" class="w-full">
    Accedi
  </x-button>
</form>
@endsection
