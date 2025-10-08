@extends('layouts.app')
@section('title', 'Shop Nàng Thơ - Phụ kiện thời trang')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
    .shop-theme {
        font-family: 'Helvetica Neue', Arial, sans-serif;
    }

    .shop-header {
        display: flex;
        align-items: center;
    }

    .shop-logo {
        height: 50px;
        border-radius: 8px;
    }

    .shop-title {
        font-size: 1.8rem;
        font-weight: bold;
    }

    /* Carousel cải thiện để hiển thị hình ảnh rõ nét */
    #heroCarousel .carousel-item {
        position: relative;
        height: 520px;
        overflow: hidden;
        border-radius: 15px;

    }

    #heroCarousel .carousel-item::before {
        content: "";
        position: absolute;
        inset: 0;
        background-size: cover;
        background-position: center;
        filter: blur(15px) brightness(0.8);
        z-index: 1;
        transform: scale(1.1);
    }

    #heroCarousel .carousel-item img {
        position: relative;
        z-index: 2;
        height: 100%;
        width: 100%;
        object-fit: cover;
        object-position: center;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    }

    /* Responsive cho slideshow */
    @media (max-width: 992px) {
        #heroCarousel .carousel-item {
            height: 420px;
        }
    }

    @media (max-width: 768px) {
        #heroCarousel .carousel-item {
            height: 360px;
        }

        #heroCarousel .carousel-item img {
            object-fit: contain;
            height: 340px;
            width: auto;
            max-width: 100%;
            margin: 10px auto;
            display: block;
        }
    }

    #heroCarousel .carousel-item[data-bg="sline1"]::before {
        background-image: url("{{ asset('img/sline1.png') }}");
    }

    #heroCarousel .carousel-item[data-bg="sline2"]::before {
        background-image: url("{{ asset('img/sline2.jpg') }}");
    }

    #heroCarousel .carousel-item[data-bg="sline3"]::before {
        background-image: url("{{ asset('img/sline3.jpg') }}");
    }

    #heroCarousel .carousel-item[data-bg="sline4"]::before {
        background-image: url("{{ asset('img/sline4.jpeg') }}");
    }

    .custom-caption {
        background: rgba(0, 0, 0, 0.45);
        border-radius: 12px;
        padding: 20px;
        bottom: 10%;
        z-index: 3;
        text-shadow: 1px 1px 6px rgba(0, 0, 0, 0.7);
    }

    .custom-caption h2 {
        font-size: 2.1rem;
        font-weight: 700;
        color: #fff;
        animation: fadeInDown 1s ease-in-out;
    }

    .custom-caption p {
        color: #fff;
        animation: fadeInUp 1s ease-in-out;
    }

    /* Nút điều hướng slide */
    .carousel-control-prev,
    .carousel-control-next {
        width: 8%;
        opacity: 1;
        /* luôn thấy */
    }

    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        background-color: rgba(0, 0, 0, 0.65);
        border-radius: 5px;
        padding: 10px 12px;
        display: inline-block;
        /* tăng kích thước */
        background-size: 60% 60%;
        filter: invert(1);
        /* icon sáng trên nền tối */
    }

    /* Nâng các nút điều hướng của hero lên trên ảnh và overlay */
    #heroCarousel .carousel-control-prev,
    #heroCarousel .carousel-control-next {
        z-index: 10;
    }

    /* Tăng độ tương phản cho icon của hero */
    #heroCarousel .carousel-control-prev-icon,
    #heroCarousel .carousel-control-next-icon {
        width: 70px;
        height: 70px;
        font-weight: bold;
        background-color: transparent !important;
        border: none !important;
        box-shadow: none;
        filter: none;
        /* giữ icon trắng mặc định của Bootstrap */
        padding: 0;
        transition: transform .15s ease, filter .15s ease, opacity .15s ease;
    }

    /* Hover/active nhẹ cho hero controls */
    #heroCarousel .carousel-control-prev:hover .carousel-control-prev-icon,
    #heroCarousel .carousel-control-next:hover .carousel-control-next-icon {
        transform: scale(1.08);
        filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.35));
    }

    #heroCarousel .carousel-control-prev:active .carousel-control-prev-icon,
    #heroCarousel .carousel-control-next:active .carousel-control-next-icon {
        transform: scale(0.95);
        opacity: 0.9;
    }

    @media (max-width: 768px) {

        .carousel-control-prev,
        .carousel-control-next {
            width: 12%;
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            padding: 14px;
        }
    }

    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .shop-card {
        background: #fff;
        border: 1px solid #f5f5f5;
        transition: box-shadow 0.2s ease-in-out;
    }

    .shop-card:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    /* Banner with solid color background */
    .banner-card {
        position: relative;
        min-height: 220px;
        background-size: cover;
        background-position: center;
        border-radius: 12px;
        overflow: hidden;
    }

    .banner-card .banner-overlay {
        position: absolute;
        inset: 0;
        /* background: rgba(0, 0, 0, 0.0); */
        /* no darkening for solid color */
    }

    .banner-card .banner-content {
        position: relative;
        z-index: 1;
        padding: 24px;
        color: #fff;
        /* dark text for light background */
    }

    .banner-new {
        background-color: #ffe6e0;
    }

    .banner-best {
        background-color: #fff0d5;
    }

    .btn-shop {
        background-color: var(--accent);
        color: #fff;
        border-radius: 4px;
        font-weight: 600;
        transition: 0.2s;
    }

    .btn-shop:hover {
        background-color: var(--accent-600);
        color: #fff;
    }

    .btn-outline-shop {
        border: 1px solid var(--accent);
        color: var(--accent);
        background-color: transparent;
        transition: 0.2s;
    }

    .btn-outline-shop:hover {
        background-color: var(--accent);
        color: #fff;
    }

    .carousel-item {
        background-color: #fff !important;
    }
