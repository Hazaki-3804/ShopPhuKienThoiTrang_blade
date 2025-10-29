@extends('adminlte::page')

@section('css')
<meta name="csrf-token" content="{{ csrf_token() }}">
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
    .btn-action{
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .btn-sm {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    .btn:hover{
        transform: scale(1.05);
    }

    /* 🌟 Dropdown Actions Menu - Nút ba chấm */
    .btn-light.border-0 {
        padding: 8px 12px;
        background: transparent !important;
        transition: background-color 0.2s ease, transform 0.2s ease;
    }
    .btn-light.border-0:hover {
        background-color: #f1f3f5 !important;
        transform: scale(1.05);
    }
    .btn-light.border-0:active {
        background-color: #e9ecef !important;
    }

    /* 🌈 Dropdown menu hiện đại */
    .dropdown-menu {
        min-width: 180px;
        font-size: 0.9rem;
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        background-color: #ffffff;
        padding: 6px 0;
        opacity: 0;
        transform: translateY(8px);
        visibility: hidden;
        transition: all 0.25s ease;
    }

    /* Hiệu ứng xuất hiện mượt */
    .show > .dropdown-menu {
        opacity: 1;
        transform: translateY(0);
        visibility: visible;
    }

    /* ✨ Mục trong menu */
    .dropdown-item {
        padding: 8px 16px;
        color: #495057;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        border-radius: 6px;
        margin: 2px 6px;
        border: none;
        background: transparent;
        width: 100%;
        text-align: left;
        cursor: pointer;
    }
    .dropdown-item i {
        width: 18px;
        text-align: center;
    }
    .dropdown-item:hover {
        background-color: #f1f3f5;
        color: #212529 !important;
        transform: translateX(2px);
    }
    
    /* Fix form trong dropdown */
    .dropdown-menu form {
        margin: 0;
    }
    .dropdown-menu form button.dropdown-item {
        font-family: inherit;
        font-size: inherit;
    }

    /* 🧩 Fix DataTables bị che dropdown */
    table.dataTable td {
        overflow: visible !important;
    }
    
    /* Stats Cards Styling - Giống trang quản lý khuyến mãi */
    .card.bg-primary,
    .card.bg-success,
    .card.bg-warning,
    .card.bg-danger,
    .card.bg-info,
    .card.bg-secondary {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card.bg-primary:hover,
    .card.bg-success:hover,
    .card.bg-warning:hover,
    .card.bg-danger:hover,
    .card.bg-info:hover,
    .card.bg-secondary:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }
      .card {
        border-radius: 8px;
        overflow: hidden;
    }

    .card-header {
        border-bottom: 2px solid #e9ecef;
    }
    /* Badge styling */
    .badge {
        padding: 0.5em 0.75em;
        font-weight: 500;
    }
    
</style>
<link rel="stylesheet" href="{{ asset('css/table.css') }}">
@stack('styles')
@endsection
@section('js')
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<!-- Include AJAX Form Handler for all admin pages -->
<script src="{{ asset('js/admin/ajax-form-handler.js') }}"></script>
@stack('scripts')
@endsection

<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>