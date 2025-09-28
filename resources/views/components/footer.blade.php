<footer class="footer pt-5 border-top">
    <div class="container">
        <div class="row gy-4">

            <!-- About -->
            <div class="col-lg-4 col-md-6">
                <a href="/" class="d-flex align-items-center mb-3 text-decoration-none">
                    <span class="h4 fw-bold brand-name">Nàng Thơ</span>
                </a>
                <p class="mb-2 text-muted">Phường 8, TP Vĩnh Long</p>
                <p class="mb-2"><strong>Phone:</strong> 0779089258</p>
                <p><strong>Email:</strong> shopnangtho@gmail.com</p>
            </div>

            <!-- Useful Links -->
            <div class="col-lg-2 col-md-6">
                <h5 class="fw-bold mb-3 section-title">Liên kết</h5>
                <ul class="list-unstyled">
                    <li><a href="/" class="footer-link d-block py-1"><i class="bi bi-chevron-right"></i> Trang chủ</a></li>
                    <li><a href="{{ route('shop.index') }}" class="footer-link d-block py-1"><i class="bi bi-chevron-right"></i> Sản phẩm</a></li>
                    <li><a href="{{ route('about') }}" class="footer-link d-block py-1"><i class="bi bi-chevron-right"></i> Giới thiệu</a></li>
                    <li><a href="{{ route('contact') }}" class="footer-link d-block py-1"><i class="bi bi-chevron-right"></i> Liên hệ</a></li>
                </ul>
            </div>

            <!-- Our Services -->
            <div class="col-lg-2 col-md-6">
                <h5 class="fw-bold mb-3 section-title">Dịch vụ</h5>
                <ul class="list-unstyled">
                    <li><span class="d-block py-1 text-muted"><i class="bi bi-chevron-right"></i> Giao hàng nhanh</span></li>
                    <li><span class="d-block py-1 text-muted"><i class="bi bi-chevron-right"></i> Đổi trả dễ dàng</span></li>
                    <li><span class="d-block py-1 text-muted"><i class="bi bi-chevron-right"></i> Hỗ trợ 24/7</span></li>
                </ul>
            </div>

            <!-- Social -->
            <div class="col-lg-4 col-md-6">
                <h5 class="fw-bold mb-3 section-title">Kết nối với chúng tôi</h5>
                <p class="text-muted">Theo dõi các kênh mạng xã hội để cập nhật ưu đãi mới nhất.</p>
                <div class="d-flex gap-3">
                    <a href="#" class="social-btn instagram" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="social-btn facebook" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="social-btn youtube" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                    <a href="#" class="social-btn tiktok" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
                </div>
            </div>

        </div>

        <!-- Bottom -->
        <hr class="mt-4 footer-hr">
        <div class="text-center py-3">
            <small>© 2025 <span class="fw-bold brand-name">Shop Nàng Thơ</span>. All Rights Reserved.</small>
        </div>
    </div>
</footer>
<!-- End Footer -->
<style>
    .footer {
        background: var(--accent-100);
        color: var(--text);
    }
    .brand-name { color: var(--accent); }
    .section-title { color: var(--text); }
    .footer-hr { border: 0; border-top: 1px solid rgba(0,0,0,0.06); }
    .footer-link { color: var(--text); text-decoration: none; }
    .footer-link:hover { color: var(--accent); text-decoration: underline; }
    /* Social circular buttons */
    .social-btn {
        width: 36px; height: 36px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        color: #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.12);
        transition: transform .2s ease, filter .2s ease, box-shadow .2s ease;
    }
    .social-btn i { font-size: 1.1rem; line-height: 1; }
    .social-btn:hover { transform: translateY(-2px); filter: brightness(0.95); box-shadow: 0 4px 10px rgba(0,0,0,0.16); }
    /* Brand backgrounds */
    .social-btn.facebook { background-color: #1877F2; }
    .social-btn.youtube { background-color: #FF0000; }
    .social-btn.tiktok { background-color: #000000; }
    /* Instagram gradient */
    .social-btn.instagram {
        background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%);
    }
    .footer .bi { transition: transform 0.2s ease; }
</style>