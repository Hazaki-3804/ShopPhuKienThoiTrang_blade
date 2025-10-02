@props(['items' => []])
@push('styles')
<link rel="stylesheet" href="{{ asset('css/breadcrumbs.css') }}">
@endpush
<ol class="breadcrumb fs-6">
    @foreach($items as $item)
    @if ($loop->last)
    @elseif ($loop->remaining === 1)
    <li class="breadcrumb-item">
        <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
    </li>
    @else
    <li class="breadcrumb-item active">
        <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
    </li>
    @endif
    @endforeach

</ol>