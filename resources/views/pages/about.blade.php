@extends('layouts.app')
@section('title', 'Về chúng tôi')

@section('content')
<div class="container about-page py-4">
    <div class="row g-4 align-items-center">
        <div class="col-12 col-md-6 fade-up">
            <h2 class="fw-bold shop-title">Shop Nàng Thơ</h2>
            <p class="shop-desc">
                Shop Nàng Thơ là nơi mang đến cho bạn những phụ kiện thời trang pastel tối giản, tinh tế và hợp xu hướng.
                Từ túi xách, mũ, kính cho đến vòng tay, dây chuyền – tất cả đều được tuyển chọn kỹ lưỡng, phù hợp cho những cô nàng yêu phong cách nhẹ nhàng, thanh lịch.
                Chúng tôi cập nhật mẫu mới mỗi tuần để bạn luôn tự tin và tỏa sáng trong mọi khoảnh khắc.<br>
                Hãy đến với Shop Nàng Thơ để khám phá vẻ đẹp của sự đơn giản và tinh tế!
            </p>
        </div>
        <div class="col-12 col-md-6 fade-up">
            <div class="about-img-wrapper">
                <img src="{{ asset('img/gioithieu.jpg') }}" class="about-img" alt="Giới thiệu Shop Nàng Thơ">
            </div>
        </div>
    </div>
</div>

<style>
    /* --- VỀ CHÚNG TÔI PAGE CSS --- */
    .about-page {
        padding-top: 2rem;
        padding-bottom: 2rem;
    }

    /* Tiêu đề nổi bật màu cam đào, to hơn */
    .shop-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #FF8A65;
        /* cam đào */
        letter-spacing: 1px;
        margin-bottom: 1rem;
    }

    /* Nội dung mô tả */
    .shop-desc {
        font-size: 1.1rem;
        line-height: 1.8;
        color: #5c5c5c;
    }

    /* Ảnh giới thiệu bo góc, giữ tỉ lệ */
    .about-img-wrapper {
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: transform 0.4s ease, box-shadow 0.3s ease;
        width: 100%;
        height: auto;
    }

    .about-img-wrapper:hover {
        transform: scale(1.03);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    /* Đảm bảo ảnh fit cột, không bị méo */
    .about-img {
        width: 100%;
        height: auto;
        object-fit: cover;
        display: block;
    }
</style>
@endsection