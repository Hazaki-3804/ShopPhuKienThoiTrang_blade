@props(['categories' => collect()])
@php($currentSlug = request()->string('category')->toString())
<ul class="list-unstyled m-0">
    <li class="my-1">
        <a class="text-decoration-none {{ request('category') ? '' : 'fw-bold text-dark' }}" href="{{ route('shop.index', request()->except('page', 'category', 'price_min', 'price_max')) }}">Tất cả sản phẩm</a>
    </li>
    @foreach(($categories ?? collect()) as $cat)
        @php($active = $currentSlug === ($cat->slug ?? ''))
        <li class="my-1">
            <a class="text-decoration-none category-link {{ $active ? 'active' : '' }}" href="{{ route('shop.index', ['category' => $cat->slug]) }}">{{ $cat->name }}</a>
        </li>
    @endforeach
    <style>
        .category-link { color: #2c2c2c; transition: color .2s ease; }
        .category-link:hover, .category-link.active { color: var(--accent); text-decoration: underline; }
    </style>
</ul>


