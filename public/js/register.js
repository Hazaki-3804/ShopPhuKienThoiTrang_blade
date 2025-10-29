document.addEventListener('DOMContentLoaded', function () {
  const provinceEl = document.getElementById('province');
  const wardEl = document.getElementById('ward');
  const addressEl = document.getElementById('address');
  let provinceName=document.getElementById('province').textContent;
  let wardName=document.getElementById('ward').textContent;

  // Init Choices.js (gõ trực tiếp để lọc, không ô tìm riêng trong dropdown)
  const provinceChoices = new Choices(provinceEl, {
    searchEnabled: true,
    placeholderValue: provinceEl.dataset.placeholder || '-- Chọn tỉnh thành --',
    searchPlaceholderValue: 'Gõ để lọc...',
    shouldSort: false,
    itemSelectText: '',
  });

  const wardChoices = new Choices(wardEl, {
    searchEnabled: true,
    placeholderValue: wardEl.dataset.placeholder || '-- Chọn xã/phường --',
    searchPlaceholderValue: 'Gõ để lọc...',
    shouldSort: false,
    itemSelectText: '',
  });

  // Gọi API lấy danh sách tỉnh + xã/phường (depth=2)
  fetch('https://provinces.open-api.vn/api/v2/?depth=2')
    .then(res => res.json())
    .then(data => {
      // Set provinces
      const provinceOptions = data.map(p => ({ value: String(p.code), label: p.name }));
      provinceChoices.setChoices(provinceOptions, 'value', 'label', true);

      // On province change -> set wards
      provinceEl.addEventListener('change', function () {
        const code = this.value;
        // Clear wards
        wardChoices.clearStore();
        wardChoices.clearChoices();
        wardEl.disabled = true;

        if (!code) return;

        const selected = data.find(p => String(p.code) === String(code));
        const wardOptions = (selected?.wards || []).map(w => ({ value: String(w.code), label: w.name }));
        wardChoices.setChoices(wardOptions, 'value', 'label', true);
        wardEl.disabled = false;
      });
    })
    .catch(err => console.error('Lỗi khi lấy dữ liệu dropdown:', err));
    function btn_loading(formId, btnId) {
        const form = document.getElementById(formId);
        const btn = document.getElementById(btnId);
        if (form && btn) {
            form.addEventListener('submit', function () {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Đăng ký';
            });
        }
    }
    btn_loading('registerForm', 'registerBtn');
    addressEl.addEventListener('blur', function () {
    provinceName = provinceEl.options[provinceEl.selectedIndex].textContent;
    wardName = wardEl.options[wardEl.selectedIndex].textContent;
    let addressValue = addressEl.value; // Dùng 'let' để có thể thay đổi giá trị

    if (provinceName && wardName && addressValue) {
        // 1. Loại bỏ wardName và provinceName khỏi addressValue (để tránh lặp lại)
        // Đây là regex để thoát các ký tự đặc biệt trong tên để dùng trong RegExp
        const escapedProvinceName = provinceName.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const escapedWardName = wardName.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        
        // Tạo RegExp để tìm và xóa không phân biệt chữ hoa/thường (case-insensitive)
        const provinceRegex = new RegExp(escapedProvinceName, 'gi');
        const wardRegex = new RegExp(escapedWardName, 'gi');

        // Xóa ward và province nếu đã có trong addressValue
        addressValue = addressValue.replace(provinceRegex, '');
        addressValue = addressValue.replace(wardRegex, '');

        // Chuẩn hóa và cắt bỏ khoảng trắng thừa
        addressValue = addressValue.replace(/\s+/g, ' ').trim();

        // 2. Thêm wardName và provinceName vào cuối addressValue
        // Sử dụng dấu phẩy và khoảng trắng để phân tách
        addressEl.value = `${addressValue}, ${wardName}, ${provinceName}`;
    }
});
      
});
