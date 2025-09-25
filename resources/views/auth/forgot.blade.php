@extends('layouts.app')
@section('title', 'Quên mật khẩu')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card card-hover">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Quên mật khẩu</h5>
                    @if (session('status'))
                        <x-alert type="success">{{ session('status') }}</x-alert>
                    @endif
                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <button class="btn btn-brand w-100">Gửi liên kết</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


