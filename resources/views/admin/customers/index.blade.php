@extends('adminlte::page')
@section('title', 'Customers')

@section('content_header')
    <h1>Customers</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Customers</span>
        <input type="search" class="form-control form-control-sm" placeholder="Search customer..." style="max-width: 220px;">
    </div>
    <div class="card-body table-responsive">
        <table class="table align-middle datatable" id="customersTable">
            <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Joined</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach(range(1,10) as $i)
                <tr class="fade-up widget">
                    <td>{{ $i }}</td>
                    <td>Khách {{ $i }}</td>
                    <td>user{{ $i }}@mail.com</td>
                    <td>09{{ rand(10,99) }} {{ rand(100,999) }} {{ rand(100,999) }}</td>
                    <td>{{ now()->subDays(rand(1,365))->format('d/m/Y') }}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-secondary">View</button>
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
    if (window.DataTable) { new DataTable('#customersTable'); }
</script>
@endpush
@endsection


