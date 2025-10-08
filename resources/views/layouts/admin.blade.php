@extends('adminlte::page')

@section('css')
<!-- Google Fonts Space Grotesk -->
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
<!-- Bootstrap Icons for admin UI icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body,
    h1,
    h2,
    h3,
    h4,
    h5,
    h6,
    .card,
    .table {
        font-family: 'Space Grotesk', sans-serif;
    }

    /* Sidebar defaults: dark background, light text */
    .main-sidebar .nav-sidebar .nav-link {
        background: transparent !important;
        color: #e5e7eb !important; /* light gray */
        border-radius: .5rem;
        margin: 4px 8px;
    }
    .main-sidebar .nav-sidebar .nav-link .nav-icon,
    .main-sidebar .nav-sidebar .nav-link p,
    .main-sidebar .nav-sidebar .nav-header { color: #e5e7eb !important; }

    /* Only active/hover use teal */
    .main-sidebar .nav-sidebar .nav-link:hover,
    .main-sidebar .nav-sidebar .nav-link.active {
        background-color: #16a2b8 !important; /* teal */
        color: #fff !important;
    }
    .main-sidebar .nav-sidebar .nav-link.active .nav-icon,
    .main-sidebar .nav-sidebar .nav-link:hover .nav-icon,
    .main-sidebar .nav-sidebar .nav-link.active p,
    .main-sidebar .nav-sidebar .nav-link:hover p { color: #fff !important; }

    /* Submenu links keep dark until active/hover */
    .main-sidebar .nav-treeview > .nav-item > .nav-link { margin-left: 16px; }
    .main-sidebar .nav-treeview > .nav-item > .nav-link.active,
    .main-sidebar .nav-treeview > .nav-item > .nav-link:hover { background-color: #0f8fa3 !important; color:#fff !important; }
</style>
@stack('styles')
@endsection

@section('content_top_nav_right')
<ul class="navbar-nav ml-auto">
    @include('partials.user-dropdown')
</ul>
@endsection
@section('js')
<!-- Include AJAX Form Handler for all admin pages -->
<script src="{{ asset('js/admin/ajax-form-handler.js') }}"></script>
@stack('scripts')
@endsection

<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>