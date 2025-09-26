@props(['product'])
<div class="card h-100 card-hover fade-up">
    <img src="{{ $product['image'] ?? 'https://picsum.photos/400/300?random=' . ($product['id'] ?? 1) }}" class="card-img-top" alt="{{ $product['name'] ?? 'Product' }}">
    <div class="card-body d-flex flex-column">
        <h6 class="fw-semibold mb-1"><a class="text-decoration-none" href="{{ route('shop.show', $product['id'] ?? 1) }}">{{ $product['name'] ?? 'Product Name' }}</a></h6>
        <div class="text-muted small mb-2">{{ $product['category'] ?? 'Accessory' }}</div>
        <div class="mt-auto d-flex justify-content-between align-items-center">
            <span class="fw-semibold">{{ $product['price'] ?? '299.000₫' }}</span>
            <a href="{{ route('shop.show', $product['id'] ?? 1) }}" class="btn btn-sm btn-outline-secondary">Xem</a>
        </div>
    </div>
    <!-- Comment: Card sản phẩm với hover lift + fade-up -->

</div>