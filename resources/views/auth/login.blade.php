@extends('layouts.app')
@section('title', 'Đăng nhập')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">
            <div class="card shadow-sm card-hover fade-up">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3 text-center">Chào mừng trở lại</h5>
                    @if ($errors->any())
                        <div class="alert alert-danger small">@foreach ($errors->all() as $error) <div>{{ $error }}</div> @endforeach</div>
                    @endif
                    <form method="POST" action="{{ route('login') }}" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" required autocomplete="email" autofocus>
                            @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mật khẩu</label>
                            <input id="password" type="password" name="password" class="form-control" required autocomplete="current-password">
                            @error('password')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                <label class="form-check-label" for="remember">Ghi nhớ</label>
                            </div>
                            <a href="{{ route('password.request') }}" class="small">Quên mật khẩu?</a>
                        </div>
                        <button type="submit" class="btn btn-brand w-100">Đăng nhập</button>
                    </form>
                    <div class="text-center text-muted my-3">Hoặc</div>
                    <div class="d-grid gap-2">
                        <a href="{{ route('oauth.redirect', 'google') }}" class="btn btn-outline-secondary"><i class="bi bi-google me-2"></i>Đăng nhập với Google</a>
                        <a href="{{ route('oauth.redirect', 'facebook') }}" class="btn btn-outline-secondary"><i class="bi bi-facebook me-2"></i>Đăng nhập với Facebook</a>
                    </div>
                    <div class="text-center mt-3 small">Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký</a></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    input, button { transition: box-shadow .2s ease, transform .1s ease; }
    input:focus { box-shadow: 0 0 0 .2rem rgba(195,155,211,.25); }
    .btn-brand:hover { transform: translateY(-1px); }
    body { cursor: none; }
</style>
@endpush

