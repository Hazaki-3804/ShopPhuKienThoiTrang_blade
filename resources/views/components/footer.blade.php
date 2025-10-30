<footer class="footer">
    <div class="footer-main">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-4 col-md-6">
                    <div class="footer-brand">
                        <a href="/" class="d-flex align-items-center gap-2 mb-3 text-decoration-none">
                            <img src="{{ asset($site_settings['site_logo']??'img/logo_shop.png') }}" alt="Logo" class="footer-logo">
                            <span class="h4 fw-bold brand-name mb-0">{{ $site_settings['site_name']??'Shop Nàng Thơ' }}</span>
                        </a>
                        <p class="footer-desc mb-3">{{ $site_settings['site_description']??'Phụ kiện thời trang cao cấp' }}</p>
                        <div class="footer-contact">
                            <div class="contact-item">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>{{ $site_settings['contact_address'] }}</span>
                            </div>
                            <div class="contact-item">
                                <i class="bi bi-telephone-fill"></i>
                                <a href="tel:{{ $site_settings['contact_phone'] }}">{{ $site_settings['contact_phone'] }}</a>
                            </div>
                            <div class="contact-item">
                                <i class="bi bi-envelope-fill"></i>
                                <a href="mailto:{{ $site_settings['contact_email'] }}">{{ $site_settings['contact_email'] }}</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h5 class="footer-title mt-2">Về chúng tôi</h5>
                    <ul class="footer-links">
                        <li><a href="/"><i class="bi bi-chevron-right"></i> Trang chủ</a></li>
                        <li><a href="{{ route('shop.index') }}"><i class="bi bi-chevron-right"></i> Sản phẩm</a></li>
                        <li><a href="{{ route('about') }}"><i class="bi bi-chevron-right"></i> Giới thiệu</a></li>
                        <li><a href="{{ route('contact') }}"><i class="bi bi-chevron-right"></i> Liên hệ</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h5 class="footer-title mt-2">Chính sách</h5>
                    <ul class="footer-links">
                        <li><a href="#"><i class="bi bi-chevron-right"></i> Chính sách đổi trả</a></li>
                        <li><a href="#"><i class="bi bi-chevron-right"></i> Chính sách bảo mật</a></li>
                        <li><a href="#"><i class="bi bi-chevron-right"></i> Điều khoản dịch vụ</a></li>
                        <li><a href="#"><i class="bi bi-chevron-right"></i> Hướng dẫn mua hàng</a></li>
                    </ul>
                </div>

                <div class="col-lg-4 col-md-6">
                    <h5 class="footer-title mt-2">Nhận tin khuyến mãi</h5>
                    <p class="footer-desc mb-3">Đăng ký để nhận ưu đãi độc quyền và xu hướng mới nhất</p>
                    <form class="newsletter-form mb-4" onsubmit="event.preventDefault(); alert('Cảm ơn bạn đã đăng ký!')">
                        <div class="input-group">
                            <input type="email" class="form-control newsletter-input" placeholder="Email của bạn..." required>
                            <button class="btn newsletter-btn" type="submit">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </div>
                    </form>
                    <div class="social-links mb-4">
                        <a href="{{ $site_settings['contact_facebook'] }}" class="social-btn facebook" aria-label="Facebook">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="{{ $site_settings['contact_instagram'] }}" class="social-btn instagram" aria-label="Instagram">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="{{ $site_settings['contact_youtube'] }}" class="social-btn youtube" aria-label="YouTube">
                            <i class="bi bi-youtube"></i>
                        </a>
                        <a href="{{ $site_settings['contact_tiktok'] }}" class="social-btn tiktok" aria-label="TikTok">
                            <i class="bi bi-tiktok"></i>
                        </a>
                    </div>
                    <div class="payment-methods-footer">
                        <p class="small fw-semibold mb-2 text-muted">Thanh toán</p>
                        <div class="payment-logos">
                            <div class="payment-logo-item" title="Thanh toán khi nhận hàng">
                                <i class="bi bi-cash-coin"></i>
                            </div>
                            <div class="payment-logo-item" title="Ví điện tử MoMo">
                                <img src="https://homepage.momocdn.net/fileuploads/svg/momo-file-240411162904.svg" alt="MoMo">
                            </div>
                            <div class="payment-logo-item" title="Ví điện tử PayOS">
                                <img src="https://payos.vn/docs/img/logo.svg" alt="PayOS">
                            </div>
                            <div class="payment-logo-item" title="Ví điện tử VNPAY">
                                <img src="https://stcd02206177151.cloud.edgevnpay.vn/assets/images/logo-icon/logo-primary.svg" alt="VNPAY">
                            </div>
                            <div class="payment-logo-item" title="Chuyển khoản SePay">
                                <img src="https://sepay.vn//assets/img/logo/sepay-blue-154x50.png" alt="SePay">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                    <p class="mb-0">
                        <small>© 2025 <span class="fw-semibold brand-name">Shop Nàng Thơ</span>. All Rights Reserved.</small>
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="mb-0">
                        <small class="text-muted">Thiết kế bởi <i class="bi bi-heart-fill text-danger pulse-heart"></i> Team Shop Nàng Thơ</small>
                    </p>
                </div>
            </div>
        </div>
    </div>
</footer>