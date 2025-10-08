@extends('layouts.app')
@section('title', 'Đăng nhập')

@push('styles')
<link href="{{ asset('css/login.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">
            <div class="card shadow-sm card-hover fade-up">
                <div class="card-body p-4">
                    <h3 class="fw-semibold mb-3 text-center">Đăng nhập</h3>
                    <p class="fs-6 text-center">Đăng nhập để sử dụng các tính năng của Shop</p>
                    @if ($errors->has('login_error'))
                    <div class="alert alert-danger alert-dismissible fade show fs-6" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i> {{ $errors->first('login_error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif
                    <form method="POST" action="{{ route('login') }}" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label label-input-important"><i class="bi bi-envelope-fill"></i> Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" required autocomplete="email" autofocus>
                            @error('email')
                            <x-input-error :message="$message" />
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label"><i class="bi bi-person-fill-lock"></i> Mật khẩu</label>
                            <x-input-password name="password" autocomplete="current-password" />
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                <label class="form-check-label" for="remember">Ghi nhớ</label>
                            </div>
                            <a href="{{ route('password.request') }}" class="small">Quên mật khẩu?</a>
                        </div>
                        <button type="submit" class="btn btn-login w-100">Đăng nhập</button>
                    </form>
                    <div class="text-center text-muted my-3">Hoặc</div>
                    <div class="d-grid gap-2">
                        <a href="{{ route('oauth.redirect', 'google') }}" class="btn btn-social d-flex align-items-center justify-content-center gap-2"><svg width="24px" data-e2e="" height="24px" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M43 24.4313C43 23.084 42.8767 21.7885 42.6475 20.5449H24.3877V27.8945H34.8219C34.3724 30.2695 33.0065 32.2818 30.9532 33.6291V38.3964H37.2189C40.885 35.0886 43 30.2177 43 24.4313Z" fill="#4285F4"></path>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M24.3872 43.001C29.6219 43.001 34.0107 41.2996 37.2184 38.3978L30.9527 33.6305C29.2165 34.7705 26.9958 35.4441 24.3872 35.4441C19.3375 35.4441 15.0633 32.1018 13.5388 27.6108H7.06152V32.5337C10.2517 38.7433 16.8082 43.001 24.3872 43.001Z" fill="#34A853"></path>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M13.5395 27.6094C13.1516 26.4695 12.9313 25.2517 12.9313 23.9994C12.9313 22.7472 13.1516 21.5295 13.5395 20.3894V15.4668H7.06217C5.74911 18.0318 5 20.9336 5 23.9994C5 27.0654 5.74911 29.9673 7.06217 32.5323L13.5395 27.6094Z" fill="#FBBC04"></path>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M24.3872 12.5568C27.2336 12.5568 29.7894 13.5155 31.7987 15.3982L37.3595 9.94866C34.0018 6.88281 29.6131 5 24.3872 5C16.8082 5 10.2517 9.25777 7.06152 15.4674L13.5388 20.39C15.0633 15.8991 19.3375 12.5568 24.3872 12.5568Z" fill="#EA4335"></path>
                            </svg> Đăng nhập với Google</a>
                        <a href="{{ route('oauth.redirect', 'facebook') }}" class="btn btn-social d-flex align-items-center justify-content-center gap-2"><svg width="24px" data-e2e="" height="24px" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M24 47C36.7025 47 47 36.7025 47 24C47 11.2975 36.7025 1 24 1C11.2975 1 1 11.2975 1 24C1 36.7025 11.2975 47 24 47Z" fill="white"></path>
                                <path d="M24 1C11.2964 1 1 11.2964 1 24C1 35.4775 9.40298 44.9804 20.3846 46.7205L20.3936 30.6629H14.5151V24.009H20.3936C20.3936 24.009 20.3665 20.2223 20.3936 18.5363C20.4206 16.8503 20.7542 15.2274 21.6288 13.7487C22.9722 11.4586 25.0639 10.3407 27.6335 10.0251C29.7432 9.76362 31.826 10.0521 33.9087 10.3407C34.0529 10.3587 34.125 10.3767 34.2693 10.4038C34.2693 10.4038 34.2783 10.6472 34.2693 10.8005C34.2603 12.4053 34.2693 16.0839 34.2693 16.0839C33.2685 16.0659 31.6096 15.9667 30.5096 16.138C28.6884 16.4175 27.6425 17.5806 27.6064 19.4108C27.5704 20.8354 27.5884 24.009 27.5884 24.009H33.9988L32.962 30.6629H27.5974V46.7205C38.597 44.9984 47.009 35.4775 47.009 24C47 11.2964 36.7036 1 24 1Z" fill="#0075FA"></path>
                            </svg> Đăng nhập với Facebook</a>
                    </div>
                    <div class="text-center mt-3 small">Bạn chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký</a></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection