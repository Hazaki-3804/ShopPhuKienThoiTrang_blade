<?php

return [
    'title' => 'Fashion Admin',
    'logo' => '<b>Fashion</b>Admin',
    'logo_img' => null,
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_brand' => '',

    'usermenu_enabled' => true,
    'layout_fixed_sidebar' => true,
    'layout_fixed_navbar' => true,

    'menu' => [
        ['text' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'fas fa-gauge-high'],
        ['text' => 'Orders', 'route' => 'admin.orders.index', 'icon' => 'fas fa-receipt'],
        ['text' => 'Products', 'route' => 'admin.products.index', 'icon' => 'fas fa-bag-shopping'],
        ['text' => 'Customers', 'route' => 'admin.customers.index', 'icon' => 'far fa-user'],
        ['text' => 'Analytics', 'route' => 'admin.analytics', 'icon' => 'fas fa-chart-line'],
        ['text' => 'Settings', 'route' => 'admin.settings', 'icon' => 'fas fa-gear'],
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
    ],
];


