@props([
'id' => 'heroCarousel',
'items' => [], // mỗi slide có: 'image', 'title', 'subtitle', 'button'
])

<div id="{{ $id }}" class="carousel slide carousel-fade rounded-4 shadow-lg overflow-hidden" data-bs-ride="carousel">
    {{-- Indicators --}}
    <div class="carousel-indicators">
        @foreach ($items as $index => $item)
        <button type="button" data-bs-target="#{{ $id }}" data-bs-slide-to="{{ $index }}"
            class="@if($index === 0) active @endif"
            aria-label="Slide {{ $index + 1 }}"></button>
        @endforeach
    </div>

    <div class="carousel-inner">
        @foreach ($items as $index => $item)
        <div class="carousel-item @if($index === 0) active @endif">
            <div class="position-relative" style="height: 480px;">
                {{-- Background --}}
                <img src="{{ $item['image'] }}" class="d-block w-100 h-100 object-fit-cover"
                    style="filter: brightness(0.8);" alt="Slide {{ $index + 1 }}">

                {{-- Pastel overlay --}}
                <div class="position-absolute top-0 start-0 w-100 h-100"
                    style="background: linear-gradient(135deg, rgba(255,182,193,0.35), rgba(173,216,230,0.35));">
                </div>

                {{-- Content --}}
                <div class="position-absolute top-50 start-50 translate-middle text-center text-white px-4">
                    <h2 class="fw-bold display-5 animate__animated animate__fadeInDown">
                        {{ $item['title'] }}
                    </h2>
                    <p class="lead mb-3 animate__animated animate__fadeInUp">
                        {{ $item['subtitle'] }}
                    </p>
                    @if(!empty($item['button']))
                    <a href="{{ $item['button']['url'] }}"
                        class="btn btn-lg px-4 rounded-pill"
                        style="background-color: #FFB6C1; color: white; font-weight: 600;">
                        {{ $item['button']['label'] }}
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Controls --}}
    <button class="carousel-control-prev" type="button" data-bs-target="#{{ $id }}" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#{{ $id }}" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
    </button>
</div>