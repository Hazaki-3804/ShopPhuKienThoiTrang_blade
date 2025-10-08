@extends('layouts.app')
@section('title', $product->name)

@section('content')
<div class="container my-4">
    <div class="row g-4">
        <!-- Cột ảnh + slider + đánh giá -->
        <div class="col-12 col-md-6">
            <!-- Ảnh chính (không click) -->
            <div class="ratio ratio-1x1 border rounded-3 overflow-hidden mb-3" style="height: 500px;">
                <img id="mainImage"
                    src="{{ asset($product->product_images[0]->image_url ?? 'https://picsum.photos/800/800?random=' . $product->id) }}"
                    class="w-80 h-80 object-fit-cover"
                    alt="{{ $product->name }}">
            </div>

            <!-- Gallery ảnh nhỏ đơn giản -->
            <div class="d-flex gap-2 mt-2">
                @foreach($product->product_images as $index => $image)
                <img src="{{ asset($image->image_url) }}" class="img-thumbnail" style="width:80px;cursor:pointer;" onclick="showImage({{ $index }})" alt="Thumbnail {{ $index + 1 }}">
                @endforeach
            </div>

            <!-- Đánh giá -->
            <div class="mt-4">
                <h6 class="fw-semibold mb-2">Đánh giá</h6>
                <div class="mb-3">
                    @forelse($product->reviews as $rv)
                    <div class="border rounded p-2 mb-2">
                        <div class="d-flex justify-content-between">
                            <strong>{{ $rv->user_name }}</strong>
                            <span class="text-warning">
                                @for($i=0;$i<$rv->rating;$i++)★@endfor
                            </span>
                        </div>
                        <div class="small text-muted">{{ $rv->created_at->format('d/m/Y H:i') }}</div>
                        <div>{{ $rv->comment }}</div>
                    </div>
                    @empty
                    <div class="text-muted small">Chưa có đánh giá</div>
                    @endforelse
                </div>
                @isset($canReview)
                @if($canReview)
                <form method="POST" action="{{ route('reviews.store', $product->id) }}" class="row g-2">
                    @csrf
                    <div class="col-12 col-md-6">
                        <input class="form-control hidden" name="user_id" value="{{ auth()->user()->id }}" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <input class="form-control" name="user_name" value="{{ auth()->user()->name }}" placeholder="Tên" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <input class="form-control" type="email" name="user_email" value="{{ auth()->user()->email }}" placeholder="Email" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <select class="form-select" name="rating" required>
                            <option value="">Rating</option>
                            @foreach(range(1,5) as $r)
                            <option value="{{ $r }}">{{ $r }} ★</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-8">
                        <input class="form-control" name="comment" placeholder="Nhận xét (tuỳ chọn)">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-outline-secondary">Gửi đánh giá</button>
                    </div>
                </form>
                @endif
                @endisset
            </div>
        </div>

        <!-- Cột thông tin sản phẩm -->
        <div class="col-12 col-md-6">
            <h4 class="fw-semibold">{{ $product->name }}</h4>
            <div class="text-muted small">Danh mục: {{ $product->category->name ?? 'N/A' }}</div>
            <div class="fs-5 fw-semibold mb-2">{{ number_format($product->price,0,',','.') }}₫</div>

            <!-- Khuyến mãi nổi bật -->
            <div class="promo-box mb-2">
                <span class="promo-label">Áp dụng mã khuyến mãi giảm 15k khi mua đơn hàng 150k</span>
                <span class="promo-label">Mua để nhận quà</span>
            </div>
            @if($product->stock == 1)
            <div class="alert alert-warning py-2 px-3 mt-2 mb-0" style="font-size:1rem;">Sản phẩm này chỉ còn 1 cái!</div>
            @elseif($product->stock == 0)
            <div class="alert alert-danger py-2 px-3 mt-2 mb-0" style="font-size:1rem;">Mặt hàng đã hết!</div>
            @endif
            <div class="product-desc-title mb-2">
                <span class="desc-icon"><i class="bi bi-file-earmark-text"></i></span>
                <span class="desc-text">Mô Tả Sản Phẩm</span>
            </div>
            <p class="text-muted">{!! nl2br(e($product->description)) !!}</p>
            <div class="d-flex gap-2 align-items-center mb-3">
                @php
                $totalPrice = $product->price * 1; // Số lượng mặc định là 1, có thể thay bằng biến qty nếu có
                @endphp
                <div class="mb-3 w-100">
                    <div class="d-flex align-items-center gap-2" style="width:100%;">
                        <label for="voucherSelect" class="fw-semibold mb-1" style="white-space:nowrap;">Voucher giảm giá:</label>
                        <select id="voucherSelect" name="voucher" class="form-select" style="max-width:320px;" {{ $totalPrice < 150000 ? 'disabled' : '' }}>
                            <option value="">-- Không áp dụng --</option>
                            <option value="Giam15k">Giảm 15k cho đơn từ 150k</option>
                        </select>
                    </div>
                    @if($totalPrice < 150000)
                        <div class="text-danger small mt-1">Đơn hàng phải từ 150.000đ mới được chọn mã giảm giá!
                </div>
                @endif
            </div>

            <form class="d-flex gap-2 align-items-center" method="POST" action="{{ route('cart.add', $product->id) }}">
                @csrf
                <input type="hidden" name="voucher" id="voucherInput">
            </form>
            <script>
                document.getElementById('voucherSelect').addEventListener('change', function() {
                    document.getElementById('voucherInput').value = this.value;
                });
            </script>
        </div>
        <div class="d-flex flex-column gap-2 align-items-start">
            <!-- qty chung -->
            <div class="d-flex gap-2 align-items-center mb-2">
                <label for="qtyInput">Số lượng:</label>
                @include('components.quantity-selector', [
                'id' => 'qtyInput',
                'name' => 'qty',
                'max' => $product->stock,
                'value' => 1
                ])
            </div>

            <!-- nút hành động -->
            <div class="d-flex gap-3 mb-2">
                <form method="POST" action="{{ route('cart.add', $product->id) }}"
                    onsubmit="this.querySelector('input[name=qty]').value=document.getElementById('qtyInput').value;">
                    @csrf
                    <input type="hidden" name="qty" value="1">
                    <button class="btn btn-shopee btn-shopee-lg text-nowrap">
                        <i class="bi bi-bag-plus me-1"></i> Thêm vào giỏ
                    </button>
                </form>

                <form method="POST" action="{{ route('cart.buynow', $product->id) }}"
                    onsubmit="this.querySelector('input[name=qty]').value=document.getElementById('qtyInput').value;">
                    @csrf
                    <input type="hidden" name="qty" value="1">
                    <button class="btn btn-shopee btn-shopee-lg text-nowrap">
                        <i class="bi bi-lightning-charge"></i> Mua ngay
                    </button>
                </form>
            </div>
        </div>

        <!-- Khối dịch vụ giao hàng, đổi trả -->
        <div class="service-info-box mt-2 p-3 rounded text-center">
            <div class="row g-2 justify-content-center align-items-center">
                <div class="col-auto"><i class="bi bi-truck"></i></div>
                <div class="col">Giao hàng toàn quốc đơn hàng từ 99k</div>
                <div class="col-auto"><i class="bi bi-cash-coin"></i></div>
                <div class="col">COD nội thành Vĩnh Long</div>
                <div class="col-auto"><i class="bi bi-arrow-repeat"></i></div>
                <div class="col">Đổi trả trong 24h</div>
            </div>
            <hr>
            <div class="row g-2 justify-content-center align-items-center">
                <div class="col-auto"><i class="bi bi-truck"></i></div>
                <div class="col">Hỗ trợ ship 20k cho đơn hàng từ 300k nội thành Vĩnh Long</div>
            </div>
            <div class="row g-2 justify-content-center align-items-center">
                <div class="col-auto"><i class="bi bi-truck"></i></div>
                <div class="col">Hỗ trợ ship 30k cho đơn hàng từ 500k các khu vực khác</div>
            </div>
        </div>
    </div>
