@extends('layouts.app')
@section('title', 'Về chúng tôi')

@section('content')
<div class="about-page">
    <div class="container">
        <div class="row align-items-center g-5">
            <!-- Cột trái -->
            <div class="col-12 col-md-6 fade-up">
                <h2 class="shop-title">Shop Nàng Thơ</h2>
                <p class="shop-quote">
                    “Nét đẹp giản dị – tinh tế – nhưng vẫn cuốn hút.”
                </p>
                <p class="shop-desc">
                    Shop Nàng Thơ mang đến những phụ kiện thời trang nhẹ nhàng và tinh tế,
                    từ túi xách, kính, cho đến vòng tay và dây chuyền.  
                    Tất cả đều được chọn lọc kỹ lưỡng để tôn lên vẻ đẹp thanh lịch và dịu dàng của bạn.  
                    <br><br>
                    Chúng tôi luôn cập nhật mẫu mới mỗi tuần để bạn tự tin tỏa sáng trong mọi khoảnh khắc.
                </p>
                <a href="{{ url('/shop') }}" class="btn btn-explore mt-3"><i class="bi bi-search-heart-fill"></i> Khám phá ngay</a>
            </div>

            <!-- Cột phải -->
            <div class="col-12 col-md-6 fade-up text-center">
                <div class="about-img-wrapper">
                    <img src="{{ asset('img/gioithieu.jpg') }}" class="about-img" alt="Giới thiệu Shop Nàng Thơ">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PHẦN 2: GIÁ TRỊ THƯƠNG HIỆU -->
<div class="brand-values py-5 text-center">
    <div class="container">
        <h3 class="fw-bold mb-4 text-uppercase text-muted">Giá trị mà Nàng Thơ mang lại</h3>
        <div class="row g-4">
            <div class="col-md-4">
                <i class="bi bi-gem fs-2 text-warning"></i>
                <h5 class="mt-3 fw-semibold">Tinh tế trong từng chi tiết</h5>
                <p>Chúng tôi chọn lọc sản phẩm tỉ mỉ, mang lại cảm giác thanh lịch và nữ tính.</p>
            </div>
            <div class="col-md-4">
                <i class="bi bi-heart fs-2 text-danger"></i>
                <h5 class="mt-3 fw-semibold">Yêu thương và tận tâm</h5>
                <p>Mỗi sản phẩm đều được gói ghém như món quà dành riêng cho bạn.</p>
            </div>
            <div class="col-md-4">
                <i class="bi bi-star fs-2 text-warning"></i>
                <h5 class="mt-3 fw-semibold">Phong cách riêng biệt</h5>
                <p>Shop Nàng Thơ giúp bạn thể hiện cá tính mà vẫn giữ nét nhẹ nhàng vốn có.</p>
            </div>
        </div>
    </div>
</div>

<!-- PHẦN 3: CÂU CHUYỆN CỦA CHÚNG TÔI -->
<div class="brand-story py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h3 class="fw-bold text-uppercase text-muted">Câu chuyện của chúng tôi</h3>
            <p class="text-secondary fst-italic">Hành trình nhỏ bé, lan tỏa niềm cảm hứng pastel dịu dàng</p>
        </div>

        <div class="timeline-modern">
            <div class="timeline-item fade-up">
                <div class="timeline-icon">
                    <i class="bi bi-flower1"></i>
                </div>
                <div class="timeline-content">
                    <span class="timeline-year">2022</span>
                    <h5>Khởi đầu</h5>
                    <p>Shop Nàng Thơ được thành lập với khát vọng mang đến phụ kiện pastel tinh tế và thanh lịch cho phái đẹp Việt.</p>
                </div>
            </div>

            <div class="timeline-item fade-up">
                <div class="timeline-icon">
                    <i class="bi bi-bag-heart"></i>
                </div>
                <div class="timeline-content">
                    <span class="timeline-year">2023</span>
                    <h5>Phát triển</h5>
                    <p>Ra mắt bộ sưu tập “Pastel Dreams” — đánh dấu bước ngoặt quan trọng trong hành trình khẳng định phong cách riêng.</p>
                </div>
            </div>

            <div class="timeline-item fade-up">
                <div class="timeline-icon">
                    <i class="bi bi-stars"></i>
                </div>
                <div class="timeline-content">
                    <span class="timeline-year">2024</span>
                    <h5>Lan tỏa</h5>
                    <p>Với hơn 10.000 khách hàng thân thiết, Shop Nàng Thơ tiếp tục mở rộng hệ thống online và mang cảm hứng pastel đến khắp Việt Nam.</p>
                </div>
            </div>
        </div>
    </div>
</div>


