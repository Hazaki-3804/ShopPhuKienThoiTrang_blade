@extends('adminlte::page')
@section('title', 'Orders')

@section('content_header')
    <h1>Orders</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Orders</span>
        <div class="d-flex gap-2">
            <input type="search" class="form-control form-control-sm" placeholder="Search order..." style="max-width: 220px;">
            <button class="btn btn-outline-secondary btn-sm">Filter</button>
        </div>
    </div>
    <div class="card-body table-responsive">
        <table class="table align-middle datatable" id="ordersTable">
            <thead><tr><th>#</th><th>Customer</th><th>Status</th><th>Total</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach(range(1,10) as $i)
                <tr class="fade-up widget">
                    <td>#ORD{{ 1200 + $i }}</td>
                    <td>Khách {{ $i }}</td>
                    <td>
                        <select class="form-select form-select-sm" style="min-width:130px;">
                            <option>Pending</option>
                            <option selected>Processing</option>
                            <option>Completed</option>
                            <option>Cancelled</option>
                        </select>
                    </td>
                    <td>{{ number_format(rand(199,999)*1000,0,',','.') }}₫</td>
                    <td>{{ now()->subDays($i)->format('d/m/Y') }}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-secondary">View</button>
                        <button class="btn btn-sm btn-outline-secondary">Update</button>
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
    if (window.DataTable) { new DataTable('#ordersTable'); }
</script>
@endpush
@endsection


