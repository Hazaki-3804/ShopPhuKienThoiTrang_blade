document.addEventListener("DOMContentLoaded", function () {
    var priceSlider = document.getElementById('price-slider');

    if (priceSlider) {
        noUiSlider.create(priceSlider, {
            start: [
                parseInt(document.getElementById('price_min').value) || 0,
                parseInt(document.getElementById('price_max').value) || 500000
            ],
            connect: true,
            step: 10000,
            range: {
                'min': 0,
                'max': 500000
            },
            format: {
                to: value => Math.round(value),
                from: value => Number(value)
            }
        });

        var priceMinInput = document.getElementById('price_min');
        var priceMaxInput = document.getElementById('price_max');
        var priceMinLabel = document.getElementById('price-min-label');
        var priceMaxLabel = document.getElementById('price-max-label');

        // Cập nhật giá trị khi kéo
        priceSlider.noUiSlider.on('update', function (values) {
            priceMinInput.value = values[0];
            priceMaxInput.value = values[1];
            if (priceMinLabel) priceMinLabel.textContent = new Intl.NumberFormat().format(values[0]) + "₫";
            if (priceMaxLabel) priceMaxLabel.textContent = new Intl.NumberFormat().format(values[1]) + "₫";
        });

        // Auto submit khi buông chuột
        priceSlider.noUiSlider.on('change', function () {
            document.getElementById('filterForm').submit();
        });
    }

    // Auto submit khi đổi sort
    var sortSelect = document.getElementById('sortSelect');
    if (sortSelect) {
        sortSelect.addEventListener('change', function () {
            document.getElementById('filterForm').submit();
        });
    }
});