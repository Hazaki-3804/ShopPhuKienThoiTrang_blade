@props(['categories' => collect()])
<ul class="list-unstyled m-0">
    @foreach(($categories ?? collect()) as $cat)
        <li class="my-1"><a class="text-decoration-none" href="{{ route('shop.index', ['category' => $cat->slug]) }}">{{ $cat->name }}</a></li>
    @endforeach
</ul>


