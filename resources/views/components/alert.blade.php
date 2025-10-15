@props(['type' => 'info'])

@php
    $colorClass = [
        'success' => 'alert-success',
        'danger' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info',
    ][$type] ?? 'alert-info';
@endphp

<div {{ $attributes->merge([
    'class' => "alert $colorClass alert-dismissible fade show position-relative",
    'role' => 'alert',
]) }}>
    {{ $slot }}
    <button 
        type="button" 
        class="btn-close position-absolute end-0 top-50 translate-middle-y" 
        data-bs-dismiss="alert" 
        aria-label="Close">
    </button>
</div>
