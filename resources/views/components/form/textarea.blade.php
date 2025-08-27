@props(['label', 'name', 'value' => '', 'required' => false, 'rows' => 3, 'placeholder' => ''])

<div class="mb-4">
  @if($label)
  <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">
    {{ $label }}
    @if($required)
      <span class="text-red-500">*</span>
    @endif
  </label>
  @endif
  
  <textarea id="{{ $name }}"
            name="{{ $name }}"
            rows="{{ $rows }}"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            {{ $attributes->merge(['class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-gold-500 focus:ring-gold-500 sm:text-sm']) }}>{{ old($name, $value) }}</textarea>
  
  @error($name)
  <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
  @enderror
</div>
