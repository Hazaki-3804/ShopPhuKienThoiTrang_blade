@extends('layouts.admin')

@section('title', 'Lỗi hệ thống - 500')

@section('content_header')
<!-- <h1 class="text-danger">
    <i class="fas fa-bug"></i> Lỗi hệ thống 500
</h1> -->
@stop

@section('content')
<div class="error-page d-flex mx-auto">
    <div class="error-content text-center" style="margin: 0 auto;">
        <h2 class="headline text-danger"> 500</h2>
        
        <div class="error-body">
            <h3><i class="fas fa-bug text-danger"></i> Oops! Có lỗi xảy ra.</h3>
            
            <p class="lead">
                Hệ thống đang gặp sự cố kỹ thuật.
                <br>
                Vui lòng thử lại sau hoặc liên hệ với quản trị viên hệ thống.
            </p>
            
            @if(config('app.debug') && isset($exception) && auth()->check() && auth()->user()->role_id == 1)
            <div class="alert alert-danger text-left mt-3">
                <h5><i class="fas fa-info-circle"></i> Chi tiết lỗi (Debug mode):</h5>
                <p class="mb-1"><strong>Message:</strong> {{ $exception->getMessage() }}</p>
                <p class="mb-1"><strong>File:</strong> {{ $exception->getFile() }}</p>
                <p class="mb-0"><strong>Line:</strong> {{ $exception->getLine() }}</p>
            </div>
            @endif
            
            <div class="mt-4">
                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-home"></i> Về trang chủ Admin
                </a>
                <button onclick="location.reload()" class="btn btn-warning btn-lg ml-2">
                    <i class="fas fa-redo"></i> Thử lại
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.error-page {
    padding: 2rem 0;
}

.headline {
    font-size: 6rem;
    font-weight: 900;
    margin-bottom: 1rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
}

.error-body h3 {
    font-size: 1.5rem;
    margin-bottom: 1rem;
    color: #495057;
}

.error-body p {
    font-size: 1.1rem;
    color: #6c757d;
    margin-bottom: 2rem;
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1.1rem;
}

.alert {
    max-width: 600px;
    margin: 0 auto;
}
</style>
@endsection
