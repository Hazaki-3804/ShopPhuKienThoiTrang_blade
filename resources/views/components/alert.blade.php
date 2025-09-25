@props(['type' => 'info'])
@php($map = [
    'success' => 'alert alert-success',
    'danger' => 'alert alert-danger',
    'warning' => 'alert alert-warning',
    'info' => 'alert alert-info'
])
<div {{ $attributes->merge(['class' => $map[$type] ?? $map['info']]) }}>
    {{ $slot }}
</div>


