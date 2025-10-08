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

// Function to show dynamic toast notifications
function showToast(message, type = 'warning') {
    const toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) return;

    const bgClass = {
        'success': 'toast-success-light',
        'error': 'toast-error-light',
        'info': 'toast-info-light',
        'warning': 'toast-warning-light'
    }[type] || 'toast-warning-light';

    const icon = {
        'success': '✅',
        'error': '❌',
        'info': 'ℹ️',
        'warning': '⚠️'
    }[type] || '⚠️';

    const delay = {
        'success': 2500,
        'info': 3000,
        'warning': 3500,
        'error': 4000
    }[type] || 3500;

    const toastHtml = `
        <div class="toast align-items-center ${bgClass} p-2 border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="${delay}">
            <div class="d-flex">
                <div class="toast-body">
                    <span class="me-2">${icon}</span> ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    const newToast = toastContainer.lastElementChild;
    const bsToast = new bootstrap.Toast(newToast, { delay: delay });
    bsToast.show();

    // Remove toast element after it's hidden
    newToast.addEventListener('hidden.bs.toast', function () {
        newToast.remove();
    });
}
