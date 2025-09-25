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
        html, body { background: var(--bg-white); color: var(--text-dark); }
        .brand-gradient { background: linear-gradient(135deg, var(--pastel-pink), var(--pastel-blue), var(--pastel-lavender)); }
        .btn-brand { background: var(--brand); color: #fff; border: none; }
        .btn-brand:hover { filter: brightness(0.95); }
        .card-hover { transition: transform .2s ease, box-shadow .2s ease; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 10px 20px rgba(0,0,0,.08); }

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

    @stack('scripts')
</body>
</html>


