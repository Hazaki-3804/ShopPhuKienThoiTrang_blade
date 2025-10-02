@extends('layouts.app')

@section('content')
<div class="error-page d-flex align-items-center justify-content-center min-vh-70">
    <div class="container p-5">
        <div class="row align-items-center">
            <!-- Cột chữ -->
            <div class="col-md-6" style="padding-left: 300px;">
                <h1 class="error-code">@yield('code', '404')</h1>
                <h2 class="error-title">@yield('title', 'Oops! Trang không tồn tại')</h2>
                <p class="error-message">
                    @yield('message', 'Có thể sản phẩm bạn tìm đã hết hàng hoặc bị xóa.
                    Vui lòng quay lại trang chủ để tiếp tục mua sắm nhé 💍👗.')
                </p>
                <a href="{{ url('/') }}" class="btn-back"><i class="bi bi-arrow-left-square"></i> Về trang chủ</a>
            </div>

            <!-- Cột ảnh -->
            <div class="col-md-6 text-center">
                <img src="{{ asset('img/error.png') }}"
                    alt="Fashion 404" class="img-fluid error-img">
            </div>
        </div>
    </div>
</div>

<style>
    .error-page {
        background: #f1f1f1;
    }

    .error-code {
        font-size: 4rem;
        font-weight: 900;
        color: #ff3d57;
        /* đỏ hồng fashion */
    }

    .error-title {
        font-size: 1.8rem;
        font-weight: 700;
        color: #222;
        margin-bottom: 1rem;
    }

    .error-message {
        font-size: 1.1rem;
        color: #555;
        margin-bottom: 2rem;
        line-height: 1.6;
    }

    .btn-back {
        display: inline-block;
        padding: 0.8rem 1.8rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1rem;
        color: #fff;
        text-decoration: none;
        background: #ff5722;
        transition: transform 0.2s ease
    }

    .btn-back:hover {
        background: #ff5722;
        transform: scale(1.05);
        text-decoration: none;
        color: #fff;
    }

    .error-img {
        max-width: 400px;
    }
</style>
@endsection