<style>
/* === PHẦN CHUNG === */
    .about-page {
        background: linear-gradient(135deg, #fffaf7 0%, #ffe6e6 100%);
        min-height: 80vh;
        display: flex;
        align-items: center;
        overflow: hidden;
    }
    .shop-title {
        font-size: 2.8rem;
        font-weight: 800;
        color: #FF8A65;
        position: relative;
        display: inline-block;
    }
    .shop-title::after {
        content: "";
        position: absolute;
        width: 60%;
        height: 3px;
        bottom: -8px;
        left: 0;
        background-color: #FFD1C1;
        border-radius: 2px;
    }
    .shop-quote {
        font-style: italic;
        color: #ff9e80;
        font-size: 1.2rem;
        margin-top: 0.75rem;
        margin-bottom: 1.25rem;
    }
    .shop-desc {
        color: #5f5f5f;
        font-size: 1.05rem;
        line-height: 1.8;
        text-align: justify;
    }
    .btn-explore {
        background-color: var(--accent);
        color: #fff;
        border-radius: 50px;
        padding: 0.6rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-explore:hover {
        background-color: var(--accent-hover);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 138, 101, 0.3);
    }
    .about-img-wrapper {
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 6px 25px rgba(255, 138, 101, 0.15);
        transition: all 0.4s ease;
        border: 6px solid #fff;
    }
    .about-img-wrapper:hover {
        transform: scale(1.03);
        box-shadow: 0 8px 35px rgba(255, 138, 101, 0.25);
    }
    .about-img {
        width: 100%;
        height: auto;
        object-fit: cover;
        display: block;
        transition: all 0.4s ease;
    }

    /* PHẦN 2: GIÁ TRỊ THƯƠNG HIỆU */
    .brand-values {
        background-color: #fffaf7;
    }
    .brand-values i {
        transition: transform 0.3s ease;
    }
    .brand-values i:hover {
        transform: scale(1.2);
    }

    /* === TIMELINE HIỆN ĐẠI === */
    .brand-story {
        background-color: #fffdfe;
    }
    .timeline-modern {
        position: relative;
        max-width: 900px;
        margin: auto;
        padding: 2rem 0;
    }
    .timeline-modern::before {
        content: "";
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 3px;
        height: 100%;
        background: linear-gradient(to bottom, #ffd4c6, #ff8a65);
        border-radius: 2px;
    }
    .timeline-item {
        position: relative;
        width: 50%;
        padding: 1.5rem 2rem;
    }
    .timeline-item:nth-child(odd) {
        left: 0;
        text-align: right;
    }
    .timeline-item:nth-child(even) {
        left: 50%;
    }
    .timeline-icon {
        position: absolute;
        top: 20px;
        right: -30px;
        width: 50px;
        height: 50px;
        background: #fff;
        border: 4px solid #ffbfa3;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ff8a65;
        font-size: 1.4rem;
        box-shadow: 0 4px 12px rgba(255, 138, 101, 0.2);
        transition: all 0.4s ease;
    }
    .timeline-item:nth-child(even) .timeline-icon {
        left: -30px;
        right: auto;
    }
    .timeline-icon:hover {
        transform: scale(1.15);
        background: #ff8a65;
        color: #fff;
    }

    /* Nội dung timeline */
    .timeline-content {
        background: #fff;
        border-radius: 1.25rem;
        padding: 1.5rem;
        box-shadow: 0 5px 15px rgba(255, 138, 101, 0.1);
        transition: all 0.4s ease;
    }
    .timeline-content:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(255, 138, 101, 0.2);
    }
    .timeline-year {
        display: inline-block;
        font-size: 0.9rem;
        color: #ff8a65;
        font-weight: 700;
        letter-spacing: 1px;
        background: #fff1ed;
        padding: 3px 10px;
        border-radius: 20px;
        margin-bottom: 0.5rem;
    }
    .timeline-content h5 {
        color: #ff7043;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    .timeline-content p {
        color: #555;
        margin: 0;
        line-height: 1.6;
    }

    /* Đường kết nối giữa các item */
    .timeline-item::after {
        content: "";
        position: absolute;
        top: 45px;
        right: -3px;
        width: 20px;
        height: 3px;
        background: #ffd4c6;
    }
    .timeline-item:nth-child(even)::after {
        left: -3px;
        right: auto;
    }

    /* Mobile responsive */
    @media (max-width: 767px) {
        .timeline-modern::before {
            left: 20px;
        }
        .timeline-item {
            width: 100%;
            padding-left: 60px;
            text-align: left !important;
        }
        .timeline-icon {
            left: 0 !important;
            right: auto;
        }
        .timeline-item::after {
            display: none;
        }
    }
</style>
@endsection
