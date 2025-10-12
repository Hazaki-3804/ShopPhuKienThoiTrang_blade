<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Title & Logo
    |--------------------------------------------------------------------------
    */
    'title' => 'Admin Shop',
    'title_prefix' => '',
    'title_postfix' => '',

    'logo' => 'Admin Shop',
    // 'logo_img' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_alt' => 'Admin Shop',
    'preloader'=>[
        'enabled'=>false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    */
    'usermenu_enabled' => false,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_logout' => false,

    'layout_topnav' => null, // null = sidebar, 'topnav' = chỉ navbar
    'layout_boxed' => false,
    'layout_fixed_sidebar' => true,
    'layout_fixed_navbar' => true,
    'layout_fixed_footer' => false,
    // 'layout_dark_mode' => true,

    /*
    |--------------------------------------------------------------------------
    | Classes
    |--------------------------------------------------------------------------
    */
    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',
    'classes_sidebar' => 'sidebar-dark-info elevation-4',
    'classes_sidebar_nav' => '',
    'classes_footer' => 'text-center',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    */
    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l', // l = leave, s = scroll, n = never
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    */
    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | Auth Views Classes (Login, Register, Forgot Password)
    |--------------------------------------------------------------------------
    */
    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => 'bg-gradient-primary',
    'classes_auth_body' => '',
    'classes_auth_footer' => 'text-center',
    'classes_auth_icon' => 'text-primary',
    'classes_auth_btn' => 'btn-flat btn-primary',

    'menu' => [
        [
            'text' => 'Trang chủ',
            'route' => 'dashboard',
            'icon' => 'fas fa-home',
            'breadcrumb' => true, // bật breadcrumb
        ],
        ['header' => 'SẢN PHẨM'],
        [
            'text' => 'Quản lý đơn hàng',
            'route' => 'admin.orders.index',
            'icon' => 'fas fa-shopping-cart',
            'breadcrumb' => true,
        ],
        [
            'text' => 'Quản lý danh mục',
            'icon' => 'fas fa-tags',
            'breadcrumb' => true,
            'submenu' => [
                [
                    'text' => 'Danh sách danh mục',
                    'route' => 'admin.categories.index',
                    'breadcrumb' => true,
                ],
            ]
        ],
        [
            'text' => 'Quản lý sản phẩm',
            'icon' => 'fas fa-cubes',
            'breadcrumb' => true,
            'submenu' => [
                [
                    'text' => 'Danh sách sản phẩm',
                    'route' => 'admin.products.index',
                    'breadcrumb' => true,
                ],
                [
                    'text' => 'Thêm sản phẩm',
                    'route' => 'admin.products.create',
                    'breadcrumb' => true,
                ],
            ],
        ],
        [
            'text' => 'Quản lý khuyến mãi',
            'icon' => 'fas fa-gift',
            'breadcrumb' => true,
            'submenu' => [
                [
                    'text' => 'Danh sách khuyến mãi',
                    'route' => 'admin.promotions.index',
                    'breadcrumb' => true,
                ],
                [
                    'text' => 'Thêm khuyến mãi',
                    'route' => 'admin.promotions.create',
                    'breadcrumb' => true,
                ],
            ],
        ],
        [
            'text' => 'Quản lý phí vận chuyển',
            'route' => 'admin.shipping-fees.index',
            'icon' => 'fas fa-shipping-fast',
            'breadcrumb' => true,
        ],
        ['header' => 'KHÁCH HÀNG'],
        [
            'text' => 'Quản lý khách hàng',
            'route' => 'admin.customers.index',
            'icon' => 'far fa-user'
        ],
        [
            'text' => 'Thống kê & Báo cáo',
            'icon' => 'fas fa-chart-line',
            'breadcrumb' => true,
            'submenu' => [
                [
                    'text' => 'Tổng quan',
                    'route' => 'admin.statistics.index',
                    'icon' => 'fas fa-tachometer-alt',
                    'breadcrumb' => true,
                ],
                [
                    'text' => 'Thống kê khách hàng',
                    'route' => 'admin.statistics.customers',
                    'icon' => 'fas fa-users',
                    'breadcrumb' => true,
                ],
                [
                    'text' => 'Thống kê sản phẩm',
                    'route' => 'admin.statistics.products',
                    'icon' => 'fas fa-box',
                    'breadcrumb' => true,
                ],
                [
                    'text' => 'Thống kê thời gian',
                    'route' => 'admin.statistics.time',
                    'icon' => 'fas fa-calendar-alt',
                    'breadcrumb' => true,
                ],
            ],
        ],
        
        ['text' => 'Quản lý bình luận', 'route' => 'admin.reviews.index', 'icon' => 'fas fa-comments', 'breadcrumb' => true],
        ['header' => 'NHÂN VIÊN'],
        [
            'text' => 'Quản lý nhân viên',
            'route' => 'admin.users.index',
            'icon' => 'fas fa-uesrs'
        ],
        // ['text' => 'Thống kê', 'route' => 'analytics', 'icon' => 'fas fa-chart-line'],
        ['text' => 'Cài đặt', 'route' => 'settings', 'icon' => 'fas fa-cog'],
    ],

    'plugins' => [
        'Datatables' => [
            'active' => true,
            'files' => [
                ['type' => 'css', 'asset' => false, 'location' => 'https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css'],
                ['type' => 'js',  'asset' => false, 'location' => 'https://code.jquery.com/jquery-3.6.0.min.js'],
                ['type' => 'js',  'asset' => false, 'location' => 'https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js'],
                ['type' => 'js',  'asset' => false, 'location' => 'https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js'],

                // Buttons cho BS4
                ['type' => 'css', 'asset' => false, 'location' => 'https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css'],
                ['type' => 'js',  'asset' => false, 'location' => 'https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js'],
                ['type' => 'js',  'asset' => false, 'location' => 'https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js'],

                // Export
                ['type' => 'js',  'asset' => false, 'location' => 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js'],
                ['type' => 'js',  'asset' => false, 'location' => 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js'],
                ['type' => 'js',  'asset' => false, 'location' => 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js'],
                ['type' => 'js',  'asset' => false, 'location' => 'https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js'],
                ['type' => 'js',  'asset' => false, 'location' => 'https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js'],
            ],
        ],
        'Select2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/select2/js/select2.full.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/select2/css/select2.min.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/chart.js/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/sweetalert2/sweetalert2.all.min.js',
                ],
            ],
        ],
        'Pace' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/pace-progress/themes/blue/pace-theme-flash.css',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/pace-progress/pace.min.js',
                ],
            ],
        ],
        'Fontawesome' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/fontawesome-free/css/all.min.css',
                ],
            ],
        ],
        'CustomCss' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'build/assets/custom.css',
                ],
            ],
        ],
    ],
];
