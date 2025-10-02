<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top py-3">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4 d-flex align-items-center gap-2" href="{{ route('home') }}">
            <img src="{{ asset('img/logo_shop.png') }}?v=2" alt="Logo Shop" class="logo-shop" width="40" height="40" loading="eager">
            <span class="brand-text">Shop Nàng Thơ</span>
        </a>

        <div class="d-flex d-lg-none align-items-center gap-2">
            <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#searchBoxMobile">
                <i class="bi bi-search fs-5"></i>
            </a>
            <a class="nav-link position-relative" href="{{ route('cart.index') }}">
                <i class="bi bi-bag-heart-fill fs-4 icon-cart-shopee"></i>
                @if(($sharedCartCount ?? 0) > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-shopee">
                    {{ $sharedCartCount }}
                </span>
                @endif
            </a>

            @auth
            <div class="nav-item dropdown">
                <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle fs-4"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    @if(auth()->user()->role_id === 1)
                    <li><a class="dropdown-item" href="{{ route('dashboard') }}">Trang quản trị</a></li>
                    @endif
                    <li><a class="dropdown-item" href="{{ route('password.change') }}">Đổi mật khẩu</a></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button class="dropdown-item" type="submit">Đăng xuất</button>
                        </form>
                    </li>
                </ul>
            </div>
            @else
            <a class="nav-link" href="{{ route('login') }}" title="Đăng nhập">
                <i class="bi bi-box-arrow-in-right fs-4"></i>
            </a>
            <a class="nav-link" href="{{ route('register') }}" title="Đăng ký">
                <i class="bi bi-person-plus-fill fs-4"></i>
            </a>
            @endauth
        </div>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 text-center">
                <li class="nav-item">
                    <a class="nav-link text-uppercase fw-semibold {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Trang chủ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-uppercase fw-semibold {{ request()->routeIs('shop.index') ? 'active' : '' }}" href="{{ route('shop.index') }}">Sản phẩm</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-uppercase fw-semibold {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">Giới thiệu</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-uppercase fw-semibold {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Liên hệ</a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto align-items-center d-none d-lg-flex">
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

                <li class="nav-item me-3">
                    <a class="nav-link position-relative" href="{{ route('cart.index') }}">
                        <i class="bi bi-bag-heart-fill fs-3 icon-cart-shopee"></i>
                        @if(($sharedCartCount ?? 0) > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-shopee">
                            {{ $sharedCartCount }}
                        </span>
                        @endif
                    </a>
                </li>
                @auth
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle fw-semibold" href="#" role="button" data-bs-toggle="dropdown">
                        <img src="{{auth()->user()->avatar}}" alt="" style="width: 36px; height: 36px;" class="rounded-circle object-fit-cover" />{{ auth()->user()->name }}
                    </a>
                    <ul class=" dropdown-menu dropdown-menu-end">
                        @if(auth()->user()->role_id === 1)
                        <li><a class="dropdown-item" href="{{ route('dashboard') }}">Trang quản trị</a></li>
                        @endif
                        <li><a class="dropdown-item" href="{{ route('profile.index') }}">Thông tin người dùng</a></li>
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
                    <a class="btn rounded-pill px-3" href="{{ route('login') }}">Đăng nhập</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-shopee rounded-pill px-3" href="{{ route('register') }}">Đăng ký</a>
                </li>
                @endauth
            </ul>
        </div>
    </div>

    <div class="collapse bg-white shadow-sm p-3 border-top" id="searchBoxMobile">
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