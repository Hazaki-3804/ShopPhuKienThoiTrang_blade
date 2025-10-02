@extends('layouts.app')
@section('title', 'Giỏ hàng')

@section('content')
<div class="container py-4">
    <h5 class="fw-semibold mb-3">Giỏ hàng</h5>
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="list-group">
                @forelse($items as $line)
                @php
                $img = optional($line['product']->product_images[0] ?? null)->image_url;
                if ($img && !\Illuminate\Support\Str::startsWith($img, ['http://','https://','/'])) {
                $img = asset($img);
                }
                $img = $img ?: 'https://picsum.photos/120/120?random=' . $line['product']->id;
                @endphp
                <div class="list-group-item d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ $img }}" alt="{{ $line['product']->name }}" class="cart-thumb rounded border">
                        <div>
                            <div class="fw-semibold">{{ $line['product']->name }}</div>
                            <div class="text-muted small">{{ number_format($line['price'],0,',','.') }}₫</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <form method="POST" action="{{ route('cart.update', $line['product']->id) }}" class="d-flex gap-2 align-items-center auto-update-form">
                            @csrf
                            <input type="number" name="qty" value="{{ $line['qty'] }}" min="1" class="form-control qty-input text-center" style="width:90px;">
                        </form>
                        <form method="POST" action="{{ route('cart.remove', $line['product']->id) }}" class="delete-form">
                            @csrf
                            <button class="btn btn-delete-shopee btn-sm"><i class="bi bi-trash"></i> Xóa</button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="alert alert-warning text-center w-100 my-2" role="alert">
                    <i class="bi bi-cart-x me-2"></i>
                    Giỏ hàng của bạn hiện tại không có sản phẩm nào!
                    <a href="{{ route('shop.index') }}" class="ms-2">Tiếp tục mua sắm</a>
                </div>
                @endforelse
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span>Tạm tính</span>
                        <strong>{{ number_format($total,0,',','.') }}₫</strong>
                    </div>
                    <a href="{{ route('checkout.index') }}" class="btn btn-brand w-100 mt-3">Thanh toán</a>
                </div>
            </div>
        </div>
    </div>
</div>
@if(isset($related) && $related->count())
<div class="container mt-5">
    <h6 class="fw-semibold mb-3">Sản phẩm tương tự</h6>
    <div class="row g-3">
        @foreach($related as $rel)
        <div class="col-6 col-md-4 col-lg-3">
            @include('components.product-card', ['product' => $rel])
        </div>
        @endforeach
    </div>
</div>
@endif
@push('styles')
<style>
    .cart-thumb {
        width: 64px;
        height: 64px;
        object-fit: cover;
    }

    @media (min-width: 992px) {
        .cart-thumb {
            width: 72px;
            height: 72px;
        }
    }

    /* Shopee-like delete button */
    .btn-delete-shopee {
        background: #fff;
        color: #EE4D2D;
        border: 1px solid #EE4D2D;
    }

    .btn-delete-shopee:hover,
    .btn-delete-shopee:focus {
        background: #EE4D2D;
        color: #fff;
        border-color: #EE4D2D;
    }
</style>
@endpush
@push('scripts')
<script>
    // Auto submit update form when quantity changes
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.auto-update-form .qty-input').forEach(function(inp) {
            inp.addEventListener('change', function() {
                const form = this.closest('form');
                if (form) form.submit();
            });
            // Optional: submit on input blur for immediate UX
            inp.addEventListener('blur', function() {
                const form = this.closest('form');
                if (form) form.submit();
            });
        });

    });
</script>
@endpush
@endsection