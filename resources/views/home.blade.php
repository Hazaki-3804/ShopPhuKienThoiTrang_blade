@extends('layouts.app')
@section('title', 'Shop Nàng Thơ - Phụ kiện thời trang')

@section('content')

<div class="container shop-theme py-4">
    @if(!empty($activeDiscount))
        @include('components.promo-banner', ['discount' => $activeDiscount])
    @endif
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
                            <input type="email" class="form-control rounded-3" placeholder="Email của bạn...">
                        </div>
                        <div class="col-12 col-md-3 d-grid">
                            <button class="btn btn-shopee text-white fw-semibold">
                                <i class="bi bi-send"></i> Đăng ký
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