@props(['categories' => collect()])
<ul class="list-unstyled m-0">
    <li class="my-1">
        <a class="text-decoration-none {{ request('category') ? '' : 'fw-bold text-dark' }}" href="{{ route('shop.index', request()->except('page', 'category', 'price_min', 'price_max')) }}">Tất cả sản phẩm</a>
    </li>
    @foreach(($categories ?? collect()) as $cat)
        <li class="my-1">
            <a class="text-decoration-none {{ request('category') === $cat->slug ? 'fw-bold text-dark' : '' }}" href="{{ route('shop.index', array_merge(request()->except('page', 'category', 'price_min', 'price_max'), ['category' => $cat->slug])) }}">{{ $cat->name }}</a>
        </li>
    @endforeach
    
</ul>


