@extends('layouts.app')

@section('title', 'Nuovo Articolo')

@section('header')
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Nuovo Articolo</h1>
            <p class="mt-2 text-sm text-gray-700">Aggiungi un nuovo prodotto all'inventario</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('items.index') }}" class="btn btn-outline-gray">
                <i class="fas fa-arrow-left mr-2"></i>
                Torna alla Lista
            </a>
        </div>
    </div>
@endsection

@section('content')
    <form action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-6">Informazioni Generali</h3>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <x-form.select 
                        name="category_id" 
                        label="Categoria" 
                        :value="old('category_id')" 
                        required>
                        <option value="">Seleziona una categoria</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </x-form.select>
                </div>
                
                <div>
                    <x-form.input 
                        name="name" 
                        label="Nome Articolo" 
                        :value="old('name')" 
                        placeholder="Es: Anello oro 18K con diamante"
                        required />
                </div>
                
                <div class="lg:col-span-2">
                    <x-form.textarea 
                        name="description" 
                        label="Descrizione" 
                        :value="old('description')" 
                        placeholder="Descrizione dettagliata dell'articolo..."
                        rows="3" />
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-6">Specifiche Tecniche</h3>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div>
                    <x-form.input 
                        type="number" 
                        name="weight" 
                        label="Peso (grammi)" 
                        :value="old('weight')" 
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                        required />
                </div>
                
                <div>
                    <x-form.select 
                        name="karat" 
                        label="Carati">
                        <option value="">Seleziona carati</option>
                        <option value="24" {{ old('karat') == '24' ? 'selected' : '' }}>24K</option>
                        <option value="22" {{ old('karat') == '22' ? 'selected' : '' }}>22K</option>
                        <option value="18" {{ old('karat') == '18' ? 'selected' : '' }}>18K</option>
                        <option value="14" {{ old('karat') == '14' ? 'selected' : '' }}>14K</option>
                        <option value="10" {{ old('karat') == '10' ? 'selected' : '' }}>10K</option>
                    </x-form.select>
                </div>
                
                <div>
                    <x-form.input 
                        type="number" 
                        name="price" 
                        label="Prezzo (€)" 
                        :value="old('price')" 
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                        required />
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-6">Foto e Note</h3>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Foto Articolo
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                        <div class="space-y-1 text-center">
                            <div class="mx-auto h-12 w-12 text-gray-400">
                                <i class="fas fa-image text-2xl"></i>
                            </div>
                            <div class="flex text-sm text-gray-600">
                                <label for="photo" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Carica una foto</span>
                                    <input id="photo" name="photo" type="file" class="sr-only" accept="image/*" onchange="previewImage(this)">
                                </label>
                                <p class="pl-1">o trascina qui</p>
                            </div>
                            <p class="text-xs text-gray-500">
                                PNG, JPG, GIF fino a 2MB
                            </p>
                        </div>
                    </div>
                    @error('photo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    
                    <!-- Image preview -->
                    <div id="imagePreview" class="mt-4 hidden">
                        <img id="preview" class="h-32 w-32 object-cover rounded-lg">
                    </div>
                </div>
                
                <div>
                    <x-form.textarea 
                        name="notes" 
                        label="Note" 
                        :value="old('notes')" 
                        placeholder="Note aggiuntive sull'articolo..."
                        rows="6" />
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-6">Stato</h3>
            
            <div>
                <x-form.select 
                    name="status" 
                    label="Stato Articolo" 
                    :value="old('status', 'active')" 
                    required>
                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Attivo</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inattivo</option>
                </x-form.select>
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('items.index') }}" class="btn btn-outline-gray">
                Annulla
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-2"></i>
                Salva Articolo
            </button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('preview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove('hidden');
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
