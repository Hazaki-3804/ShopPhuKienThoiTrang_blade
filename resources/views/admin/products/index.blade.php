@extends('layouts.admin')
@section('title', 'Products')

@section('content_header')
<h1>Products</h1>
@stop

@section('content')
<div class="card mb-3">
    <x-breadcrumbs :items="[
        ['label' => 'Quản lý sản phẩm', 'url' => route('products.index')],
        ['label' => 'Thêm sản phẩm', 'url' => null],
    ]" />
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Products</span>
        <input type="search" class="form-control form-control-sm" placeholder="Search product..." style="max-width: 220px;">
    </div>
    <div class="card-body table-responsive">
        <table class="table align-middle datatable" id="productsTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- DataTables will populate data here -->
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    (function() {
        // Preview
        const input = document.getElementById('imageInput');
        const preview = document.getElementById('imagePreview');
        if (input && preview) {
            input.addEventListener('change', (e) => {
                const file = e.target.files?.[0];
                if (!file) {
                    preview.removeAttribute('src');
                    return;
                }
                preview.src = URL.createObjectURL(file);
            });
        }
        $(document).ready(function() {
            $('#productsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("products.data") }}',
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'image',
                        name: 'image',
                        render: function(data) {
                            return `<img src="${data}" width="48" class="rounded">`;
                        }
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'category',
                        name: 'category.name'
                    },
                    {
                        data: 'price',
                        name: 'price'
                    },
                    {
                        data: 'stock',
                        name: 'stock'
                    },
                    {
                        data: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        });
    })();
</script>
@endpush
@endsection