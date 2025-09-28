<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top py-3">
    <div class="container">
        <!-- Logo + Brand -->
        <a class="navbar-brand fw-bold fs-4 d-flex align-items-center gap-2" href="{{ route('home') }}">
            <!-- Logo -->
            <img src="{{ asset('img/logo_shop.png') }}?v=2" alt="Logo Shop" class="logo-shop" width="40" height="40" loading="eager" onerror="if(this.src.indexOf('?v=')>-1){this.src='{{ asset('img/logo_shop.png') }}'; this.onerror=null;}">
            <!-- Tên Shop -->
            <span class="brand-text">Shop Nàng Thơ</span>
        </a>

        <!-- Right icons (mobile only) -->
        <div class="d-flex d-lg-none align-items-center">
            <!-- Search icon -->
            <a class="nav-link me-2" href="#" data-bs-toggle="collapse" data-bs-target="#searchBoxMobile">
                <i class="bi bi-search fs-5"></i>
            </a>
            <!-- Cart -->
            <a class="nav-link me-2 position-relative" href="{{ route('cart.index') }}">
                <i class="bi bi-cart3 fs-5 icon-cart-shopee"></i>
                @if(($sharedCartCount ?? 0) > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-shopee">
                    {{ $sharedCartCount }}
                </span>
                @endif
            </a>
            <!-- User -->
            @auth
            <a class="nav-link" href=""><i class="bi bi-person-circle fs-5"></i></a>
            @else
            <a class="btn btn-sm btn-shopee rounded-pill px-3" href="{{ route('login') }}">Đăng nhập</a>
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

            <!-- Right (desktop only) -->
            <ul class="navbar-nav ms-auto align-items-center d-none d-lg-flex">
                <!-- Search -->
                <li class="nav-item me-3">
                    <form class="d-flex" action="" method="GET">
                        <div class="input-group input-group-sm">
                            <input class="form-control border-end-0 rounded-start-pill"
                                type="search" name="q" placeholder="Tìm sản phẩm..." aria-label="Search">
                            <button class="btn btn-outline-secondary rounded-end-pill" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>
                </li>
                <!-- Cart -->
                <li class="nav-item me-3">
                    <a class="nav-link position-relative" href="{{ route('cart.index') }}">
                        <i class="bi bi-cart3 fs-5 icon-cart-shopee"></i>
                        @if(($sharedCartCount ?? 0) > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-shopee">
                            {{ $sharedCartCount }}
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
                        <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Trang quản trị</a></li>
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
                    <a class="btn btn-outline-shopee rounded-pill px-3" href="{{ route('login') }}">Đăng nhập</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-shopee rounded-pill px-3" href="{{ route('register') }}">Đăng ký</a>
                </li>
                @endauth
            </ul>
        </div>
    </div>

    <!-- Search collapse (mobile) -->
    <div class="collapse bg-light p-3" id="searchBoxMobile">
        <form class="d-flex" action="" method="GET">
            <div class="input-group">
                <input class="form-control border-end-0" type="search" name="q" placeholder="Tìm sản phẩm..." aria-label="Search">
                <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
            </div>
        </form>
    </div>
</nav>

<style>
    .navbar-nav .nav-link {
        font-size: 0.95rem;
        letter-spacing: 1px;
        transition: color 0.3s ease;
    }

    .navbar-nav .nav-link:hover {
        color: var(--accent) !important;
        text-decoration: underline;
    }

    /* Màu cam khi active */
    .navbar-nav .nav-link.active,
    .navbar-nav .nav-link:active {
        color: var(--accent) !important;
    }

    /* Logo */
    .logo-shop {
        height: 40px; /* Điều chỉnh chiều cao logo */
        width: auto;
        object-fit: contain;
        display: inline-block;
    }

    /* Chữ Shop Nàng Thơ */
    .brand-text {
        font-family: 'Playfair Display', serif;
        font-size: 1.4rem;
        font-weight: bold;
        letter-spacing: 2px;
        color: var(--accent); /* Shopee orange */
    }
    /* Cart icon accent color (peach) */
    .icon-cart-shopee { color: var(--accent); font-size: 1.35rem; }
    .icon-cart-shopee:hover { color: var(--accent-600); }
    .bg-shopee { background-color: var(--accent) !important; }

    /* Nút cam đào chủ đạo */
    .btn-shopee {
        background-color: var(--accent);
        border-color: var(--accent);
        color: white;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-shopee:hover {
        background-color: var(--accent-600);
        border-color: var(--accent-600);
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(var(--accent-rgb), 0.3);
    }
    
    .btn-outline-shopee {
        background-color: transparent;
        color: var(--accent);
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-outline-shopee:hover {
        background-color: var(--accent);
        border-color: var(--accent);
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(var(--accent-rgb), 0.3);
    }
</style>
