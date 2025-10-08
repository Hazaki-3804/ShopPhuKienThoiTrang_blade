@extends('layouts.admin')

@section('title', 'Không có quyền truy cập - 403')

@section('content_header')
<h1 class="text-warning">
    <i class="fas fa-lock"></i> Lỗi 403
</h1>
@stop

@section('content')
<div class="error-page">
    <div class="error-content text-center">
        <h2 class="headline text-warning"> 403</h2>
        
        <div class="error-body">
            <h3><i class="fas fa-lock text-warning"></i> Truy cập bị từ chối.</h3>
            
            <p class="lead">
                Bạn không có quyền truy cập vào trang này.
                <br>
                Vui lòng liên hệ quản trị viên để được cấp quyền.
            </p>
            
            <div class="mt-4">
                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-home"></i> Về trang chủ Admin
                </a>
                <button onclick="history.back()" class="btn btn-secondary btn-lg ml-2">
                    <i class="fas fa-arrow-left"></i> Quay lại
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
</style>
@endsection
