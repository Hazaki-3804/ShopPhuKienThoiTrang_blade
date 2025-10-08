@extends('layouts.admin')

@section('title', 'Trang không tồn tại - 404')

@section('content_header')
@stop

@section('content')
<div class="error-page d-flex mx-auto">
    <div class="error-content text-center" style="margin: 0 auto;">
        <h2 class="headline text-warning"> 404</h2>

        <div class="error-body">
            <h3><i class="fas fa-exclamation-triangle text-warning"></i> Oops! Trang không tồn tại.</h3>

            <p class="lead">
                Trang bạn đang tìm kiếm không tồn tại trong hệ thống quản trị.
                <br>
                Có thể URL đã bị thay đổi hoặc trang đã bị xóa.
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
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
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