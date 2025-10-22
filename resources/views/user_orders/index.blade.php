@extends('layouts.app')
@section('title', 'Đơn mua của tôi')

@section('content')
<div class="container py-3 py-md-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-semibold">Đơn mua</h5>
        <a href="{{ route('user.orders.index') }}" class="text-decoration-none small">Xem lịch sử mua hàng →</a>
    </div>

    <!-- Status quick actions -->
    <div class="card shadow-sm border-0 rounded-3 mb-3">
        <div class="card-body">
            <div class="row text-center g-3 g-md-4 order-quick-actions">
                <div class="col-3">
                    <a class="d-block text-decoration-none text-body qa-tile qa-pending" href="{{ route('user.orders.index', ['status' => 'pending']) }}">
                        <div class="icon-wrap"><i class="bi bi-clipboard-check"></i></div>
                        <div class="small mt-1">Chờ xác nhận</div>
                        <div class="fw-bold">{{ $counts['pending'] ?? 0 }}</div>
                    </a>
                </div>
                <div class="col-3">
                    <a class="d-block text-decoration-none text-body qa-tile qa-processing" href="{{ route('user.orders.index', ['status' => 'processing']) }}">
                        <div class="icon-wrap"><i class="bi bi-box-seam"></i></div>
                        <div class="small mt-1">Chờ lấy hàng</div>
                        <div class="fw-bold">{{ $counts['processing'] ?? 0 }}</div>
                    </a>
                </div>
                <div class="col-3">
                    <a class="d-block text-decoration-none text-body qa-tile qa-shipped" href="{{ route('user.orders.index', ['status' => 'shipped']) }}">
                        <div class="icon-wrap"><i class="bi bi-truck"></i></div>
                        <div class="small mt-1">Chờ giao hàng</div>
                        <div class="fw-bold">{{ $counts['shipped'] ?? 0 }}</div>
                    </a>
                </div>
                <div class="col-3">
                    <a class="d-block text-decoration-none text-body qa-tile qa-delivered" href="{{ route('user.orders.index', ['status' => 'delivered']) }}">
                        <div class="icon-wrap"><i class="bi bi-star"></i></div>
                        <div class="small mt-1">Đánh giá</div>
                        <div class="fw-bold">{{ $reviewableCount }}</div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter tabs -->
    <ul class="nav nav-pills gap-2 flex-wrap mb-3">
        @php $map = $statusMap; @endphp
        <li class="nav-item"><a class="nav-link {{ !$currentStatus ? 'active' : '' }}" href="{{ route('user.orders.index') }}">Tất cả ({{ $counts['all'] ?? 0 }})</a></li>
        @foreach($map as $key => $label)
            <li class="nav-item">
                <a class="nav-link pill-{{ $key }} {{ $currentStatus === $key ? 'active' : '' }}" href="{{ route('user.orders.index', ['status' => $key]) }}">{{ $label }} ({{ $counts[$key] ?? 0 }})</a>
            </li>
        @endforeach
    </ul>

    <!-- Orders list -->
    @forelse($orders as $order)
    <div class="card border-0 shadow-sm rounded-3 mb-2">
        <div class="card-body">
            <div class="d-flex justify-content-between flex-wrap gap-2">
                <div>
                    <div class="fw-semibold">Đơn #{{ $order->id }}</div>
                    <div class="text-muted small">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                </div>
                <div class="align-self-center">
                    @php
                        $badge = 'bg-' . ($order->status_class ?? 'secondary');
                        $textFix = in_array(($order->status_class ?? ''), ['warning','light']) ? 'text-dark' : '';
                    @endphp
                    <span class="badge {{ $badge }} {{ $textFix }}">{{ $order->status_text }}</span>
                </div>
            </div>
            <div class="mt-2">
                @foreach(($order->order_items ?? []) as $idx => $it)
                    @break($idx>=3)
                    @php
                        $p = $it->product;
                        $img = optional($p->product_images->first())->image_url ?? null;
                        if ($img && !\Illuminate\Support\Str::startsWith($img, ['http://','https://','/'])) { $img = asset($img); }
                        $img = $img ?: 'https://picsum.photos/64/64?random=' . ($p->id ?? 1);
                    @endphp
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <img src="{{ $img }}" class="rounded border" style="width:40px;height:40px;object-fit:cover;" alt="{{ $p->name ?? '' }}">
                        <div class="small flex-grow-1 text-truncate">{{ $p->name ?? 'Sản phẩm' }} × {{ $it->quantity }}</div>
                        <div class="small text-nowrap">{{ number_format($it->price,0,',','.') }}₫</div>
                    </div>
                @endforeach
            </div>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <div class="text-muted small">Tổng tiền</div>
                <div class="fw-bold text-danger">{{ number_format($order->total_price,0,',','.') }}₫</div>
            </div>
            <div class="mt-2 d-flex justify-content-between align-items-center gap-2">
              <div class="d-flex gap-2">
                <div class="small text-muted">Thanh toán:</div>
                @php
                    $badge = 'bg-' . ($order->payment && $order->payments->status==='completed'?'success':'danger');
                @endphp
                <span class="badge {{ $badge }}">{{$order->payment && $order->payments->status==='completed'?'Đã thanh toán':'Chưa thanh toán' }}</span>
              </div>
              <div class="d-flex gap-2">
                <a href="{{ route('user.orders.show', $order) }}" class="btn btn-sm btn-secondary">Chi tiết</a>
                @if($order->payments && $order->payments->status!=='completed' && in_array($order->status, ['pending','processing']))
                <form method="POST" action="{{ route('user.orders.cancel', $order) }}" id="cancelForm-{{ $order->id }}">
                    @csrf
                    @method('PATCH')
                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal-{{ $order->id }}">Hủy đơn</button>
                </form>

                <!-- Modal xác nhận hủy -->
                <div class="modal fade" id="cancelModal-{{ $order->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h6 class="modal-title">Xác nhận hủy đơn</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        Bạn có chắc muốn hủy đơn hàng #{{ $order->id }}? Hành động này không thể hoàn tác.
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="button" class="btn btn-danger" data-cancel-order="{{ $order->id }}">Đồng ý hủy</button>
                      </div>
                    </div>
                  </div>
                </div>
                @push('scripts')
                <script>
                  document.addEventListener('DOMContentLoaded', function(){
                    const btn = document.querySelector('[data-cancel-order="{{ $order->id }}"]');
                    if(btn){
                      btn.addEventListener('click', function(){
                        const form = document.getElementById('cancelForm-{{ $order->id }}');
                        if(form) form.submit();
                      });
                    }
                  });
                </script>
                @endpush
                @endif
              </div>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center text-muted py-4">Chưa có đơn hàng nào.</div>
    @endforelse

    <div class="mt-3">
        {{ $orders->withQueryString()->links() }}
    </div>
