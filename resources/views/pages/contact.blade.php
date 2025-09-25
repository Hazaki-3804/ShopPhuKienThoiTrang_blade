@extends('layouts.app')
@section('title', 'Liên hệ')

@section('content')
<div class="container">
    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <h4 class="fw-semibold">Liên hệ</h4>
            <form class="row g-3 mt-2">
                <div class="col-12 col-md-6">
                    <input type="text" class="form-control" placeholder="Họ và tên">
                </div>
                <div class="col-12 col-md-6">
                    <input type="email" class="form-control" placeholder="Email">
                </div>
                <div class="col-12">
                    <textarea class="form-control" rows="5" placeholder="Nội dung"></textarea>
                </div>
                <div class="col-12">
                    <button class="btn btn-brand">Gửi</button>
                </div>
            </form>
        </div>
        <div class="col-12 col-lg-6">
            <div class="ratio ratio-16x9 rounded-3 overflow-hidden">
                <iframe src="https://www.google.com/maps?q=Ho%20Chi%20Minh&output=embed" style="border:0; width:100%; height:100%;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
            <div class="mt-2 d-flex gap-3">
                <a href="#" class="text-muted"><i class="bi bi-facebook"></i></a>
                <a href="#" class="text-muted"><i class="bi bi-instagram"></i></a>
                <a href="#" class="text-muted"><i class="bi bi-twitter-x"></i></a>
            </div>
        </div>
    </div>
</div>
@endsection


