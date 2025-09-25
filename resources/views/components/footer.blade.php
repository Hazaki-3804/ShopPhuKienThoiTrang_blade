<footer class="border-top mt-5 py-4 bg-white">
    <div class="container">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <h6 class="fw-semibold">Fasho</h6>
                <p class="text-muted small">Phụ kiện thời trang với sắc pastel hiện đại, tối giản.</p>
            </div>
            <div class="col-6 col-md-4">
                <h6 class="fw-semibold">Quick Links</h6>
                <ul class="list-unstyled small">
                    <li><a class="text-decoration-none" href="{{ route('shop.index') }}">Shop</a></li>
                    <li><a class="text-decoration-none" href="{{ route('about') }}">About</a></li>
                    <li><a class="text-decoration-none" href="{{ route('contact') }}">Contact</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-4">
                <h6 class="fw-semibold">Newsletter</h6>
                <form class="d-flex gap-2">
                    <input type="email" class="form-control" placeholder="Email của bạn">
                    <button class="btn btn-brand">Subscribe</button>
                </form>
                <div class="mt-2 small text-muted">Theo dõi ưu đãi mới nhất.</div>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-4 small text-muted">
            <span>&copy; {{ date('Y') }} Fasho</span>
            <div class="d-flex gap-3">
                <a href="#" aria-label="Facebook" class="text-muted"><i class="bi bi-facebook"></i></a>
                <a href="#" aria-label="Instagram" class="text-muted"><i class="bi bi-instagram"></i></a>
                <a href="#" aria-label="Twitter" class="text-muted"><i class="bi bi-twitter-x"></i></a>
            </div>
        </div>
    </div>
    <!-- Comment: Footer đơn giản + newsletter + social icons -->
</footer>


