@extends('layouts.admin')
@section('title', 'Analytics')

@section('content_header')
<h1>Analytics</h1>
@stop

@section('content')
<div class="row g-3">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header">Sales (Last 12 months)</div>
            <div class="card-body"><canvas id="sales12m" height="120"></canvas></div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header">Category Share</div>
            <div class="card-body"><canvas id="categoryShare" height="120"></canvas></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const s12 = document.getElementById('sales12m');
    if (s12) new Chart(s12, {
        type: 'bar',
        data: {
            labels: [...Array(12).keys()].map(m => `T${m+1}`),
            datasets: [{
                label: 'Sales',
                data: [12, 9, 14, 18, 15, 20, 22, 24, 19, 17, 21, 25],
                backgroundColor: '#c39bd3'
            }]
        }
    });
    const cs = document.getElementById('categoryShare');
    if (cs) new Chart(cs, {
        type: 'pie',
        data: {
            labels: ['Bags', 'Hats', 'Glasses', 'Bracelets', 'Necklaces'],
            datasets: [{
                data: [30, 15, 20, 18, 17],
                backgroundColor: ['#ffd1dc', '#cfe8ff', '#e6d6ff', '#f6ead4', '#c39bd3']
            }]
        },
        options: {
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
@endpush