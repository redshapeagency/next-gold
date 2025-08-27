@props(['label', 'name', 'options' => [], 'value' => '', 'required' => false, 'placeholder' => 'Seleziona...'])

<div class="mb-4">
  @if($label)
  <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">
    {{ $label }}
    @if($required)
      <span class="text-red-500">*</span>
    @endif
  </label>
  @endif
  
  <select id="{{ $name }}"
          name="{{ $name }}"
          @if($required) required @endif
          {{ $attributes->merge(['class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-gold-500 focus:ring-gold-500 sm:text-sm']) }}>
    @if($placeholder)
      <option value="">{{ $placeholder }}</option>
    @endif
    @foreach($options as $optionValue => $optionLabel)
      <option value="{{ $optionValue }}" @if(old($name, $value) == $optionValue) selected @endif>
        {{ $optionLabel }}
      </option>
    @endforeach
  </select>
  
  @error($name)
  <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
  @enderror
</div>
