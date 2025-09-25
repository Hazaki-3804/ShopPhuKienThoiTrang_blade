@extends('layouts.app')
@section('title', 'Fasho - Pastel Accessories')

@section('content')
<div class="container">
    <!-- Hero Slider (simple) -->
    <div id="hero" class="p-4 rounded-3 brand-gradient text-center text-dark fade-up">
        <h2 class="fw-semibold">Phụ kiện pastel tối giản</h2>
        <p class="mb-3">Túi xách • Mũ • Kính • Vòng tay • Dây chuyền</p>
        <a href="{{ route('shop.index') }}" class="btn btn-brand">Mua ngay</a>
    </div>

    <!-- Banners -->
    <div class="row g-3 mt-4">
        <div class="col-12 col-md-6 fade-up">
            <div class="p-4 bg-light rounded-3 h-100">
                <h5 class="fw-semibold">New Arrivals</h5>
                <p class="text-muted small">Mẫu mới cập nhật hàng tuần</p>
                <a href="{{ route('shop.index', ['sort' => 'new']) }}" class="btn btn-outline-secondary btn-sm">Khám phá</a>
            </div>
        </div>
        <div class="col-12 col-md-6 fade-up">
            <div class="p-4 bg-light rounded-3 h-100">
                <h5 class="fw-semibold">Best Sellers</h5>
                <p class="text-muted small">Sản phẩm bán chạy nhất</p>
                <a href="{{ route('shop.index', ['sort' => 'best']) }}" class="btn btn-outline-secondary btn-sm">Xem ngay</a>
            </div>
        </div>
    </div>

    <!-- Product sections -->
    <div class="mt-4">
        <h5 class="fw-semibold mb-3">Trending Accessories</h5>
        <div class="row g-3">
            @php($demo = [
                ['id'=>1,'name'=>'Túi pastel mini','category'=>'Túi xách','price'=>'499.000₫'],
                ['id'=>2,'name'=>'Nón bucket beige','category'=>'Mũ','price'=>'199.000₫'],
                ['id'=>3,'name'=>'Kính trong suốt','category'=>'Kính','price'=>'299.000₫'],
                ['id'=>4,'name'=>'Vòng tay charm','category'=>'Vòng tay','price'=>'259.000₫']
            ])
            @foreach($demo as $p)
                <div class="col-6 col-md-3">
                    @include('components.product-card', ['product' => $p])
                </div>
            @endforeach
        </div>
    </div>

    <!-- Newsletter -->
    <div class="mt-5 fade-up">
        <div class="p-4 bg-light rounded-3">
            <h5 class="fw-semibold">Nhận ưu đãi pastel</h5>
            <form class="row g-2 mt-2">
                <div class="col-12 col-md-9">
                    <input type="email" class="form-control" placeholder="Email của bạn">
                </div>
                <div class="col-12 col-md-3 d-grid">
                    <button class="btn btn-brand">Subscribe</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


