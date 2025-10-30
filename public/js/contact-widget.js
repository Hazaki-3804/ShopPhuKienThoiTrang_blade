document.addEventListener('DOMContentLoaded', function() {
    const contactToggle = document.getElementById('contact-toggle');
    const contactWidgetWrapper = document.querySelector('.contact-widget-wrapper');
    const contactBackdrop = document.createElement('div');
    contactBackdrop.classList.add('contact-backdrop');
    document.body.appendChild(contactBackdrop);

    function toggleContactWidget() {
        contactWidgetWrapper.classList.toggle('active');
        // Đồng bộ trạng thái backdrop với widget
        if (contactWidgetWrapper.classList.contains('active')) {
            contactBackdrop.classList.add('active');
            //Đổi icon thành icon close
            contactToggle.innerHTML = '<i class="bi bi-x-lg text-white fs-5"></i>';
        } else {
            contactBackdrop.classList.remove('active');
            //Đổi icon thành icon open
            contactToggle.innerHTML = '<i class="bi bi-envelope-paper fw-bold text-white fs-4"></i>';
        }
    }

    contactToggle.addEventListener('click', toggleContactWidget);

    // Đóng widget khi click ra ngoài hoặc vào backdrop
    contactBackdrop.addEventListener('click', function() {
        if (contactWidgetWrapper.classList.contains('active')) {
            toggleContactWidget();
        }
    });

    // Thêm xử lý để đóng khi bấm ESC (tùy chọn)
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && contactWidgetWrapper.classList.contains('active')) {
            toggleContactWidget();
        }
    });
});