</div>
<!-- Modal phóng to ảnh (nhiều ảnh, có next/prev) -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body p-0 text-center position-relative">
                <img id="modalImage" src="" class="img-fluid rounded">
                <!-- Nút đóng -->
                <button type="button" class="btn-close position-absolute top-0 end-0 m-2 bg-white rounded-circle" data-bs-dismiss="modal" aria-label="Close"></button>
                <!-- Nút prev/next -->
                <button type="button" class="btn position-absolute top-50 start-0 translate-middle-y text-white fs-2" onclick="prevImage()">‹</button>
                <button type="button" class="btn position-absolute top-50 end-0 translate-middle-y text-white fs-2" onclick="nextImage()">›</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('styles')
<style>
    .product-desc-image {
        display: flex;
        justify-content: center;
        /* căn giữa ngang */
        align-items: center;
        /* căn giữa dọc */
        flex-direction: column;
        width: 100%;
        /* giữ trong khung cha */
        text-align: center;
        margin-left: 50%;
    }

    .product-desc-image img {
        max-width: 1200px;
        /* tăng kích thước tối đa */
        max-height: 800px;
        width: 1000px;
        /* tự co theo tỉ lệ */
        height: 800px;
        object-fit: contain;
        /* giữ ảnh không méo */
        display: block;
        margin: 0 auto;
        /* căn giữa chính xác */
    }

    /* Căn chỉnh hàng voucher, số lượng, nút giỏ, mua ngay */
    .product-action-row {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 16px;
        margin-bottom: 0;
        flex-wrap: wrap;
    }

    .product-action-row>* {
        margin-bottom: 0 !important;
    }

    .product-action-row label {
        margin-bottom: 4px;
        font-size: 1rem;
    }

    #voucherSelect {
        border-radius: 8px;
        border: 1px solid #ff7f50;
        box-shadow: none;
        padding: 6px 12px;
        font-size: 1.05rem;
        width: 320px;
        min-width: 250px;
        transition: border-color 0.2s;
        margin-bottom: 0;
    }

    #voucherSelect:focus {
        border-color: #ff4500;
        outline: none;
    }

    #voucherSelect option[disabled] {
        color: #ccc;
    }

    /* Số lượng kiểu liền khối, giống hình */

    .quantity-selector {
        display: inline-flex;
        align-items: center;
        border: 1.5px solid #d9d9d9;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
        height: 40px;
    }

    .quantity-selector button {
        border: none;
        background: #fff;
        width: 40px;
        height: 40px;
        font-size: 1.3rem;
        color: #555;
        cursor: pointer;
        transition: background 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0;
    }

    .quantity-selector button:active {
        background: #f5f5f5;
    }

    .quantity-selector button:disabled {
        color: #ccc;
        cursor: not-allowed;
    }

    .quantity-selector input[type="number"] {
        border: none;
        width: 46px;
        height: 40px;
        text-align: center;
        font-size: 1.1rem;
        box-shadow: none;
        outline: none;
        background: #fff;
        padding: 0;
        margin: 0;
        border-radius: 0;
    }

    .btn-outline-shopee {
        border-radius: 8px;
        border: 1.5px solid #ff7f50;
        color: #ff7f50;
        background: #fff;
        transition: background 0.2s, color 0.2s;
        height: 40px;
        min-width: 120px;
        margin-bottom: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-outline-shopee:hover {
        background: #ff7f50;
        color: #fff;
    }

    /* Dropdown số lượng */
    #qtyInput {
        border-radius: 4px;
        border: 1px solid #ff7f50;
        box-shadow: none;
        padding: 3px 6px;
        font-size: 1rem;
        width: 20px;
        text-align: center;
        transition: border-color 0.2s;
    }

    #qtyInput:focus {
        border-color: #ff4500;
        outline: none;
    }
    .promo-box {
        display: flex;
        gap: 8px;
        margin-bottom: 8px;
        flex-wrap: wrap;
    }

    .promo-label {
        background: #ffe4e1;
        color: #ee4d2d;
        font-weight: 600;
        border-radius: 6px;
        padding: 4px 12px;
        font-size: 1rem;
        box-shadow: 0 1px 4px rgba(238, 77, 45, 0.08);
        border: 1px solid #f8bbd0;
        letter-spacing: 0.2px;
        transition: background 0.2s;
    }

    .promo-label:hover {
        background: #ffd6d6;
    }
