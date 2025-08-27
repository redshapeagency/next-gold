@extends('layouts.app')

@section('title', 'Setup Iniziale - Next Gold')

@section('content')
<div class="text-center mb-6">
  <div class="flex justify-center mb-4">
    <div class="w-16 h-16 bg-gold-500 rounded-full flex items-center justify-center">
      <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
    </div>
  </div>
  <h2 class="text-2xl font-bold text-gray-900">Benvenuto in Next Gold</h2>
  <p class="text-gray-600">Configura il tuo primo account amministratore</p>
</div>

<form method="POST" action="{{ route('setup.store') }}" class="space-y-4">
  @csrf
  
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-form.input 
      label="Nome" 
      name="first_name" 
      :value="old('first_name')"
      required 
      autofocus />
      
    <x-form.input 
      label="Cognome" 
      name="last_name" 
      :value="old('last_name')"
      required />
  </div>
  
  <x-form.input 
    label="Username" 
    name="username" 
    :value="old('username')"
    required 
    help="Sarà utilizzato per il login insieme all'email" />
    
  <x-form.input 
    label="Email" 
    name="email" 
    type="email"
    :value="old('email')"
    required />
    
  <x-form.input 
    label="Password" 
    name="password" 
    type="password"
    required 
    help="Minimo 8 caratteri" />
    
  <x-form.input 
    label="Conferma Password" 
    name="password_confirmation" 
    type="password"
    required />
  
  <x-button type="submit" class="w-full">
    Completa Setup
  </x-button>
</form>
@endsection
