@extends('layouts.app')
@section('title', 'Đổi mật khẩu')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card card-hover">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Đổi mật khẩu</h5>
                    @if (session('status'))
                    <x-alert type="success">{{ session('status') }}</x-alert>
                    @endif
                    <form method="POST" action="{{ route('password.change.post') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu hiện tại</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu mới</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Xác nhận mật khẩu</label>
                            <input type="password" class="form-control" name="password_confirmation" required>
                        </div>
                        <button class="btn btn-brand w-100">Cập nhật</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection