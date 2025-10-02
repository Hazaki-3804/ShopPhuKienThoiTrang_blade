@extends('layouts.app')
@section('title', $product->name)

@section('content')
<div class="container mt-4">
    <div class="row g-4">
        <div class="col-12 col-md-6">
            <x-breadcrumbs :items="[
                ['label' => 'Trang chủ', 'url' => route('home')],
                ['label' => 'Sản phẩm', 'url' => route('shop.index')],
                ['label' => $product->category->name ?? 'Danh mục', 'url' => route('shop.index', ['category' => $product->category->slug ?? ''])],
                ['label' => $product->name]
            ]" />
            <div class="ratio ratio-1x1 border rounded-3 overflow-hidden product-main-box">
                <img src="{{ $product->product_images[0]->image_url ?? 'https://picsum.photos/800/800?random=' . $product->id }}" class="w-100 h-100 object-fit-cover" alt="{{ $product->name }}">
            </div>
            <!-- Mô tả sản phẩm bằng hình ảnh từ trường description (các URL cách nhau bởi dấu ,) -->
            @php
            $desc = trim($product->description ?? '');
            $descItems = collect($desc ? explode(',', $desc) : [])->map(fn($s)=>trim($s))->filter();
            $isImages = $descItems->count() && $descItems->every(function($it){
            return str_contains($it, 'http') || preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $it);
            });
            @endphp
            @if($isImages)
            <div class="mt-3 product-desc-box">
                <div class="desc-title">Mô tả sản phẩm</div>
                <div class="d-flex flex-column gap-3">
                    @foreach($descItems as $img)
                    @php($url = (\Illuminate\Support\Str::startsWith($img, ['http://','https://','/'])) ? $img : asset($img))
                    <img src="{{ $url }}" class="w-100 rounded border" alt="Mô tả sản phẩm" loading="lazy" onerror="this.style.display='none'">
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        <div class="col-12 col-md-6 mt-5">
            <h4 class="fw-semibold">Sản phẩm: {{ $product->name }}</h4>
            <div class="text-muted small">Danh mục: {{ $product->category->name ?? 'N/A' }}</div>

            <div class="fs-5 fw-semibold mb-2">Giá: {{ number_format($product->price,0,',','.') }}₫</div>
            @if(!$isImages)
            <p class="text-muted">{{ $product->description }}</p>
            @endif
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
                <div class="d-flex gap-2">
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

        </div>

    </div>

    <div class="mt-5">
        <h6 class="fw-semibold mb-2">Đánh giá</h6>
        <div class="mb-3">
            @forelse($product->reviews as $rv)
            <div class="border rounded p-2 mb-2">
                <div class="d-flex justify-content-between"><strong>{{ $rv->user_name }}</strong><span class="text-warning">@for($i=0;$i<$rv->rating;$i++)★@endfor</span></div>
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
            <div class="col-12 col-md-6"><input class="form-control hidden" name="user_id" value="{{ auth()->user()->id }}" required></div>
            <div class="col-12 col-md-6"><input class="form-control" name="user_name" value="{{ auth()->user()->name }}" placeholder="Tên" required></div>
            <div class="col-12 col-md-6"><input class="form-control" type="email" name="user_email" value="{{ auth()->user()->email }}" placeholder="Email" required></div>
            <div class="col-12 col-md-4">
                <select class="form-select" name="rating" required>
                    <option value="">Rating</option>
                    @foreach(range(1,5) as $r)
                    <option value="{{ $r }}">{{ $r }} ★</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-8"><input class="form-control" name="comment" placeholder="Nhận xét (tuỳ chọn)"></div>
            <div class="col-12"><button class="btn btn-outline-secondary">Gửi đánh giá</button></div>
            @endif
            @endisset
    </div>
</div>
@endsection
@push('styles')
<style>
    .product-desc-box {
        border: 1px solid #e8e1dd;
        border-radius: 8px;
        padding: 10px;
        background: #fff;
    }

    .product-desc-box .desc-title {
        font-weight: 700;
        font-size: 1.05rem;
        color: #2c2c2c;
        margin-bottom: .4rem;
    }

    .product-desc-box img {
        border-color: #f0e9e6 !important;
    }

    .product-main-box {
        width: 100%;
        margin: 0;
    }


    .product-title-logo {
        height: 24px;
        width: auto;
        object-fit: contain;
        display: inline-block;
        vertical-align: middle;
    }
</style>
@endpush