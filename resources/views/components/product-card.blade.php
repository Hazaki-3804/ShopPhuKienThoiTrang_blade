@props(['product'])
@php
    // Chuẩn hóa dữ liệu cho cả mảng demo và Eloquent model
    $pid = is_array($product) ? ($product['id'] ?? 0) : ($product->id ?? 0);
    $pname = is_array($product) ? ($product['name'] ?? 'Product') : ($product->name ?? 'Product');
    $pcategory = is_array($product) ? ($product['category'] ?? null) : (optional($product->category)->name ?? null);
    $pprice = is_array($product) ? ($product['price'] ?? null) : ($product->price ?? null);

    // Ảnh: ưu tiên image từ mảng, nếu không lấy hình đầu tiên trong quan hệ product_images
    $firstImage = null;
    if (is_array($product)) {
        $firstImage = $product['image'] ?? null;
    } else {
        $firstImage = optional($product->product_images->first())->image_url ?? null;
    }

    $imageUrl = $firstImage ?: ('https://picsum.photos/600/600?random=' . $pid);
    if ($firstImage && !\Illuminate\Support\Str::startsWith($firstImage, ['http://','https://','/'])) {
        // Nếu là đường dẫn tương đối, thêm asset()
        $imageUrl = asset($firstImage);
    }

    // Định dạng giá nếu là số
    if (is_numeric($pprice)) {
        $pprice = number_format($pprice, 0, ',', '.') . '₫';
    }
@endphp

<div class="card h-100 card-hover fade-up">
    <div class="product-thumb-wrap">
        <img src="{{ $imageUrl }}" class="product-thumb" alt="{{ $pname }}" loading="lazy">
    </div>
    <div class="card-body d-flex flex-column">
        <h6 class="fw-semibold mb-1">
            <a class="text-decoration-none product-title-link" href="{{ $pid ? route('shop.show', $pid) : route('shop.index') }}">{{ $pname }}</a>
        </h6>
        @if($pcategory)
        <div class="text-muted small mb-2">{{ $pcategory }}</div>
        @endif
        <div class="mt-auto d-flex justify-content-between align-items-center">
            <span class="fw-semibold">{{ $pprice ?? '299.000₫' }}</span>
            <a href="{{ $pid ? route('shop.show', $pid) : route('shop.index') }}" class="btn btn-sm btn-outline-shopee">Xem</a>
        </div>
    </div>
    <!-- Comment: Card sản phẩm với hover lift + fade-up -->

    <style>
        .product-thumb-wrap { height: 220px; background: #fff; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .product-thumb { max-height: 100%; max-width: 100%; object-fit: contain; }
        @media (min-width: 992px) { .product-thumb-wrap { height: 260px; } }
        /* Title color black inside product card */
        .product-title-link { color: #2c2c2c; }
        .product-title-link:hover { color: #1f1f1f; text-decoration: underline; }
    </style>
</div>