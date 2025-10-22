@extends('layouts.app')
@section('title','Chi tiết đơn #'.$order->id)

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Đơn #{{ $order->id }}</h5>
    <a href="{{ route('user.orders.index') }}" class="btn btn-sm btn-outline-secondary">← Quay lại</a>
  </div>

  <div class="row g-3">
    <div class="col-12 col-lg-8">
      <div class="card shadow-sm border-0 rounded-3 mb-3">
        <div class="card-body">
          <div class="d-flex justify-content-between flex-wrap gap-2">
            <div>
              @php
                $badge = 'bg-' . ($order->status_class ?? 'secondary');
                $textFix = in_array(($order->status_class ?? ''), ['warning','light']) ? 'text-dark' : '';
              @endphp
              <div class="fw-semibold">Trạng thái: <span class="badge {{ $badge }} {{ $textFix }}">{{ $order->status_text }}</span></div>
              <div class="small text-muted">Tạo lúc: {{ $order->created_at->format('d/m/Y H:i') }}</div>
            </div>
            @if($canCancel)
            <form method="POST" action="{{ route('user.orders.cancel', $order) }}" id="cancelForm-show-{{ $order->id }}">
              @csrf
              @method('PATCH')
              <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal-show-{{ $order->id }}">Hủy đơn</button>
            </form>
            <!-- Modal xác nhận hủy -->
            <div class="modal fade" id="cancelModal-show-{{ $order->id }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header">
                    <h6 class="modal-title">Xác nhận hủy đơn</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">Bạn có chắc muốn hủy đơn hàng #{{ $order->id }}? Hành động này không thể hoàn tác.</div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-danger" data-cancel-order-show="{{ $order->id }}">Đồng ý hủy</button>
                  </div>
                </div>
              </div>
            </div>
            @push('scripts')
            <script>
              document.addEventListener('DOMContentLoaded', function(){
                const btn = document.querySelector('[data-cancel-order-show="{{ $order->id }}"]');
                if(btn){
                  btn.addEventListener('click', function(){
                    const form = document.getElementById('cancelForm-show-{{ $order->id }}');
                    if(form) form.submit();
                  });
                }
              });
            </script>
            @endpush
            @endif
          </div>

          <hr>
          @php $subtotal = 0; @endphp
          @foreach($order->order_items as $it)
            @php
              $p = $it->product;
              $img = optional($p->product_images->first())->image_url ?? null;
              if ($img && !\Illuminate\Support\Str::startsWith($img, ['http://','https://','/'])) { $img = asset($img); }
              $img = $img ?: 'https://picsum.photos/64/64?random=' . ($p->id ?? 1);
              $lineTotal = (int)$it->price * (int)$it->quantity;
              $subtotal += $lineTotal;
            @endphp
            <div class="d-flex align-items-center gap-3 py-2 border-bottom">
              <img src="{{ $img }}" class="rounded border" style="width:56px;height:56px;object-fit:cover;" alt="{{ $p->name ?? '' }}">
              <div class="flex-grow-1">
                <div class="fw-semibold">{{ $p->name ?? 'Sản phẩm' }}</div>
                <div class="small text-muted">Số lượng: {{ $it->quantity }}</div>
              </div>
              <div class="text-end" style="min-width:120px;">
                <div class="small text-muted">đơn giá: {{ number_format($it->price,0,',','.') }}₫</div>
                <div class="fw-semibold text-nowrap">{{ number_format($lineTotal,0,',','.') }}₫</div>
              </div>
              @if($order->status === 'delivered' && $p)
              <div class="ms-3">
                <a class="btn btn-sm btn-outline-success" href="{{ route('shop.show', $p->id) }}#review">Đánh giá</a>
              </div>
              @endif
            </div>
          @endforeach

          @php
            $subtotal = (int)$subtotal;
            $grand = (int)$order->total_price;
            $adjustment = $grand - $subtotal; // gồm phí ship và/hoặc giảm giá nếu có
          @endphp
          <div class="mt-3">
            <div class="d-flex justify-content-between py-1">
              <div class="text-muted">Tạm tính</div>
              <div>{{ number_format($subtotal,0,',','.') }}₫</div>
            </div>
            <div class="d-flex justify-content-between py-1">
              <div class="text-muted">Điều chỉnh (phí ship/giảm giá)</div>
              <div class="{{ $adjustment>=0 ? '' : 'text-success' }}">{{ $adjustment>=0 ? '+' : '-' }}{{ number_format(abs($adjustment),0,',','.') }}₫</div>
            </div>
            <hr class="my-2">
            <div class="d-flex justify-content-between align-items-center">
              <div class="fw-semibold">Tổng thanh toán</div>
              <div class="fw-bold text-danger">{{ number_format($grand,0,',','.') }}₫</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-4">
      <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">
          <h6 class="fw-semibold mb-2">Thông tin giao hàng</h6>
          <div class="small text-muted">Người nhận</div>
          <div class="mb-1">{{ $order->customer_name }}</div>
          <div class="small text-muted">Liên hệ</div>
          <div class="mb-1">{{ $order->customer_phone }} • {{ $order->customer_email }}</div>
          <div class="small text-muted">Địa chỉ</div>
          <div>{{ $order->shipping_address }}</div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
