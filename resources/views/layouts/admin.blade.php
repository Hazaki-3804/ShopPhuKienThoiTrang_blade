<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin | Fashion Accessories')</title>

    <!-- AdminLTE + Bootstrap 5 + FontAwesome + DataTables -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css" />
    <style>
        :root { --brand: #c39bd3; }
        body { background: #fff; }
        .cursor-dot { position: fixed; pointer-events: none; z-index: 9999; width: 8px; height: 8px; border-radius: 50%; background: var(--brand); transform: translate(-50%,-50%); }
        .widget.fade-up { opacity: 0; transform: translateY(12px); transition: all .4s ease; }
        .widget.fade-up.in-view { opacity: 1; transform: translateY(0); }
    </style>
    @stack('styles')
</head>
<body class="layout-fixed">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button"><i class="fa-solid fa-bars"></i></a>
            </li>
        </ul>
        <form class="form-inline ms-2">
            <div class="input-group">
                <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                <button class="btn btn-navbar" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
            </div>
        </form>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" data-bs-toggle="dropdown" href="#"><i class="fa-regular fa-bell"></i><span class="badge bg-danger ms-1">3</span></a>
                <div class="dropdown-menu dropdown-menu-end p-2" style="min-width: 260px;">
                    <h6 class="dropdown-header">Notifications</h6>
                    <a href="#" class="dropdown-item">New order placed</a>
                    <a href="#" class="dropdown-item">Stock low: Necklace A</a>
                    <a href="#" class="dropdown-item">New customer registered</a>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link" data-bs-toggle="dropdown" href="#"><i class="fa-regular fa-user"></i></a>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="#">Profile</a>
                    <a class="dropdown-item" href="#">Settings</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#">Logout</a>
                </div>
            </li>
        </ul>
    </nav>

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('admin.dashboard') }}" class="brand-link text-decoration-none">
            <span class="brand-text fw-semibold">Fashion Admin</span>
        </a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-lte-toggle="treeview" role="menu">
                    <li class="nav-item"><a href="{{ route('admin.dashboard') }}" class="nav-link"><i class="fa-solid fa-gauge-high me-2"></i><p>Dashboard</p></a></li>
                    <li class="nav-item"><a href="{{ route('admin.orders.index') }}" class="nav-link"><i class="fa-solid fa-receipt me-2"></i><p>Orders</p></a></li>
                    <li class="nav-item"><a href="{{ route('admin.products.index') }}" class="nav-link"><i class="fa-solid fa-bag-shopping me-2"></i><p>Products</p></a></li>
                    <li class="nav-item"><a href="{{ route('admin.customers.index') }}" class="nav-link"><i class="fa-regular fa-user-group me-2"></i><p>Customers</p></a></li>
                    <li class="nav-item"><a href="{{ route('admin.analytics') }}" class="nav-link"><i class="fa-solid fa-chart-line me-2"></i><p>Analytics</p></a></li>
                    <li class="nav-item"><a href="{{ route('admin.settings') }}" class="nav-link"><i class="fa-solid fa-gear me-2"></i><p>Settings</p></a></li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"><h1 class="m-0">@yield('page_title', 'Dashboard')</h1></div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            @yield('breadcrumbs')
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </section>
    </div>

    <footer class="main-footer small">
        <strong>&copy; {{ date('Y') }} Fashion Accessories.</strong> All rights reserved.
    </footer>
</div>

<div class="cursor-dot" id="cursorDot"></div>
//Jquery
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
<script>
    // Custom cursor follow (ring removed)
    (function(){
        const dot = document.getElementById('cursorDot');
        if(!dot) return;
        window.addEventListener('mousemove', (e)=>{
            dot.style.top = e.clientY + 'px';
            dot.style.left = e.clientX + 'px';
        });
    })();

    // Reveal widgets on view
    (function(){
        const widgets = document.querySelectorAll('.widget.fade-up');
        const io = new IntersectionObserver((entries)=>{
            entries.forEach(en=>{ if(en.isIntersecting){ en.target.classList.add('in-view'); } });
        }, { threshold: 0.12 });
        widgets.forEach(w=> io.observe(w));
    })();

    // Init DataTables for any .datatable tables
    (function(){
        if (window.jQuery) return; // using vanilla init
        document.querySelectorAll('table.datatable').forEach(function(tbl){
            const id = tbl.getAttribute('id');
            if (id) {
                new DataTable('#'+id);
            } else {
                new DataTable(tbl);
            }
        });
    })();
</script>
@stack('scripts')
</body>
</html>