</style>
<style>
    .product-desc-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 1.15rem;
        font-weight: 700;
        color: #ee4d2d;
        margin-bottom: 0.5rem;
        letter-spacing: 0.5px;
    }

    .product-desc-title .desc-icon {
        font-size: 1.3em;
        color: #ee4d2d;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .product-desc-title .desc-text {
        text-transform: uppercase;
        font-family: 'Segoe UI', Arial, sans-serif;
    }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<style>
    .service-info-box {
        background: #fff0f5;
        border: 1px solid #f8bbd0;
        box-shadow: 0 2px 8px rgba(248, 187, 208, 0.08);
        font-size: 1rem;
        margin-top: 8px;
    }

    .service-info-box i {
        color: #ee4d2d;
        font-size: 1.3em;
        vertical-align: middle;
    }

    .service-info-box hr {
        margin: 0.5rem 0;
        border-color: #f8bbd0;
    }

    .swiper {
        width: 100%;
        height: 100px;
    }

    .swiper-slide img {
        border: 2px solid transparent;
    }

    .swiper-slide img:hover {
        border: 2px solid #ee4d2d;
    }

    .modal-body img {
        max-height: 80vh;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    var swiper = new Swiper(".mySwiper", {
        slidesPerView: 5,
        spaceBetween: 10,
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev"
        },
    });

    // Mảng ảnh từ PHP
    var productImages = [
        @foreach($product -> product_images as $index => $image)
        '{{ asset($image->image_url) }}'
        @if(!$loop -> last), @endif
        @endforeach
    ];
    var currentIndex = 0;

    function showImage(index) {
        currentIndex = index;
        // Cập nhật ảnh chính
        document.getElementById('mainImage').src = productImages[currentIndex];
        // Nếu có modal, cũng cập nhật ảnh modal
        var modalImage = document.getElementById('modalImage');
        if (modalImage) {
            modalImage.src = productImages[currentIndex];
        }
    }

    function nextImage() {
        currentIndex = (currentIndex + 1) % productImages.length;
        document.getElementById('modalImage').src = productImages[currentIndex];
    }

    function prevImage() {
        currentIndex = (currentIndex - 1 + productImages.length) % productImages.length;
        document.getElementById('modalImage').src = productImages[currentIndex];
    }
</script>
@endpush