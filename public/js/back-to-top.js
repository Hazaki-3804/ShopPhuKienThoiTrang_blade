document.addEventListener('DOMContentLoaded', function () {
    const scrollBtn = document.getElementById('scrollToTopBtn');
    if (!scrollBtn) return;

    // Hiện nút khi scroll xuống >200px
    const toggleVisibility = () => {
        if (window.scrollY > 200) {
            scrollBtn.classList.remove('d-none');
        } else {
            scrollBtn.classList.add('d-none');
        }
    };

    // Scroll lên đầu trang
    scrollBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Thêm hiệu ứng hover
    scrollBtn.addEventListener('mouseenter', () => {
        scrollBtn.style.backgroundColor = '#E64A19';
    });
    scrollBtn.addEventListener('mouseleave', () => {
        scrollBtn.style.backgroundColor = '#FF5722';
    });

    // Throttle scroll for performance
    let ticking = false;
    window.addEventListener('scroll', () => {
        if (!ticking) {
            window.requestAnimationFrame(() => {
                toggleVisibility();
                ticking = false;
            });
            ticking = true;
        }
    });

    // Initialize state on load
    toggleVisibility();
});
