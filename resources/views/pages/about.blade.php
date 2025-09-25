@extends('layouts.app')
@section('title', 'Về chúng tôi')

@section('content')
<div class="container">
    <div class="row g-4">
        <div class="col-12 col-md-6">
            <h4 class="fw-semibold">Câu chuyện Fasho</h4>
            <p class="text-muted">Chúng tôi yêu sự tối giản và bảng màu pastel. Mục tiêu là mang đến phụ kiện tinh tế, trẻ trung cho mọi người.</p>
        </div>
        <div class="col-12 col-md-6">
            <div class="ratio ratio-16x9 rounded-3 overflow-hidden bg-light">
                <img src="https://picsum.photos/1200/800?grayscale" class="w-100 h-100 object-fit-cover" alt="Team">
            </div>
        </div>
    </div>
</div>
@endsection


