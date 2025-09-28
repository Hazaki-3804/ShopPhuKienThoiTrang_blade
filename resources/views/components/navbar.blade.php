@push('styles')
<link rel="stylesheet" href="{{ asset('css/navbar.css') }}">
@endpush
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top py-3">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand fw-bold fs-4 " href="{{ route('home') }}">MochiShop</a>

        <!-- Right icons (mobile only) -->
        <div class="d-flex d-lg-none align-items-center">
            <!-- Search icon -->
            <a class="nav-link me-2" href="#" data-bs-toggle="collapse" data-bs-target="#searchBoxMobile">
                <i class="bi bi-search fs-5"></i>
            </a>
            <!-- Cart -->
            <a class="nav-link me-2 position-relative" href="{{ route('cart.index') }}">
                <i class="bi bi-bag fs-5"></i>
                @if(($sharedCartCount ?? 0) > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    {{ $sharedCartCount }}
                </span>
                @endif
            </a>
            <!-- User -->
            @auth
            <a class="nav-link" href=""><i class="bi bi-person-circle fs-5"></i></a>
            @else
            <a class="btn btn-sm btn-dark rounded-pill px-3" href="{{ route('login') }}">Đăng nhập</a>
            @endauth
        </div>

        <!-- Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menu -->
        <div class="collapse navbar-collapse" id="mainNav">
            <!-- Center menu -->
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 text-center">
                <li class="nav-item"><a class="nav-link text-uppercase fw-semibold" href="{{ route('home') }}">Trang chủ</a></li>
                <li class="nav-item"><a class="nav-link text-uppercase fw-semibold" href="{{ route('shop.index') }}">Sản phẩm</a></li>
                <li class="nav-item"><a class="nav-link text-uppercase fw-semibold" href="{{ route('contact') }}">Liên hệ</a></li>
            </ul>

            <!-- Right (desktop only) -->
            <ul class="navbar-nav ms-auto align-items-center d-none d-lg-flex">
                <!-- Search -->
                <li class="nav-item me-3">
                    <form class="d-flex" action="{{ route('shop.index') }}" method="GET">
                        <div class="input-group input-group-sm shadow-sm rounded-pill">
                            <input class="form-control border-end-0 rounded-start-pill"
                                type="search" name="q" value="{{ request('q') }}" placeholder="Tìm sản phẩm theo tên hoặc mô tả..." aria-label="Tìm kiếm">
                            @if(request('category'))
                            <input type="hidden" name="category" value="{{ request('category') }}">
                            @endif
                            <button class="btn btn-dark rounded-end-pill" type="submit" aria-label="Tìm kiếm">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>
                </li>

                <!-- Cart -->
                <li class="nav-item me-3">
                    <a class="nav-link position-relative p-0" href="{{ route('cart.index') }}">
                        <i class="bi bi-bag-heart fs-3"></i>
                        @if(($sharedCartCount ?? 0) > 0)
                        <span class="position-absolute top-0 start-100 translate-middle bg-danger text-white d-flex align-items-center justify-content-center"
                            style="width: 22px; height: 22px; border-radius: 50%; font-size: 12px;">
                            <strong>{{ $sharedCartCount }}</strong>
                        </span>
                        @endif
                    </a>
                </li>
                <!-- User -->
                @auth
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle fw-semibold" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle fs-5 me-1"></i>{{ auth()->user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('dashboard') }}">Trang quản trị</a></li>
                        <li><a class="dropdown-item" href="{{ route('password.change') }}">Đổi mật khẩu</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">@csrf
                                <button class="dropdown-item" type="submit">Đăng xuất</button>
                            </form>
                        </li>
                    </ul>
                </li>
                @else
                <li class="nav-item me-2">
                    <a class="btn btn-outline-dark rounded-pill px-3" href="{{ route('login') }}">Đăng nhập</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-dark rounded-pill px-3" href="{{ route('register') }}">Đăng ký</a>
                </li>
                @endauth
            </ul>
        </div>
    </div>

    <!-- Search collapse (mobile) -->
    <div class="collapse bg-light p-3" id="searchBoxMobile">
        <form class="d-flex" action="{{ route('shop.index') }}" method="GET">
            <div class="input-group">
                <input class="form-control border-end-0" type="search" name="q" value="{{ request('q') }}" placeholder="Tìm sản phẩm theo tên hoặc mô tả..." aria-label="Tìm kiếm">
                @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <button class="btn btn-dark" type="submit" aria-label="Tìm kiếm"><i class="bi bi-search"></i></button>
            </div>
        </form>
    </div>
</nav>