document.addEventListener('DOMContentLoaded', function () {
    const provinceName = $('#province');
    const wardName = $('#ward');

    // Khởi tạo Select2
    provinceName.select2({
        placeholder: "-- Chọn tỉnh thành --",
        allowClear: true,
        width: '100%' // full width
    });
    wardName.select2({
        placeholder: "-- Chọn xã/phường --",
        allowClear: true,
        width: '100%' // full width
    });

    // Gọi API
    fetch('https://provinces.open-api.vn/api/v2/?depth=2') // đường dẫn API của bạn
        .then(response => response.json())
        .then(data => {
            // let totalward = 0;
            // data.forEach(province => {
            //     totalward += province.wards.length;
            // });
            // console.log("Tổng số xã/phường:", totalward);
            data.forEach(item => {
                this.documentElement.querySelector('#province').innerHTML += `<option value="${item.code}">${item.name}</option>`;
            });
            // Cập nhật select2
            provinceName.trigger('change');
        })
        .catch(error => console.error('Lỗi khi lấy dữ liệu dropdown:', error));

    // Xử lý sự kiện khi chọn tỉnh/thành phố
    provinceName.on('change', function () {
        const selectedProvinceCode = $(this).val();
        // Xóa các tùy chọn hiện tại trong dropdown xã/phường
        wardName.empty().append('<option value=""></option>'); // Thêm tùy chọn mặc định
        if (selectedProvinceCode) {
            // Lấy dữ liệu xã/phường từ API dựa trên mã tỉnh/thành phố đã chọn
            fetch(`https://provinces.open-api.vn/api/v2/p/${selectedProvinceCode}?depth=2`)
                .then(response => response.json())
                .then(data => {
                    // console.log(data); // Kiểm tra dữ liệu nhận được từ API
                    data.wards.forEach(ward => {
                        wardName.append(`<option value="${ward.code} ">${ward.name}</option>`);
                    });
                    // Cập nhật select2
                    wardName.trigger('change');
                })
                .catch(error => console.error('Lỗi khi lấy dữ liệu xã/phường:', error));
        }
    });
});
