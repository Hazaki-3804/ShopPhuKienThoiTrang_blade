@props(['items' => []])
@push('styles')
<style>
    .breadcrumb-item+.breadcrumb-item::before {
        content: "››" !important;
        font-size: 1.25rem !important;
        margin-top: -0.25rem !important;
    }

    .breadcrumb {
        margin-left: auto !important;
        background-color: transparent !important;
        margin: 0 .25rem !important;
        padding-right: 0 !important;
    }
</style>
@endpush
<ol class="breadcrumb fs-5">
    @foreach($items as $item)
    @if($loop->last)
    <li class="breadcrumb-item active d-none d-md-inline">{{ $item['name'] }}</li>
    @else
    <li class="breadcrumb-item d-none d-md-inline">{{ $item['name'] }}</li>
    @endif
    @endforeach
</ol>