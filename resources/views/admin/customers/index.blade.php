@extends('layouts.admin')
@section('title', 'Customers')
@section('content_header')
<span class="fw-semibold"></span>
@stop
@section('content')
<div class="shadow-sm rounded bg-white py-2">
    <!-- Header giống card-header -->
    <div class="d-flex justify-content-between align-items-center px-3">
        <h4 class="fw-semibold m-0">Danh sách khách hàng</h4>
        <x-admin.breadcrumbs :items="[['name' => 'Trang chủ'], ['name' => 'Quản lý khách hàng']]" />
    </div>

    <!-- Card con chứa table -->
    <div class="card m-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <!-- Trái -->
            <div class="flex-grow-1">
                <input type="search" id="customerSearch"
                    class="form-control form-control-sm"
                    placeholder="Tìm kiếm khách hàng..."
                    style="max-width: 220px;">
            </div>

            <!-- Phải -->
            <div class="d-flex align-items-center">
                <div class="mr-2">
                    <select id="statusFilter" class="form-control form-control-sm">
                        <option value="">-- Chọn trạng thái --</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

                <div class="dropdown">
                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="exportDropdown"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-file-export"></i> Export
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="exportDropdown">
                        <a class="dropdown-item" href="#" id="btn-excel"><i class="fas fa-file-excel"></i> Excel</a>
                        <a class="dropdown-item" href="#" id="btn-csv"><i class="fas fa-file-csv"></i> CSV</a>
                        <a class="dropdown-item" href="#" id="btn-pdf"><i class="fas fa-file-pdf"></i> PDF</a>
                        <a class="dropdown-item" href="#" id="btn-print"><i class="fas fa-print"></i> Print</a>
                    </div>
                </div>
            </div>
        </div>



        <div class="card-body">
            <div class="table-responsive no-scrollbar">
                <table class="table table-bordered table-striped align-middle w-100" id="customersTable">
                    <thead class="table-info">
                        <tr>
                            <th>ID</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Số điện thoại</th>
                            <th>Địa chỉ</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- use admin.modal component -->
<x-admin.modal
    modal_id='editCustomerModal'
    title="Thêm khách hàng"
    url="{{route('customers.store')}}"
    button_type="Thêm">
</x-admin.modal>
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('css/table.css') }}">
@endpush
@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#customersTable').DataTable({
            processing: false,
            serverSide: true,
            ajax: {
                url: "{{ route('customers.data') }}",
                type: 'GET',
                data: function(d) {
                    d.status = $('#statusFilter').val();
                }
            },
            dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
                "t" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [{
                    extend: 'excelHtml5',
                    bom: true,
                    charset: 'utf-8',
                    className: 'buttons-excel',
                    exportOptions: {
                        columns: ':visible:not(:last-child)'
                    }
                },
                {
                    extend: 'csvHtml5',
                    className: 'buttons-csv',
                    bom: true,
                    charset: 'utf-8',
                    exportOptions: {
                        columns: ':visible:not(:last-child)'
                    }
                },
                {
                    extend: 'pdfHtml5',
                    className: 'buttons-pdf',
                    exportOptions: {
                        columns: ':visible:not(:last-child)'
                    }
                },
            ],
            columns: [{
                    data: 'id',
                    name: 'id',
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'phone',
                    name: 'phone'
                },
                {
                    data: 'address',
                    name: 'address'
                },
                {
                    data: 'status_badge',
                    name: 'status',
                    orderable: true,
                    searchable: false,
                    render: function(data) {
                        return data;
                    }
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false
                }
            ],
            order: [
                [0, 'desc']
            ],
            columnDefs: [{
                className: 'text-center'
            }],
            responsive: true,
            language: {
                search: 'Tìm kiếm:',
                lengthMenu: 'Hiển thị _MENU_ khách hàng',
                info: 'Hiển thị _START_ đến _END_ trong tổng _TOTAL_ khách hàng',
                infoEmpty: 'Không có dữ liệu',
                zeroRecords: '🔍Không tìm thấy khách hàng',
                infoFiltered: '(lọc từ tổng _MAX_ khách hàng)',
                loadingRecords: '<i class="fas fa-spinner fa-spin"></i> Đang tải...',
                emptyTable: 'Không có khách hàng nào để hiển thị.',
                processing: '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...',
                paginate: {
                    previous: 'Trước',
                    next: 'Sau'
                }
            },
            drawCallback: function() {
                $('.dataTables_paginate').addClass('justify-content-end');
            },
            searching: true
        });
        let typingTimer;
        const typingDelay = 500; // 500ms delay

        $('#customerSearch').on('keyup', function() {
            clearTimeout(typingTimer);
            const value = this.value;

            typingTimer = setTimeout(function() {
                table.search(value).draw();
            }, typingDelay);
        });
        $('#statusFilter').on('change', function() {
            table.ajax.reload();
        });
        // Gán click dropdown -> gọi DataTables button
        $('#btn-excel').on('click', function(e) {
            e.preventDefault();
            table.button('.buttons-excel').trigger();
        });
        $('#btn-csv').on('click', function(e) {
            e.preventDefault();
            table.button('.buttons-csv').trigger();
        });
        $('#btn-pdf').on('click', function(e) {
            e.preventDefault();
            table.button('.buttons-pdf').trigger();
        });
        $('#btn-print').on('click', function(e) {
            e.preventDefault();
            table.button('.buttons-print').trigger();
        });
    });
</script>
@endpush