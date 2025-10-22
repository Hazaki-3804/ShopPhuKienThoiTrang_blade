@extends('layouts.app')
@section('title', $product->name)

@section('content')
<div class="container py-4">
    @if(!empty($activeDiscount))
        @include('components.promo-banner', ['discount' => $activeDiscount])
    @endif
    <div class="row g-4">
        <!-- Cột ảnh + slider + đánh giá -->
        <div class="col-12 col-md-6">
            <x-breadcrumbs :items="[
                ['label' => 'Trang chủ', 'url' => route('home')],
                ['label' => 'Sản phẩm', 'url' => route('shop.index')],
                ['label' => $product->category->name ?? 'Danh mục', 'url' => route('shop.index', ['category' => $product->category->slug ?? ''])],
                ['label' => $product->name]
            ]" />
            <!-- Ảnh chính (không click) -->
            <div class="ratio ratio-1x1 border rounded-3 overflow-hidden product-main-box mb-3">
                <img id="mainImage"
                     src="{{ asset($product->product_images[0]->image_url ?? 'https://picsum.photos/800/800?random=' . $product->id) }}"
                     class="w-80 h-80 object-fit-cover"
                     alt="{{ $product->name }}">
            </div>

            <!-- Gallery ảnh nhỏ đơn giản -->
            <div class="d-flex gap-2 mt-2">
                @foreach($product->product_images as $image)
                    <img src="{{ $image->image_url }}" class="img-thumbnail" style="width:80px;cursor:pointer;" onclick="document.getElementById('mainImage').src='{{ $image->image_url }}'">
                @endforeach
            </div>



            <!-- Đánh giá -->
            <div class="mt-4" id="review">
                <h6 class="fw-semibold mb-2">Đánh giá</h6>
                <div class="mb-3">
                    @php
                        $visibleReviews = $product->reviews->where('is_hidden', false);
                    @endphp
                    @forelse($visibleReviews as $rv)
                    <div class="border rounded p-2 mb-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <strong>{{ $rv->user->name ?? $rv->user_name ?? 'Người dùng' }}</strong>
                            <span class="text-warning" aria-label="{{ $rv->rating }} sao" title="{{ $rv->rating }} sao">
                                @for($i = 0; $i < (int) $rv->rating; $i++)★@endfor
                            </span>
                        </div>
                        <div class="mt-1">{{ trim((string)$rv->comment) !== '' ? $rv->comment : '(Không có nhận xét)' }}</div>
                    </div>
                    @empty
                    <div class="text-muted small">Chưa có đánh giá</div>
                    @endforelse
                </div>
                @isset($canReview)
                @if($canReview)
                    @php $hasMyReview = isset($myReview) && $myReview; @endphp
                    @if(!$hasMyReview)
                    <form method="POST" action="{{ route('reviews.store', $product->id) }}" class="review-form">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                        <input type="hidden" name="rating" id="rating-input" value="" required>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Chất lượng sản phẩm</label>
                            <div class="star-rating">
                                <i class="bi bi-star-fill" data-rating="1"></i>
                                <i class="bi bi-star-fill" data-rating="2"></i>
                                <i class="bi bi-star-fill" data-rating="3"></i>
                                <i class="bi bi-star-fill" data-rating="4"></i>
                                <i class="bi bi-star-fill" data-rating="5"></i>
                                <span class="rating-text ms-2">Tuyệt vời</span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <textarea class="form-control" name="comment" rows="3" placeholder="Hãy chia sẻ nhận xét cho sản phẩm này bạn nhé!"></textarea>
                        </div>
                        
                        <div>
                            <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
                        </div>
                    </form>
                    @else
                    <div class="mb-2">
                        <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#editReviewForm">
                            Chỉnh sửa đánh giá
                        </button>
                    </div>
                    <div id="editReviewForm" class="collapse">
                        <form method="POST" action="{{ route('reviews.store', $product->id) }}" class="review-form">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                            <input type="hidden" name="rating" id="edit-rating-input" value="{{ $myReview->rating ?? 5 }}" required>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Chất lượng sản phẩm</label>
                                <div class="star-rating" data-current-rating="{{ $myReview->rating ?? 5 }}">
                                    <i class="bi bi-star-fill" data-rating="1"></i>
                                    <i class="bi bi-star-fill" data-rating="2"></i>
                                    <i class="bi bi-star-fill" data-rating="3"></i>
                                    <i class="bi bi-star-fill" data-rating="4"></i>
                                    <i class="bi bi-star-fill" data-rating="5"></i>
                                    <span class="rating-text ms-2">Tuyệt vời</span>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <textarea class="form-control" name="comment" rows="3" placeholder="Hãy chia sẻ nhận xét cho sản phẩm này bạn nhé!">{{ $myReview->comment }}</textarea>
                            </div>
                            
                            <div>
                                <button type="submit" class="btn btn-primary">Cập nhật đánh giá</button>
                            </div>
                        </form>
                    </div>
                    @endif
                @endif
                @endisset
            </div>
        </div>

        <!-- Cột thông tin sản phẩm -->
        <div class="col-12 col-md-6">
            <h4 class="fw-semibold">{{ $product->name }}</h4>
            <div class="text-muted small"><strong>Danh mục:</strong> {{ $product->category->name ?? 'N/A' }}</div>
            <div class="fs-5 fw-semibold mb-2">{{ number_format($product->price,0,',','.') }}₫</div>

            <!-- Khuyến mãi nổi bật -->
            <div class="promo-box mb-2">
                <span class="promo-label">Áp dụng mã khuyến mãi giảm 15k khi mua đơn hàng 250k</span>
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
                        <!-- Quantity and buttons section on new line -->
                        <div class="d-flex align-items-center gap-3">
                            <label for="qtyInput" style="margin-bottom:0;font-weight:500;">Số lượng:</label>
                            @include('components.quantity-selector', [
                                'id' => 'qtyInput',
                                'name' => 'qty',
                                'max' => $product->stock,
                                'value' => 1
                            ])
                            <form method="POST" action="{{ route('cart.add', $product->id) }}"
                                style="margin-bottom:0;"
                                id="addToCartForm"
                            >
                                @csrf
                                <input type="hidden" name="qty" value="1" id="addToCartQty">
                                <button class="btn btn-shopee  text-nowrap" {{ $product->stock < 1 ? 'disabled' : '' }}>
                                    <i class="bi bi-bag-plus me-1"></i> Thêm vào giỏ
                                </button>
                            </form>
                            <form method="POST" action="{{ route('cart.buynow', $product->id) }}"
                                style="margin-bottom:0;"
                                id="buyNowForm"
                            >
                                @csrf
                                <input type="hidden" name="qty" value="1" id="buyNowQty">
                                <button class="btn btn-shopee  text-nowrap" {{ $product->stock < 1 ? 'disabled' : '' }}>
                                    <i class="bi bi-lightning-charge"></i> Mua ngay
                                </button>
                            </form>
                        </div>
                        <!-- Bỏ hiển thị tổng tiền -->
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
                    <div class="col">Hỗ trợ ship tối đa 20k cho đơn hàng từ 300k nội thành Vĩnh Long</div>
                </div>
                <div class="row g-2 justify-content-center align-items-center">
                    <div class="col-auto"><i class="bi bi-truck"></i></div>
                    <div class="col">Hỗ trợ ship tối đa 30k cho đơn hàng từ 500k các khu vực khác</div>
                </div>
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

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<style>
    .product-desc-image {
        display: flex;
        justify-content: center;  /* căn giữa ngang */
        align-items: center;      /* căn giữa dọc */
        flex-direction: column;
        width: 100%;              /* giữ trong khung cha */
        text-align: center;
        margin-left: 50%;
    }

    .product-desc-image img {
        max-width: 1200px;   /* tăng kích thước tối đa */
        max-height: 800px;
        width: 1000px;         /* tự co theo tỉ lệ */
        height: 800px;
        object-fit: contain; /* giữ ảnh không méo */
        display: block;
        margin: 0 auto;      /* căn giữa chính xác */
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
    .product-action-row > * {
        margin-bottom: 0 !important;
    }
    .product-action-row label {
        margin-bottom: 4px;
        font-size: 1rem;
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

    /* Dropdown số lượng */
    #qtyInput {
        border-radius: 4px;
        border: 1px solid #dee2e6;
        box-shadow: none;
        padding: 3px 6px;
        font-size: 1rem;
        width: 20px;
        text-align: center;
        transition: border-color 0.2s;
    }
    #qtyInput:focus {
        border-color: #86b7fe;
        outline: none;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
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
        box-shadow: 0 1px 4px rgba(238,77,45,0.08);
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
<style>
    .service-info-box {
        background: #fff0f5;
        border: 1px solid #f8bbd0;
        box-shadow: 0 2px 8px rgba(248,187,208,0.08);
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
    .swiper { width: 100%; height: 100px; }
    .swiper-slide img { border: 2px solid transparent; }
    .swiper-slide img:hover { border: 2px solid #ee4d2d; }
    .modal-body img { max-height: 80vh; }
</style>
<style>
    .star-rating {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .star-rating i {
        font-size: 2rem;
        color: #e0e0e0;
        cursor: pointer;
        transition: color 0.2s ease, transform 0.2s ease;
    }

    .star-rating i:hover {
        transform: scale(1.1);
    }

    .rating-text {
        font-size: 1rem;
        font-weight: 500;
        color: #ffc107;
        min-width: 100px;
    }

    .review-form {
        background: #fff;
        padding: 1.5rem;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
    }

    .review-form .form-label {
        color: #333;
        margin-bottom: 0.5rem;
    }

    .review-form textarea {
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        resize: none;
    }

    .review-form textarea:focus {
        border-color: #ffc107;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    }

    .review-form .btn-primary {
        background-color: #ee4d2d;
        border-color: #ee4d2d;
        padding: 0.5rem 2rem;
    }

    .review-form .btn-primary:hover {
        background-color: #d73211;
        border-color: #d73211;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
  var swiper = new Swiper(".mySwiper", {
    slidesPerView: 5,
    spaceBetween: 10,
    navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" },
  });

  // Mảng ảnh
  var images = @json($product->product_images->pluck('image_url'));
  var currentIndex = 0;

  function showImage(index) {
    currentIndex = index;
    document.getElementById('modalImage').src = images[currentIndex];
  }

  function nextImage() {
    currentIndex = (currentIndex + 1) % images.length;
    document.getElementById('modalImage').src = images[currentIndex];
  }

  function prevImage() {
    currentIndex = (currentIndex - 1 + images.length) % images.length;
    document.getElementById('modalImage').src = images[currentIndex];
  }

  // Cập nhật tổng tiền khi thay đổi số lượng
  document.addEventListener('DOMContentLoaded', function() {
    const productPrice = {{ $product->price }};
    const qtyInput = document.getElementById('qtyInput');
    const totalPriceEl = document.getElementById('totalPrice');

    function updateTotalPrice(qty) {
      if (!qty) {
        qty = parseInt(qtyInput.value) || 1;
      }
      const total = productPrice * qty;
      if (totalPriceEl) {
        totalPriceEl.textContent = new Intl.NumberFormat('vi-VN').format(total) + '₫';
      }
      
      // Cập nhật giá trị cho các form
      const addToCartQty = document.getElementById('addToCartQty');
      const buyNowQty = document.getElementById('buyNowQty');
      if (addToCartQty) addToCartQty.value = qty;
      if (buyNowQty) buyNowQty.value = qty;
    }

    // Đăng ký callback cho quantity selector
    if (!window.qtyChangeCallbacks) {
      window.qtyChangeCallbacks = {};
    }
    window.qtyChangeCallbacks['qtyInput'] = updateTotalPrice;

    // Lắng nghe sự kiện thay đổi số lượng trực tiếp từ input
    if (qtyInput) {
      qtyInput.addEventListener('change', function() {
        updateTotalPrice();
      });
      qtyInput.addEventListener('input', function() {
        updateTotalPrice();
      });
    }

    // Cập nhật tổng tiền ban đầu
    updateTotalPrice();
    
    // Star Rating System
    const starRatings = document.querySelectorAll('.star-rating');
    
    starRatings.forEach(starRating => {
        const stars = starRating.querySelectorAll('i[data-rating]');
        const ratingText = starRating.querySelector('.rating-text');
        const form = starRating.closest('form');
        const ratingInput = form ? form.querySelector('input[name="rating"]') : null;
        
        // Get current rating if editing
        const currentRating = parseInt(starRating.dataset.currentRating) || 0;
        let selectedRating = currentRating;
        
        // Initialize stars based on current rating
        if (currentRating > 0) {
            updateStars(currentRating);
            updateText(currentRating);
            if (ratingInput) ratingInput.value = currentRating;
        }
        
        function updateStars(rating) {
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.style.color = '#ffc107';
                } else {
                    star.style.color = '#e0e0e0';
                }
            });
        }
        
        function updateText(rating) {
            const texts = ['', 'Tệ', 'Không hài lòng', 'Bình thường', 'Hài lòng', 'Tuyệt vời'];
            if (ratingText) {
                ratingText.textContent = texts[rating] || '';
            }
        }
        
        stars.forEach(star => {
            // Hover effect
            star.addEventListener('mouseenter', function() {
                const rating = parseInt(this.dataset.rating);
                updateStars(rating);
                updateText(rating);
            });
            
            // Click to select
            star.addEventListener('click', function() {
                selectedRating = parseInt(this.dataset.rating);
                if (ratingInput) {
                    ratingInput.value = selectedRating;
                }
                updateStars(selectedRating);
                updateText(selectedRating);
            });
        });
        
        // Reset to selected rating when mouse leaves
        starRating.addEventListener('mouseleave', function() {
            updateStars(selectedRating);
            updateText(selectedRating);
        });
    });
  });
</script>
@endpush
@endsection