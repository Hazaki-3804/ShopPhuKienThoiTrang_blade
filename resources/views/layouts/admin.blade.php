@extends('adminlte::page')

@section('adminlte_css_pre')
<!-- Google Fonts Space Grotesk -->
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
@endsection

@section('css')
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