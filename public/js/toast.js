document.addEventListener("DOMContentLoaded", function () {
    const toastElList = [].slice.call(document.querySelectorAll('.toast'));
    toastElList.forEach(function (toastEl) {
        const delay = 2500; // mặc định 2.5s
        const toast = new bootstrap.Toast(toastEl, {
            delay: delay
        });
        toast.show();
    });
});
