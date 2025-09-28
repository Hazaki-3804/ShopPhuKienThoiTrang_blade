<?php

return [
    'logo' => 'MochiShop',
    // 'logo_img' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_brand' => '',

    'usermenu_enabled' => true,
    'layout_fixed_sidebar' => true,
    'layout_fixed_navbar' => true,

    'menu' => [
        [
            'text' => 'Trang chủ',
            'route' => 'dashboard',
            'icon' => 'fas fa-gauge-high',
            'breadcrumb' => true, // bật breadcrumb
        ],
        [
            'text' => 'Quản lý đơn hàng',
            'route' => 'orders.index',
            'icon' => 'fas fa-receipt',
            'breadcrumb' => true,
        ],
        [
            'text' => 'Quản lý sản phẩm',
            'icon' => 'fas fa-bag-shopping',
            'breadcrumb' => true,
            'submenu' => [
                [
                    'text' => 'Danh sách sản phẩm',
                    'route' => 'products.index',
                    'breadcrumb' => true,
                ],
                [
                    'text' => 'Thêm sản phẩm',
                    'breadcrumb' => true,

                ],
                [
                    'text' => 'Danh mục sản phẩm',
                    'breadcrumb' => true,

                ],
            ],
        ],
        ['text' => 'Quản lý khách hàng', 'route' => 'customers.index', 'icon' => 'far fa-user'],
        ['text' => 'Thông kê', 'route' => 'analytics', 'icon' => 'fas fa-chart-line'],
        ['text' => 'Cài đặt', 'route' => 'settings', 'icon' => 'fas fa-gear'],
    ],

    'plugins' => [
        'Datatables' => [
            'active' => true,
            'files' => [
                ['type' => 'css', 'asset' => false, 'location' => 'https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css'],
                ['type' => 'js', 'asset' => false, 'location' => 'https://code.jquery.com/jquery-3.7.1.min.js'],
                ['type' => 'js', 'asset' => false, 'location' => 'https://cdn.datatables.net/2.0.8/js/dataTables.min.js'],
                ['type' => 'js', 'asset' => false, 'location' => 'https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js'],
            ],
        ],
        'Chartjs' => [
            'active' => true,
            'files' => [
                ['type' => 'js', 'asset' => false, 'location' => 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js'],
            ],
        ],
        'Fontawesome' => [
            'active' => true,
            'files' => [
                ['type' => 'css', 'asset' => false, 'location' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css'],
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