</style>

<div class="container shop-theme py-4">
    <!-- Header Logo + Title -->
    <!-- Hero Carousel -->
    <div id="heroCarousel" class="carousel slide mb-4" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="3"></button>
        </div>

        <div class="carousel-inner">
            <div class="carousel-item active" data-bg="sline1">
                <img src="{{ asset('img/sline1.png') }}" alt="Slide 1">
                <div class="carousel-caption custom-caption">
                    <h2 class="fw-bold">Phụ kiện xinh cho các nàng</h2>
                    <p class="lead">👜 Túi xách • 📿 Vòng tay • 🕶️ Kính</p>
                    <a href="{{ route('shop.index') }}" class="btn btn-shop btn-lg shadow">Mua ngay</a>
                </div>
            </div>
            <div class="carousel-item" data-bg="sline2">
                <img src="{{ asset('img/sline2.jpg') }}" alt="Slide 2">
                <div class="carousel-caption custom-caption">
                    <h2 class="fw-bold">Phong cách tinh tế</h2>
                    <p class="lead">Phù hợp cho những cô nàng yêu sự nhẹ nhàng</p>
                    <a href="{{ route('shop.index', ['sort' => 'new']) }}" class="btn btn-shop btn-lg shadow">Khám phá</a>
                </div>
            </div>
            <div class="carousel-item" data-bg="sline3">
                <img src="{{ asset('img/sline3.jpg') }}" alt="Slide 3">
                <div class="carousel-caption custom-caption">
                    <h2 class="fw-bold">Cập nhật mẫu mới mỗi tuần</h2>
                    <p class="lead">Luôn tự tin và tỏa sáng trong mọi khoảnh khắc</p>
                    <a href="{{ route('shop.index', ['sort' => 'best']) }}" class="btn btn-shop btn-lg shadow">Xem ngay</a>
                </div>
            </div>
            <div class="carousel-item" data-bg="sline4">
                <img src="{{ asset('img/sline4.jpeg') }}" alt="Slide 4">
                <div class="carousel-caption custom-caption">
                    <h2 class="fw-bold">Ưu đãi cuối tuần</h2>
                    <p class="lead">Săn sale phụ kiện xinh với giá tốt</p>
                    <a href="{{ route('shop.index', ['sort' => 'popular']) }}" class="btn btn-shop btn-lg shadow">Khám phá</a>
                </div>
            </div>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" style="width: auto; left: -1rem;">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next" style="width: auto; right: -1rem;">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <!-- Banners -->
    <div class="row g-3 mt-4">
        <div class="col-12 col-md-6 fade-up">
            <div class="banner-card banner-new" @if(!empty($newBannerImage)) style="background-image:url('{{ $newBannerImage }}');" @endif>
                <div class="banner-overlay"></div>
                <div class="banner-content">
                    <h5 class="fw-semibold mb-1">New Arrivals</h5>
                    <p class="small mb-2">Mẫu mới cập nhật hàng tuần</p>
                    <a href="{{ route('shop.index', ['sort' => 'new']) }}" class="btn btn-shopee btn-sm">Khám phá</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 fade-up">
            <div class="banner-card banner-best" @if(!empty($bestBannerImage)) style="background-image:url('{{ $bestBannerImage }}');" @endif>
                <div class="banner-overlay"></div>
                <div class="banner-content">
                    <h5 class="fw-semibold mb-1">Best Sellers</h5>
                    <p class="small mb-2">Sản phẩm bán chạy nhất</p>
                    <a href="{{ route('shop.index', ['sort' => 'best']) }}" class="btn btn-shopee btn-sm">Xem ngay</a>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-4">
        <h5 class="fw-semibold mb-3">Đề xuất cho bạn</h5>
        <div class="row g-3">
            <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    @foreach ($products as $chunkIndex => $chunk)
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
                    <span class="carousel-control-prev-icon bg-dark" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>

                <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next"
                    style="width: auto; right: -1rem;">
                    <span class="carousel-control-next-icon bg-dark" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
            <!-- Newsletter -->
            <div class="mt-5 fade-up">
                <div class="p-4 rounded-4 shadow-lg bg-light position-relative overflow-hidden">
                    <!-- Background accent -->

                    <h5 class="fw-bold text-dark mb-2">✨ Nhận ưu đãi sớm!</h5>
                    <p class="text-muted mb-3">Đăng ký để không bỏ lỡ <span class="fw-semibold text-dark">sản phẩm mới nhất</span> và <span class="fw-semibold text-dark">khuyến mãi độc quyền</span>.</p>

                    <form class="row g-2">
                        <div class="col-12 col-md-9">
                            <input type="email" class="form-control rounded-3" placeholder="📩 Email của bạn...">
                        </div>
                        <div class="col-12 col-md-3 d-grid">
                            <button class="btn text-white fw-semibold"
                                style="background-color:#ff6f3c; border:none; border-radius:12px;">
                                Subscribe
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <script>
                (function() {
                    const el = document.getElementById('heroCarousel');
                    if (!el || !window.bootstrap) return;
                    const carousel = bootstrap.Carousel.getOrCreateInstance(el, {
                        interval: 5000,
                        ride: false
                    });
                    let paused = false;

                    function isTypingContext() {
                        const ae = document.activeElement;
                        if (!ae) return false;
                        const tag = (ae.tagName || '').toLowerCase();
                        return tag === 'input' || tag === 'textarea' || ae.isContentEditable;
                    }

                    document.addEventListener('keydown', (e) => {
                        if (isTypingContext()) return; // đừng bắt phím khi đang nhập liệu
                        if (e.key === 'ArrowLeft' || e.key === 'a' || e.key === 'A') {
                            e.preventDefault();
                            carousel.prev();
                        } else if (e.key === 'ArrowRight' || e.key === 'd' || e.key === 'D') {
                            e.preventDefault();
                            carousel.next();
                        } else if (e.code === 'Space') {
                            e.preventDefault();
                            if (paused) {
                                carousel.cycle();
                            } else {
                                carousel.pause();
                            }
                            paused = !paused;
                        }
                    });
                })();
            </script>
        </div>
    </div>
</div>
<!-- Chatbot -->
<x-chatbot />
@endsection