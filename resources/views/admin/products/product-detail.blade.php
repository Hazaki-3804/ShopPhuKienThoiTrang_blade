<!-- View Product Modal -->
<div class="modal fade" id="viewProductModal" tabindex="-1" aria-labelledby="viewProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewProductModalLabel">
                    <i class="fas fa-eye me-2"></i> Chi tiết sản phẩm
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>           
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-primary">
                                <i class="fas fa-tag me-1"></i> Tên sản phẩm:
                            </label>
                            <p class="form-control-plaintext border-bottom" id="view_name">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-primary">
                                <i class="fas fa-list me-1"></i> Danh mục:
                            </label>
                            <p class="form-control-plaintext border-bottom" id="view_category">-</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-success">
                                <i class="fas fa-money-bill-wave me-1"></i> Giá bán:
                            </label>
                            <p class="form-control-plaintext border-bottom text-success fw-bold" id="view_price">-</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-warning">
                                <i class="fas fa-boxes me-1"></i> Tồn kho:
                            </label>
                            <p class="form-control-plaintext border-bottom" id="view_stock">-</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-info">
                                <i class="fas fa-toggle-on me-1"></i> Trạng thái:
                            </label>
                            <p class="form-control-plaintext border-bottom" id="view_status">-</p>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-secondary">
                        <i class="fas fa-align-left me-1"></i> Mô tả:
                    </label>
                    <div class="border rounded p-3 bg-light" id="view_description" style="min-height: 80px;">
                        <em class="text-muted">Chưa có mô tả</em>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted">
                                <i class="fas fa-calendar-plus me-1"></i> Ngày tạo:
                            </label>
                            <p class="form-control-plaintext border-bottom" id="view_created">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted">
                                <i class="fas fa-hashtag me-1"></i> ID sản phẩm:
                            </label>
                            <p class="form-control-plaintext border-bottom" id="view_id">-</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Đóng
                </button>
                <button type="button" class="btn btn-warning" id="editFromView">
                    <i class="fas fa-edit me-1"></i>Chỉnh sửa
                </button>
            </div>
        </div>
    </div>
</div>