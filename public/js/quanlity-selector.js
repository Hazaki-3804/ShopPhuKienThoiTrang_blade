function changeQty(id, delta) {
    let qtyInput = document.getElementById(id);
    let current = parseInt(qtyInput.value) || 1;
    let min = parseInt(qtyInput.min) || 1;
    let max = parseInt(qtyInput.max) || 99;
    let next = current + delta;
    if (next >= min && next <= max) {
        qtyInput.value = next;
    }
}