<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top py-3">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4 d-flex align-items-center gap-2" href="{{ route('home') }}">
            <img src="{{ asset($site_settings['site_logo']??'img/logo_shop.png') }}?v=2" alt="Logo Shop" class="logo-shop" width="40" height="40" loading="eager">
            <span class="brand-text">{{ $site_settings['site_name']??'Shop Nàng Thơ' }}</span>
        </a>

        <div class="d-flex d-lg-none align-items-center gap-2">
            <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#searchBoxMobile">
                <i class="bi bi-search fs-5"></i>
            </a>
            <a class="nav-link position-relative mx-2" href="{{ route('cart.index') }}">
                <i class="bi bi-bag-heart-fill fs-4 icon-cart-shopee"></i>
                @if(($sharedCartCount ?? 0) > 0)
                <span class="position-absolute top-0 start-100 translate-middle-y
                            badge rounded-circle bg-shopee d-flex align-items-center justify-content-center"
                    style="width: 20px; height: 20px;">
                    {{ $sharedCartCount }}
                </span>
                @endif
            </a>
            @auth
            <div class="nav-item dropdown">
                <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown">
                    <img src="{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->username) . '&background=random&color=fff&size=40' }}" alt="avatar" class="rounded-circle" width="32" height="32">
                </a>
                <ul class="dropdown-menu dropdown-menu-end user-menu" style="--bs-dropdown-link-hover-bg:#ffede7; --bs-dropdown-link-hover-color:#EE4D2D; --bs-dropdown-link-active-bg:#ffede7; --bs-dropdown-link-active-color:#EE4D2D;">
                    <li class="px-3 pt-2 pb-1">
                        <div class="d-flex align-items-center gap-2">
                        <img src="{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->username) . '&background=random&color=fff&size=40' }}" alt="avatar" class="rounded-circle" width="32" height="32">
                            <div class="lh-sm">
                                <div class="fw-semibold">Xin chào ! {{ auth()->user()->username }}</div>
                                <div class="text-muted small">{{ auth()->user()->email }}</div>
                            </div>
                        </div>
                    </li>
                    <li>
                        <hr class="dropdown-divider my-2">
                    </li>
                    @auth
                    @hasanyrole('Admin|Nhân viên')
                    <li>
                        <a class="dropdown-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2 me-2"></i> Trang quản trị
                        </a>
                    </li>
                    @endhasanyrole
                    @endauth
                    <li>
                        <a class="dropdown-item {{ request()->routeIs('profile.index') ? 'active' : '' }}" href="{{ route('profile.index') }}">
                            <i class="bi bi-person-badge me-2"></i> Thông tin người dùng
                        </a>
                    </li>
                    <li>
                        @if (Route::has('user.orders.index'))
                        <a class="dropdown-item {{ request()->routeIs('user.orders.*') ? 'active' : '' }}" href="{{ route('user.orders.index') }}">
                            <i class="bi bi-bag-check me-2"></i> Đơn hàng của tui
                        </a>
                        @else
                        <a class="dropdown-item" href="{{ route('profile.index') }}#orders">
                            <i class="bi bi-bag-check me-2"></i> Đơn hàng của tui
                        </a>
                        @endif
                    </li>
                    <li>
                        <a class="dropdown-item {{ request()->routeIs('password.change') ? 'active' : '' }}" href="{{ route('password.change') }}">
                            <i class="bi bi-key me-2"></i> Đổi mật khẩu
                        </a>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" class="m-0">@csrf
                            <button class="dropdown-item" type="submit">
                                <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
                            </button>
                        </form>
                    </li>                        
                </ul>
            </div>
            @endauth
        </div>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 text-center">
                <li class="nav-item underline-custom">
                    <a class="nav-link fw-bold text-dark {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}"> <i class="bi bi-house me-2 d-lg-none"></i>Trang chủ</a>
                </li>
                <li class="nav-item underline-custom">
                    <a class="nav-link fw-bold text-dark {{ request()->routeIs('shop.index') || request()->routeIs('shop.show') ? 'active' : '' }}" href="{{ route('shop.index') }}"> <i class="bi bi-cart me-2 d-lg-none"></i>Sản phẩm</a>
                </li>
                <li class="nav-item underline-custom">
                    <a class="nav-link fw-bold text-dark {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}"> <i class="bi bi-info-circle me-2 d-lg-none"></i>Giới thiệu</a>
                </li>
                <li class="nav-item underline-custom">
                    <a class="nav-link fw-bold text-dark {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}"> <i class="bi bi-telephone me-2 d-lg-none"></i>Liên hệ</a>
                </li>
                @guest
                <li class="nav-item d-lg-none">
                    <a class="nav-link fw-bold text-dark" href="{{ route('login') }}"> <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập</a>
                </li>
                <li class="nav-item d-lg-none">
                    <a class="nav-link fw-bold text-dark" href="{{ route('register') }}"> <i class="bi bi-box-arrow-in-right me-2"></i>Đăng ký</a>
                </li>
                @endguest
            </ul>

            <ul class="navbar-nav ms-auto align-items-center d-none d-lg-flex">
                <li class="nav-item me-3">
                    <form class="d-flex w-100" action="{{ route('shop.index') }}" method="GET">
                        <div class="input-group input-group-sm shadow-sm rounded-start-3 w-100" style="min-width: 300px;">
                            <input class="form-control border-end-0 p-2 rounded-start-3"
                                type="search" name="q" value="{{ request('q') }}" placeholder="Tìm sản phẩm theo tên hoặc mô tả..." aria-label="Tìm kiếm">
                            @if(request('category'))
                            <input type="hidden" name="category" value="{{ request('category') }}">
                            @endif
                            <button class="btn btn-search" style='max-width: 30px;' type="submit" aria-label="Tìm kiếm">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>
                </li>

                <li class="nav-item me-3 position-relative">
                    <a class="nav-link position-relative text-center" href="#" data-cart-toggle role="button" aria-expanded="false">
                        <i class="bi bi-bag-heart-fill fs-3 icon-cart-shopee"></i>
                        @if(($sharedCartCount ?? 0) > 0)
                        <span class="position-absolute top-0 start-100 translate-middle-x
                                    badge rounded-circle bg-shopee d-flex align-items-center justify-content-center"
                            style="width: 20px; height: 20px;">
                            {{ $sharedCartCount }}
                        </span>
                        @endif
                    </a>
                    <div class="cart-dropdown shadow p-3" data-cart-dropdown>
                        <div class="cart-dropdown-body">
                            @if(($sharedCartPreview ?? collect())->count() > 0)
                            @foreach(($sharedCartPreview ?? collect())->take(5) as $line)
                            @php
                            $img = $line['image'] ?? null;
                            if ($img && !\Illuminate\Support\Str::startsWith($img, ['http://','https://','/'])) { $img = asset($img); }
                            $img = $img ?: 'https://picsum.photos/80/80?random=' . ($line['id'] ?? 1);
                            @endphp
                            <div class="cart-item d-flex gap-2 align-items-center">
                                <img src="{{ $img }}" class="rounded border cart-item-thumb" alt="{{ $line['name'] }}">
                                <div class="flex-grow-1">
                                    <div class="cart-item-name text-truncate" style="width: 150px;">{{ $line['name'] }}</div>
                                    <div class="small text-muted">Đơn giá: <span class="text-danger fw-semibold">{{ number_format($line['price'],0,',','.') }}₫</span></div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="text-nowrap small">x{{ $line['qty'] }}</div>
                                    <form method="POST" action="{{ route('cart.remove', $line['id']) }}" class="cart-delete-form" onclick="event.stopPropagation();">
                                        @csrf
                                        <button class="btn btn-cart-delete" title="Xóa khỏi giỏ" aria-label="Xóa khỏi giỏ">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @endforeach
                            @else
                            <div class="text-center text-muted small p-2">Giỏ hàng trống</div>
                            @endif
                        </div>
                        <div class="cart-dropdown-footer d-flex justify-content-between align-items-center">
                            <span>Thành tiền</span>
                            <strong>{{ number_format(($sharedCartTotal ?? 0),0,',','.') }}₫</strong>
                        </div>
                        <a href="{{ route('cart.index') }}" class="btn btn-brand w-100 rounded-2 py-2 ">Xem giỏ hàng</a>
                    </div>
                </li>
                @auth
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle fw-semibold" href="#" role="button" data-bs-toggle="dropdown">
                        <img src="{{asset(auth()->user()->avatar??'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->username) . '&background=random&color=fff&size=40')}}" alt="" style="width: 36px; height: 36px;" class="rounded-circle object-fit-cover" /> {{ auth()->user()->username }}
                    </a>
                    <ul class=" dropdown-menu dropdown-menu-end user-menu" style="--bs-dropdown-link-hover-bg:#ffede7; --bs-dropdown-link-hover-color:#EE4D2D; --bs-dropdown-link-active-bg:#ffede7; --bs-dropdown-link-active-color:#EE4D2D;">
                        <li class="px-3 pt-2 pb-1">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ asset(auth()->user()->avatar??'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->username) . '&background=random&color=fff&size=40') }}" alt="avatar" class="rounded-circle" width="32" height="32">
                                <div class="lh-sm">
                                    <div class="fw-semibold">Xin chào, {{ auth()->user()->username }}</div>
                                    <div class="text-muted small">{{ auth()->user()->email }}</div>
                                </div>
                            </div>
                        </li>
                        <li>
                            <hr class="dropdown-divider my-2">
                        </li>
                        @auth
                        @hasanyrole('Admin|Nhân viên')
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="bi bi-speedometer2 me-2"></i> Trang quản trị
                            </a>
                        </li>
                        @endhasanyrole
                        @endauth
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('profile.index') ? 'active' : '' }}" href="{{ route('profile.index') }}">
                                <i class="bi bi-person-badge me-2"></i> Thông tin người dùng
                            </a>
                        </li>
                        <li>
                            @if (Route::has('user.orders.index'))
                            <a class="dropdown-item {{ request()->routeIs('user.orders.*') ? 'active' : '' }}" href="{{ route('user.orders.index') }}">
                                <i class="bi bi-bag-check me-2"></i> Đơn hàng của tui
                            </a>
                            @else
                            <a class="dropdown-item" href="{{ route('profile.index') }}#orders">
                                <i class="bi bi-bag-check me-2"></i> Đơn hàng của tui
                            </a>
                            @endif
                        </li>
                        <!-- <li>
                            <a class="dropdown-item {{ request()->routeIs('password.change') ? 'active' : '' }}" href="{{ route('password.change') }}">
                                <i class="bi bi-key me-2"></i> Đổi mật khẩu
                            </a>
                        </li> -->
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="m-0">@csrf
                                <button class="dropdown-item" type="submit">
                                    <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
                @else
                 <li class="nav-item me-2">
                    <a class="text-decoration-none text-dark px-3 fw-semibold" href="{{ route('register') }}">
                    Đăng ký
                    </a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-brand rounded-pill px-3 fw-semibold" href="{{ route('login') }}">
                    Đăng nhập
                    </a>
                </li>
                @endauth
            </ul>
        </div>
    </div>

    <div class="collapse bg-white shadow-sm p-3 border-top" id="searchBoxMobile">
        <form class="d-flex" action="{{ route('shop.index') }}" method="GET">
            <div class="input-group input-group-sm shadow-sm rounded-start-3">
                <input class="form-control border-end-0 p-2 rounded-start-3"
                    type="search" style="width: 300px" name="q" value="{{ request('q') }}" placeholder="Tìm sản phẩm theo tên hoặc mô tả..." aria-label="Tìm kiếm">
                @if(request('category'))
                @endif
                <button class="btn btn-search" style='max-width: 30px;' type="submit" aria-label="Tìm kiếm">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
    </div>
