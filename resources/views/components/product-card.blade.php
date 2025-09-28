@props(['product'])

<a href="{{ route('shop.show', $product['id'] ?? 1) }}" class="text-decoration-none text-dark">
    <div class="card h-100 card-hover fade-up">
        <img src="{{ $product['image'] ?? 'https://picsum.photos/400/300?random=' . ($product['id'] ?? 1) }}"
            class="card-img-top"
            alt="{{ $product['name'] ?? 'Product' }}">
        <div class="card-body d-flex flex-column">
            <h6 class="fw-semibold mb-1">{{ $product['name'] ?? 'Product Name' }}</h6>
            <div class="text-muted small mb-2">{{ $product['category'] ?? 'Accessory' }}</div>
            <div class="mt-auto d-flex justify-content-between align-items-center">
                <span class="fw-semibold">{{ $product['price'] ?? '299.000₫' }}</span>
                <span class="btn btn-sm btn-outline-secondary">Xem</span>
            </div>
        </div>
    </div>
</a>