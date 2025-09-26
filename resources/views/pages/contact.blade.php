@extends('layouts.app')
@section('title', 'Liên hệ')

@section('content')
<div class="container">
    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <h4 class="fw-semibold">Liên hệ</h4>
            <form class="row g-3 mt-2" action="" method="post">
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
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3926.1917010919096!2d105.97476581078844!3d10.246117668708749!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x310a9df5e8e237af%3A0x51dcc880558ed77e!2sVincom%20Plaza%20V%C4%A9nh%20Long!5e0!3m2!1svi!2s!4v1758845636357!5m2!1svi!2s" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </div>
</div>

@endsection