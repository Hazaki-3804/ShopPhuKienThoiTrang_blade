<!-- View Product Modal -->
<div class="modal fade" id="viewProductModal" tabindex="-1" aria-labelledby="viewProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="viewProductModalLabel">
                    <i class="fas fa-eye mr-2"></i> Chi tiết sản phẩm
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>           
            </div>

            <div class="modal-body p-4">
                <div class="row">
                    <!-- Ảnh sản phẩm -->
                    <div class="col-md-5 text-center"> 
                        <h5 class="fw-bold mb-1 text-left"><i class="fas fa-image mr-1"></i>Ảnh sản phẩm:</h5>
                        <div class="border rounded shadow-sm p-2 bg-light">
                            <img id="view_image" src="" 
                                 alt="Ảnh sản phẩm" class="img-fluid rounded mb-2" 
                                 style="max-height: 320px; object-fit: contain;">
                        </div>
                    </div>

                    <!-- Thông tin sản phẩm -->
                    <div class="col-md-7">
                        <div class="mb-3 border-bottom pb-2">
                            <h5 class="fw-bold mb-1"><i class="fas fa-tag mr-1"></i>Tên sản phẩm:</h5> <span id="view_name"></span>
                            <p class="text-muted mb-0"><i class="fas fa-hashtag mr-1"></i>ID: <span id="view_id"></span></p>
                            <p class="text-muted mb-0"><i class="fas fa-list mr-1"></i> Danh mục: <span id="view_category"></span></p>

                        </div>

                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <span class="fw-bold">
                                    <i class="fas fa-money-bill-wave mr-1 text-warning"></i>Giá bán:
                                </span>
                                <p class="fw-bold mb-0" id="view_price"></p>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <span class="fw-bold">
                                    <i class="fas fa-boxes mr-1 text-success"></i>Tồn kho:
                                </span>
                                <p class="fw-bold mb-0" id="view_stock"></p>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <span class="fw-bold">
                                    <i class="fas fa-toggle-on mr-1 text-info"></i>Trạng thái:
                                </span>
                                <p class="fw-bold mb-0" id="view_status"></p>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <span class="fw-bold">
                                    <i class="fas fa-calendar-plus mr-1 text-muted"></i>Ngày tạo:
                                </span>
                                <p class="fw-bold mb-0" id="view_created"></p>
                            </div>
                        </div>

                        <div class="mt-3">
                            <span class="fw-bold">
                                <i class="fas fa-align-left mr-1 text-muted"></i>Mô tả:
                            </span>
                            <div class="border rounded p-3 bg-light mt-2" id="view_description" style="min-height: 80px;">
                                <em class="text-muted">Chưa có mô tả</em>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Đóng
                </button>
            </div>
        </div>
    </div>
</div>
