@props(['type' => 'button', 'variant' => 'primary', 'size' => 'md'])

@php
$baseClasses = 'inline-flex items-center justify-center font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

$variantClasses = match($variant) {
    'primary' => 'bg-gold-600 hover:bg-gold-700 text-white focus:ring-gold-500',
    'secondary' => 'bg-gray-600 hover:bg-gray-700 text-white focus:ring-gray-500',
    'success' => 'bg-green-600 hover:bg-green-700 text-white focus:ring-green-500',
    'danger' => 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500',
    'warning' => 'bg-yellow-600 hover:bg-yellow-700 text-white focus:ring-yellow-500',
    'outline' => 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:ring-gold-500',
    default => 'bg-gold-600 hover:bg-gold-700 text-white focus:ring-gold-500',
};

$sizeClasses = match($size) {
    'sm' => 'px-3 py-2 text-sm',
    'md' => 'px-4 py-2 text-sm',
    'lg' => 'px-6 py-3 text-base',
    default => 'px-4 py-2 text-sm',
};

$classes = $baseClasses . ' ' . $variantClasses . ' ' . $sizeClasses;
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
  {{ $slot }}
</button>
