@extends('adminlte::page')
@section('title', 'Products')

@section('content_header')
    <h1>Products</h1>
@stop

@section('content')
<div class="card mb-3">
    <div class="card-header fw-semibold">Add / Edit Product</div>
    <div class="card-body">
        <form class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label">Name</label>
                <input type="text" class="form-control" placeholder="Product name">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label">Category</label>
                <select class="form-select">
                    <option>Túi xách</option>
                    <option>Mũ</option>
                    <option>Kính</option>
                    <option>Vòng tay</option>
                    <option>Dây chuyền</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label">Price</label>
                <input type="number" class="form-control" placeholder="499000">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label">Image</label>
                <input type="file" class="form-control" id="imageInput" accept="image/*">
                <div class="mt-2">
                    <img id="imagePreview" class="rounded border" style="max-height:140px;" alt="Preview" />
                </div>
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea class="form-control" rows="3" placeholder="Mô tả"></textarea>
            </div>
            <div class="col-12">
                <button class="btn btn-brand">Save</button>
                <button class="btn btn-outline-secondary" type="reset">Reset</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Products</span>
        <input type="search" class="form-control form-control-sm" placeholder="Search product..." style="max-width: 220px;">
    </div>
    <div class="card-body table-responsive">
        <table class="table align-middle datatable" id="productsTable">
            <thead><tr><th>#</th><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach(range(1,10) as $i)
                <tr class="fade-up widget">
                    <td>{{ $i }}</td>
                    <td><img src="https://picsum.photos/seed/p{{ $i }}/64/64" class="rounded" width="48" height="48" alt=""></td>
                    <td>Item {{ $i }}</td>
                    <td>Accessory</td>
                    <td>{{ number_format(rand(199,599)*1000,0,',','.') }}₫</td>
                    <td>{{ rand(0,50) }}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-secondary">Edit</button>
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    (function(){
        // Preview
        const input = document.getElementById('imageInput');
        const preview = document.getElementById('imagePreview');
        if (input && preview) {
            input.addEventListener('change', (e)=>{
                const file = e.target.files?.[0];
                if (!file) { preview.removeAttribute('src'); return; }
                preview.src = URL.createObjectURL(file);
            });
        }
        // DataTable
        if (window.DataTable) { new DataTable('#productsTable'); }
    })();
</script>
@endpush
@endsection


