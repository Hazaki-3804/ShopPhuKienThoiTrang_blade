@extends('adminlte::page')
@section('css')
<!-- Google Fonts Space Grotesk -->
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
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
@stack('scripts')
@endsection