</div>
@endsection

@push('styles')
<style>
/* Enhanced icon styling with gradients */
.order-quick-actions .icon-wrap{
  width: 50px; 
  height: 50px; 
  border-radius: 12px;
  display: flex; 
  align-items: center; 
  justify-content: center;
  border: none;
  margin: 0 auto 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  transition: all 0.3s ease;
}

.order-quick-actions i{ 
  font-size: 1.4rem;
}

/* Enhanced quick action tiles with modern gradients */
.qa-tile{ 
  padding: 16px 12px; 
  border-radius: 16px; 
  border: 1px solid rgba(0,0,0,0.06);
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  position: relative;
  overflow: hidden;
}

.qa-tile:hover{ 
  transform: translateY(-4px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}

.qa-tile:hover .icon-wrap {
  transform: scale(1.1) rotate(5deg);
}

.qa-tile .small {
  color: #64748b;
  font-weight: 500;
  font-size: 0.8rem;
}

.qa-tile .fw-bold {
  font-size: 1.5rem;
  font-weight: 800;
  color: #1e293b;
  margin-top: 4px;
}

/* Pending - Yellow/Amber */
.qa-pending{ 
  background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
  border-color: #fde68a;
}
.qa-pending .icon-wrap {
  background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(251, 191, 36, 0.4);
}

/* Processing - Blue */
.qa-processing{ 
  background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
  border-color: #bfdbfe;
}
.qa-processing .icon-wrap {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

/* Shipped - Cyan */
.qa-shipped{ 
  background: linear-gradient(135deg, #ecfeff 0%, #cffafe 100%);
  border-color: #a5f3fc;
}
.qa-shipped .icon-wrap {
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(6, 182, 212, 0.4);
}

/* Delivered - Green */
.qa-delivered{ 
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
  border-color: #bbf7d0;
}
.qa-delivered .icon-wrap {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

/* Enhanced nav pills */
.nav-pills .nav-link{ 
  background: #f8f9fa; 
  color: #6c757d;
  border-radius: 10px;
  padding: 8px 16px;
  font-weight: 500;
  transition: all 0.2s ease;
}

.nav-pills .nav-link:hover {
  background: #e9ecef;
  transform: translateY(-1px);
}

.nav-pills .nav-link.active{ 
  background: linear-gradient(135deg, #ff6b35 0%, #EE4D2D 100%);
  color: #ffffff;
  font-weight: 600;
  box-shadow: 0 4px 12px rgba(238, 77, 45, 0.3);
}

/* Colored pills per status with gradients */
.nav-pills .pill-pending.active{ 
  background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);
}

.nav-pills .pill-processing.active{ 
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.nav-pills .pill-shipped.active{ 
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
}

.nav-pills .pill-delivered.active{ 
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.nav-pills .pill-cancelled.active{ 
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}
</style>
@endpush
