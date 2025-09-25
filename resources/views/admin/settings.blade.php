@extends('adminlte::page')
@section('title', 'Settings')

@section('content_header')
    <h1>Settings</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header fw-semibold">Cấu hình chung</div>
    <div class="card-body">
        <form class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label">Tên shop</label>
                <input type="text" class="form-control" placeholder="Fasho">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label">Email shop</label>
                <input type="email" class="form-control" placeholder="hello@fasho.com">
            </div>
            <div class="col-12">
                <label class="form-label">Mô tả</label>
                <textarea class="form-control" rows="3"></textarea>
            </div>
            <div class="col-12">
                <button class="btn btn-brand">Lưu</button>
            </div>
        </form>
    </div>
</div>
@endsection