</nav>
@push('styles')
<style>
    /* Pull the badge closer to the cart icon on all breakpoints */
    .cart-badge-adjust {
        transform: translate(-120%, -32%);
        /* optional fine tuning for compact look */
        pointer-events: none;
    }

    /* If Bootstrap overrides add translate-middle, ensure ours wins */
    .cart-badge-adjust.translate-middle,
    .cart-badge-adjust.translate-middle-x,
    .cart-badge-adjust.translate-middle-y {
        transform: translate(-120%, -32%) !important;
    }

    /* Dropdown active highlight + hover to orange */
    /* Override Bootstrap dropdown link variables so hover definitely turns orange */
    .dropdown-menu {
        --bs-dropdown-link-hover-bg: #ffede7;
        --bs-dropdown-link-hover-color: #EE4D2D;
        --bs-dropdown-link-active-bg: #ffede7;
        --bs-dropdown-link-active-color: #EE4D2D;
    }

    .dropdown-menu .dropdown-item {
        transition: background-color .15s ease, color .15s ease;
    }

    .dropdown-menu .dropdown-item i {
        color: #6c757d;
        transition: color .15s ease;
    }

    .dropdown-menu .dropdown-item.active,
    .dropdown-menu .dropdown-item.active:focus,
    .dropdown-menu .dropdown-item.active:hover {
        background-color: #ffede7 !important;
        /* light orange */
        color: #EE4D2D !important;
        /* shopee orange */
        font-weight: 600;
    }

    .dropdown-menu .dropdown-item.active i {
        color: #EE4D2D !important;
    }

    /* Hover (non-active): turn to orange */
    .dropdown-menu .dropdown-item:not(.active):hover {
        background-color: #ffede7 !important;
        color: #EE4D2D !important;
    }

    .dropdown-menu .dropdown-item:not(.active):hover i {
        color: #EE4D2D !important;
    }

    /* Clicked/pressed and focus states go orange as well */
    .dropdown-menu .dropdown-item:active,
    .dropdown-menu .dropdown-item:focus,
    .dropdown-menu button.dropdown-item:active,
    .dropdown-menu button.dropdown-item:focus {
        background-color: #ffede7 !important;
        color: #EE4D2D !important;
    }

    .dropdown-menu .dropdown-item:active i,
    .dropdown-menu .dropdown-item:focus i,
    .dropdown-menu button.dropdown-item:active i,
    .dropdown-menu button.dropdown-item:focus i {
        color: #EE4D2D !important;
    }
