// document.addEventListener("DOMContentLoaded", function () {
//     const toastElList = [].slice.call(document.querySelectorAll('.toast'));
//     toastElList.forEach(function (toastEl) {
//         const delay = 2500; // mặc định 2.5s
//         const toast = new bootstrap.Toast(toastEl, {
//             delay: delay
//         });
//         toast.show();
//     });
// });
document.addEventListener('DOMContentLoaded', function () {
    var toastEls = document.querySelectorAll('.toast');

    if (toastEls.length === 0) return;

    // Nếu Bootstrap 5
    if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
        toastEls.forEach(function (el) {
            new bootstrap.Toast(el).show();
        });
    }
    // Nếu Bootstrap 4 (jQuery)
    else if (typeof $ === 'function' && typeof $(toastEls).toast === 'function') {
        $(toastEls).toast('show');
    }
    else {
        console.warn('Toast không thể hiển thị: Bootstrap 4 hoặc 5 chưa load.');
    }
});

