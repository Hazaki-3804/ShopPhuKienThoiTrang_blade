@extends('layouts.app')
@section('title', 'Mochi - Phụ kiện thời trang')

@section('content')
<div class="container">
    <x-carousel
        id="heroCarousel"
        :items="[
        [
            'image' => 'https://picsum.photos/400/300?random=1',
            'title' => 'Phụ kiện pastel tối giản',
            'subtitle' => 'Túi xách • Mũ • Kính • Vòng tay • Dây chuyền',
            'button' => ['url' => route('shop.index'), 'label' => 'Mua ngay'],
        ],
        [
            'image' => 'https://picsum.photos/400/300?random=2',
            'title' => 'Phong cách trẻ trung',
            'subtitle' => 'Phụ kiện cho bạn tự tin mỗi ngày',
            'button' => ['url' => route('shop.index'), 'label' => 'Khám phá'],
        ],
        [
            'image' => 'https://picsum.photos/400/300?random=3',
            'title' => 'Ưu đãi pastel tháng 9',
            'subtitle' => 'Giảm giá lên đến 30%',
            'button' => ['url' => route('shop.index'), 'label' => 'Mua ngay'],
        ],
    ]" />



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
    @php($demo = [
    ['id'=>1,'name'=>'Túi pastel mini','category'=>'Túi xách','price'=>'499.000₫'],
    ['id'=>2,'name'=>'Nón bucket beige','category'=>'Mũ','price'=>'199.000₫'],
    ['id'=>3,'name'=>'Kính trong suốt','category'=>'Kính','price'=>'299.000₫'],
    ['id'=>4,'name'=>'Vòng tay charm','category'=>'Vòng tay','price'=>'259.000₫'],
    ['id'=>5,'name'=>'Dây chuyền vàng','category'=>'Dây chuyền','price'=>'799.000₫'],
    ['id'=>6,'name'=>'Balo da bò','category'=>'Túi xách','price'=>'899.000₫'],
    ['id'=>7,'name'=>'Nón bucket màu đỏ','category'=>'Mũ','price'=>'199.000₫'],
    ['id'=>8,'name'=>'Kính trong suốt màu hồng','category'=>'Kính','price'=>'299.000₫'],
    ])
    <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            @foreach (array_chunk($demo, 4) as $chunkIndex => $chunk)
            <div class="carousel-item @if($chunkIndex === 0) active @endif">
                <div class="row g-3">
                    @foreach ($chunk as $p)
                    <div class="col-6 col-md-3">
                        @include('components.product-card', ['product' => $p])
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        <!-- {{-- Controls --}} -->
        <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev"
            style="width: auto; left: -1rem;">
            <span class="carousel-control-prev-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>

        <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next"
            style="width: auto; right: -1rem;">
            <span class="carousel-control-next-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
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