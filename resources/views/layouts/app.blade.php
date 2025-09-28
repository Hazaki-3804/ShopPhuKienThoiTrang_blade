<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', ' Shop Nàng Thơ')</title>

    <!-- Favicon: ensure consistent logo on browser tab -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=3">
    <link rel="icon" type="image/png" href="{{ asset('img/logo_shop.png') }}">
    <link rel="icon" sizes="32x32" type="image/png" href="{{ asset('img/logo_shop.png') }}">
    <link rel="icon" sizes="16x16" type="image/png" href="{{ asset('img/logo_shop.png') }}">
    <link rel="shortcut icon" href="{{ asset('img/logo_shop.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/logo_shop.png') }}">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Pastel theme + custom cursor + transitions -->
    <style>
        :root {
            /* Base neutrals */
            --bg: #ffffff;
            --bg-soft: #faf7f5;      /* nhẹ nhàng hơn trắng tinh */
            --text: #2c2c2c;
            --muted: #6b6b6b;

            /* Accent (Shopee Orange) */
            --accent: #EE4D2D;      /* Shopee primary */
            --accent-600: #D73211;  /* darker hover/active */
            --accent-100: #FFE5DE;  /* light tint for backgrounds */
            --accent-rgb: 238, 77, 45; /* for rgba() usages */

            /* Borders/shadows */
            --card-border: #efe9e6;
            --shadow: 0 10px 20px rgba(0,0,0,.08);
        }

        html, body { background: var(--bg); color: var(--text); }
        /* compensate fixed-top navbar height */
        body { padding-top: 80px; }
        @media (max-width: 576px) { body { padding-top: 72px; } }
        a { color: var(--accent); text-decoration: none; }
        a:hover { color: var(--accent-600); text-decoration: underline; }

        .brand-gradient { background: linear-gradient(135deg, #ffd1dc, #cfe8ff, #e6d6ff); }
        .btn-brand { background: var(--accent); color: #fff; border: none; }
        .btn-brand:hover { background: var(--accent-600); }
        /* Shopee-like buttons */
        .btn-shopee { background-color: var(--accent) !important; border-color: var(--accent) !important; color: #fff !important; }
        .btn-shopee:hover, .btn-shopee:focus { background-color: var(--accent-600) !important; border-color: var(--accent-600) !important; color: #fff !important; }
        .btn-outline-shopee { background-color: #fff !important; color: var(--accent) !important; border: 1px solid var(--accent) !important; }
        .btn-outline-shopee:hover, .btn-outline-shopee:focus, .btn-outline-shopee:active { background-color: var(--accent) !important; color: #fff !important; border-color: var(--accent) !important; }
        /* Disabled states keep Shopee tone instead of gray */
        .btn-shopee:disabled, .btn-shopee.disabled { background-color: var(--accent) !important; border-color: var(--accent) !important; color: #fff !important; opacity: .65; cursor: not-allowed; }
        .btn-outline-shopee:disabled, .btn-outline-shopee.disabled { background-color: #fff !important; color: var(--accent) !important; border-color: var(--accent) !important; opacity: .65; cursor: not-allowed; }
        /* Size/shape similar to Shopee product page */
        .btn-shopee-lg, .btn-outline-shopee-lg {
            padding: .6rem 1.1rem;
            font-weight: 600;
            border-radius: 6px;
            line-height: 1.2;
        }
        .card-hover { transition: transform .2s ease, box-shadow .2s ease; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: var(--shadow); }

        /* Custom cursor */
        body {  }
        /* .cursor-dot { position: fixed; top: 0; left: 0; pointer-events: none; z-index: 9999; width: 8px; height: 8px; border-radius: 50%; background: var(--brand); transform: translate(-50%, -50%); } */

        /* Fade/slide utility */
        .fade-up { opacity: 0; transform: translateY(12px); transition: all .4s ease; }
        .fade-up.in-view { opacity: 1; transform: translateY(0); }
    </style>

    @stack('styles')
</head>
<body>
    @include('components.navbar')

    <main class="py-4">
        @yield('content')
    </main>

    @include('components.footer')

    <div class="cursor-dot" id="cursorDot"></div>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Basic animation + custom cursor script -->
    <script>
        // Custom cursor follow
        (function(){
            const dot = document.getElementById('cursorDot');
            if(!dot) return;
            window.addEventListener('mousemove', (e)=>{
                dot.style.top = e.clientY + 'px';
                dot.style.left = e.clientX + 'px';
            });
        })();

        // Fade-up on scroll
        (function(){
            const els = document.querySelectorAll('.fade-up');
            const io = new IntersectionObserver((entries)=>{
                entries.forEach(en=>{ if(en.isIntersecting){ en.target.classList.add('in-view'); } });
            }, { threshold: 0.12 });
            els.forEach(el=> io.observe(el));
        })();
    </script>

    <!-- Toast container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1080">
        <div id="appToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="appToastBody">Thao tác thành công</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        <div id="appToastError" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="appToastErrorBody">Có lỗi xảy ra</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script>
        // Show toast from server-side flash messages
        (function(){
            const status = @json(session('status'));
            const error = @json(session('error'));
            if (status) {
                const el = document.getElementById('appToast');
                document.getElementById('appToastBody').textContent = status;
                if (window.bootstrap) new bootstrap.Toast(el, { delay: 2500 }).show();
            }
            if (error) {
                const el = document.getElementById('appToastError');
                document.getElementById('appToastErrorBody').textContent = error;
                if (window.bootstrap) new bootstrap.Toast(el, { delay: 3000 }).show();
            }
        })();
    </script>

    @stack('scripts')
</body>
</html>


