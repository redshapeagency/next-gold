@props(['label', 'name', 'type' => 'text', 'value' => '', 'required' => false, 'placeholder' => '', 'help' => ''])

<div class="mb-4">
  @if($label)
  <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">
    {{ $label }}
    @if($required)
      <span class="text-red-500">*</span>
    @endif
  </label>
  @endif
  
  <input type="{{ $type }}"
         id="{{ $name }}"
         name="{{ $name }}"
         value="{{ old($name, $value) }}"
         placeholder="{{ $placeholder }}"
         @if($required) required @endif
         {{ $attributes->merge(['class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-gold-500 focus:ring-gold-500 sm:text-sm']) }}>
  
  @if($help)
  <p class="mt-1 text-xs text-gray-500">{{ $help }}</p>
  @endif
  
  @error($name)
  <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
  @enderror
</div>
