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
<a href="{{ $pid ? route('shop.show', $pid) : route('shop.index') }}">
    <div class="card h-100 card-hover fade-up">
        <div class="product-thumb-wrap">
            <img src="{{ $imageUrl }}" class="product-thumb img-zoom" alt="{{ $pname }}" loading="lazy">
        </div>
        <div class="card-body d-flex flex-column">
            <h6 class="fw-semibold mb-1">
                <a class="text-decoration-none product-title-link" href="{{ $pid ? route('shop.show', $pid) : route('shop.index') }}">{{ $pname }}</a>
            </h6>
            @if($pcategory)
            <div class="text-muted small mb-2"><strong>Danh mục:</strong> {{ $pcategory }}</div>
            @endif
            <div class="mt-auto d-flex justify-content-between align-items-center">
                <span class="fw-semibold">{{ $pprice ?? '299.000₫' }}</span>
                <a href="{{ $pid ? route('shop.show', $pid) : route('shop.index') }}" class="btn btn-sm btn-outline-shopee">Xem</a>
            </div>
        </div>
        <!-- Comment: Card sản phẩm với hover lift + fade-up -->
    </div>
</a>