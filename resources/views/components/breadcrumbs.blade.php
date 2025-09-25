@props(['items' => []])
<ol class="breadcrumb float-sm-end">
    @foreach($items as $label => $url)
        @if($url)
            <li class="breadcrumb-item"><a href="{{ $url }}">{{ $label }}</a></li>
        @else
            <li class="breadcrumb-item active">{{ $label }}</li>
        @endif
    @endforeach
</ol>


