<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', ' Shop Nàng Thơ')</title>

    <!-- Favicon: ensure consistent logo on browser tab -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=3">
    <link rel="icon" type="image/png" href="{{ asset('img/logo_shop.png') }}">
    <link rel="icon" sizes="32x32" type="image/png" href="{{ asset('img/logo_shop.png') }}">
    <link rel="icon" sizes="16x16" type="image/png" href="{{ asset('img/logo_shop.png') }}">
    <link rel="shortcut icon" href="{{ asset('img/logo_shop.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/logo_shop.png') }}">
    <!-- Google Fonts: Nunito for body, Playfair Display for brand -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Nunito:wght@400;600&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- jQuery --> <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/nouislider@15.7.1/dist/nouislider.min.css" rel="stylesheet">
    <!-- Pastel theme + custom cursor + transitions -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/product-card.css') }}">
    <!-- Cache-bust navbar.css to reflect latest font-family changes -->
    <link rel="stylesheet" href="{{ asset('css/navbar.css') }}?v=20251003">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/toast.css') }}">
    @stack('styles')
</head>

<body class="d-flex flex-column min-vh-100">
    @include('components.navbar')

    <main class="flex-grow-1 pt-2">
        @yield('content')
    </main>

    @include('components.footer')
    <x-back-to-top />
    <x-toast />
    <!-- Bootstrap Bundle -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/nouislider@15.7.1/dist/nouislider.min.js"></script>
    <script src="{{ asset('js/toast.js') }}"></script>
    <script>
        // Fade-up on scroll
        (function() {
            const els = document.querySelectorAll('.fade-up');
            const io = new IntersectionObserver((entries) => {
                entries.forEach(en => {
                    if (en.isIntersecting) {
                        en.target.classList.add('in-view');
                    }
                });
            }, {
                threshold: 0.12
            });
            els.forEach(el => io.observe(el));
        })();
    </script>
    @stack('scripts')
</body>

</html>