</style>
@endpush
@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let lastMode = window.innerWidth >= 768 ? "desktop" : "mobile";

        function handleResize() {
            let mode = window.innerWidth >= 768 ? "desktop" : "mobile";
            if (mode !== lastMode) { // chỉ xử lý khi đổi chế độ
                lastMode = mode;

                if (mode === "desktop") {
                    let searchBox = document.getElementById('searchBoxMobile');
                    if (searchBox && searchBox.classList.contains('show')) {
                        let collapse = bootstrap.Collapse.getInstance(searchBox);
                        if (collapse) {
                            collapse.hide(); // chỉ gọi 1 lần => animation mượt
                        }
                    }
                }
            }
        }

        window.addEventListener("resize", handleResize);
    });
</script>
<script>
    (function() {
        // Cart dropdown manual control
        const cartToggle = document.querySelector('[data-cart-toggle]');
        const cartDropdown = document.querySelector('[data-cart-dropdown]');
        if (!cartToggle || !cartDropdown) return;
        let cartOpen = false;

        function setCartOpen(v) {
            cartOpen = !!v;
            cartDropdown.classList.toggle('open', cartOpen);
            cartToggle.setAttribute('aria-expanded', cartOpen ? 'true' : 'false');
        }

        // Profile dropdown(s) via Bootstrap API
        const profileToggles = document.querySelectorAll('.navbar [data-bs-toggle="dropdown"]');

        function closeAllProfile() {
            profileToggles.forEach(function(t) {
                try {
                    bootstrap.Dropdown.getOrCreateInstance(t).hide();
                } catch (_) {}
            });
        }
        // When any profile menu opens, close the cart
        profileToggles.forEach(function(t) {
            t.addEventListener('show.bs.dropdown', function() {
                setCartOpen(false);
            });
        });
        // Hover behavior for profile dropdown(s)
        function attachProfileHover(toggleEl) {
            const dd = toggleEl.closest('.dropdown');
            if (!dd) return;
            const api = bootstrap.Dropdown.getOrCreateInstance(toggleEl);
            let timer = null;
            dd.addEventListener('mouseenter', function() {
                if (timer) {
                    clearTimeout(timer);
                    timer = null;
                }
                setCartOpen(false);
                api.show();
            });
            dd.addEventListener('mouseleave', function() {
                if (timer) clearTimeout(timer);
                timer = setTimeout(function() {
                    api.hide();
                }, 150);
            });
        }
        profileToggles.forEach(attachProfileHover);

        // Hover behavior for cart (open on enter, close on leave)
        let cartHoverTimer = null;

        function attachCartHover(el) {
            if (!el) return;
            el.addEventListener('mouseenter', function() {
                if (cartHoverTimer) {
                    clearTimeout(cartHoverTimer);
                    cartHoverTimer = null;
                }
                closeAllProfile();
                setCartOpen(true);
            });
            el.addEventListener('mouseleave', function() {
                if (cartHoverTimer) clearTimeout(cartHoverTimer);
                cartHoverTimer = setTimeout(function() {
                    setCartOpen(false);
                }, 150);
            });
        }
        attachCartHover(cartToggle);
        attachCartHover(cartDropdown);

        // Also keep click support if needed
        cartToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const nextState = !cartOpen;
            if (nextState) closeAllProfile();
            setCartOpen(nextState);
        });

        // Close cart when clicking outside
        document.addEventListener('click', function(e) {
            if (!cartOpen) return;
            if (!cartDropdown.contains(e.target) && !cartToggle.contains(e.target)) {
                setCartOpen(false);
            }
        });
        // Close cart on ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') setCartOpen(false);
        });
    })();
</script>
@endpush