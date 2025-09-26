@extends('layouts.app')
@section('title', $product->name)

@section('content')
<div class="container">
    <div class="row g-4">
        <div class="col-12 col-md-6">
            <div class="ratio ratio-1x1 border rounded-3 overflow-hidden">
                <img src="{{ $product->product_images[0]->image_url ?? 'https://picsum.photos/800/800?random=' . $product->id }}" class="w-100 h-100 object-fit-cover" alt="{{ $product->name }}">
            </div>
            <!-- Thumbnails (demo) -->
            <div class="d-flex gap-2 mt-2">
                @foreach(range(1,4) as $t)
                <img src="{{ $product->image_url ?? 'https://picsum.photos/200/200?random=' . ($product->id + $t) }}" class="rounded border" style="width:72px; height:72px; object-fit:cover;" alt="thumb">
                @endforeach
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="text-muted small">Danh mục: {{ $product->category->name ?? 'N/A' }}</div>
            <h4 class="fw-semibold">{{ $product->name }}</h4>
            <div class="fs-5 fw-semibold mb-2">{{ number_format($product->price,0,',','.') }}₫</div>
            <div class="mb-3 {{ $product->stock>0 ? 'text-success' : 'text-danger' }}">Stock: {{ $product->stock }}</div>
            <p class="text-muted">{{ $product->description }}</p>
            <div class="d-flex gap-2 align-items-center">
                <form class="d-flex gap-2 align-items-center" method="POST" action="{{ route('cart.add', $product->id) }}">
                    @csrf
                    <input type="number" id="qtyInput" class="form-control" name="qty" value="1" min="1" max="{{ $product->stock }}" style="max-width:120px;">
                    <button class="btn btn-brand" {{ $product->stock<1 ? 'disabled' : '' }}>Thêm vào giỏ</button>
                </form>
                <form method="POST" action="{{ route('cart.buynow', $product->id) }}" onsubmit="this.querySelector('input[name=qty]').value=document.getElementById('qtyInput').value;">
                    @csrf
                    <input type="hidden" name="qty" value="1">
                    <button class="btn btn-outline-secondary" {{ $product->stock<1 ? 'disabled' : '' }}>Mua ngay</button>
                </form>
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
        </form>
        @else
        <div class="alert alert-info">Chỉ khách đã mua sản phẩm mới có thể đánh giá.</div>
        @endif
        @endisset
    </div>
</div>
@endsection