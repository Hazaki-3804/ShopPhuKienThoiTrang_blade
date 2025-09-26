<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Fashion Accessories Shop')</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Pastel theme + custom cursor + transitions -->
    <style>
        :root {
            --pastel-pink: #ffd1dc;
            --pastel-blue: #cfe8ff;
            --pastel-lavender: #e6d6ff;
            --pastel-beige: #f6ead4;
            --bg-white: #ffffff;
            --text-dark: #2c2c2c;
            --brand: #c39bd3;
        }

        html,
        body {
            background: var(--bg-white);
            color: var(--text-dark);
        }

        .brand-gradient {
            background: linear-gradient(135deg, var(--pastel-pink), var(--pastel-blue), var(--pastel-lavender));
        }

        .btn-brand {
            background: var(--brand);
            color: #fff;
            border: none;
        }

        .btn-brand:hover {
            filter: brightness(0.95);
        }

        .card-hover {
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, .08);
        }
    </style>

    @stack('styles')
</head>

<body class="d-flex flex-column min-vh-100">
    @include('components.navbar')

    <main class="py-4 flex-grow-1">
        @yield('content')
    </main>

    @include('components.footer')
    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>

</html>