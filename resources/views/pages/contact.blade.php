@extends('layouts.app')
@section('title', 'Liên hệ')

@section('content')
<div class="contact-page py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-peach">Liên hệ với Shop Nàng Thơ</h2>
            <p class="text-muted">Chúng tôi luôn sẵn sàng lắng nghe ý kiến, phản hồi và hỗ trợ bạn mọi lúc!</p>
        </div>

        <div class="row g-4 align-items-stretch">
            <!-- Form liên hệ -->
            <div class="col-12 col-lg-6 fade-up">
                <div class="contact-card p-4 h-100">
                    <h5 class="fw-semibold mb-3"><i class="bi bi-envelope-heart me-2 text-peach"></i>Gửi lời nhắn cho chúng tôi</h5>
                    <form class="row g-3" action="" method="post">
                        <div class="col-12 col-md-6">
                            <label class="form-label small">Họ và tên</label>
                            <input type="text" class="form-control custom-input" placeholder="Nguyễn Thảo Nhi">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small">Email</label>
                            <input type="email" class="form-control custom-input" placeholder="email@example.com">
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Nội dung</label>
                            <textarea class="form-control custom-input" rows="5" placeholder="Nhập nội dung bạn muốn gửi..."></textarea>
                        </div>
                        <div class="col-12 text-center mt-3">
                            <button type="submit" class="btn btn-peach px-5 py-2 fw-semibold shadow-sm"><i class='bi bi-send'></i> Gửi ngay</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Google Map -->
            <div class="col-12 col-lg-6 fade-up">
                <div class="contact-card p-3 h-100">
                    <div class="ratio ratio-16x9 rounded-4 overflow-hidden shadow-sm">
<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1624.9377343199574!2d105.96075671896484!3d10.25024926369076!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x310a82ce95555555%3A0x451cc8d95d6039f8!2zVHLGsOG7nW5nIMSQ4bqhaSBo4buNYyBTxrAgcGjhuqFtIEvhu7kgdGh14bqtdCBWxKluaCBMb25n!5e0!3m2!1svi!2s!4v1760859967564!5m2!1svi!2s" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>                    </div>
                    <div class="mt-3 text-center">
                        <p class="mb-1"><i class="bi bi-geo-alt-fill text-peach me-2"></i>Phường Long Châu, TP Vĩnh Long</p>
                        <p class="mb-1"><i class="bi bi-telephone-fill text-peach me-2"></i>0779089258</p>
                        <p><i class="bi bi-envelope-fill text-peach me-2"></i>shopnangtho@gmail.com</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* --- CONTACT PAGE --- */
    .contact-page {
        background: #fffdfe;
    }

    .text-peach {
        color: #FF8A65;
    }

    .btn-peach {
        background: var(--accent);
        color: #fff;
        border: none;
        border-radius: 30px;
        transition: all 0.3s ease;
    }

    .btn-peach:hover {
        color: #fff;
        background: var(--accent-hover);
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(255, 138, 101, 0.4);
    }

    .contact-card {
        background: #fff;
        border-radius: 1rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .contact-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    }

    .custom-input {
        border-radius: 10px;
        border: 1px solid #ffd4c4;
        transition: all 0.3s ease;
    }

    .custom-input:focus {
        border-color: #FF8A65;
        box-shadow: 0 0 0 0.15rem rgba(255, 138, 101, 0.25);
    }
</style>
@endsection
