@props(['variant' => 'brand', 'type' => 'button'])
@php($classes = $variant === 'brand' ? 'btn btn-brand' : 'btn btn-outline-secondary')
<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>


