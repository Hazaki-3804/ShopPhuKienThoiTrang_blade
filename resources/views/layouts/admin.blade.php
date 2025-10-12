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
</style>
@stack('styles')
@endsection
@section('js')
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<!-- Include AJAX Form Handler for all admin pages -->
<script src="{{ asset('js/admin/ajax-form-handler.js') }}"></script>
@stack('scripts')
@endsection

<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>