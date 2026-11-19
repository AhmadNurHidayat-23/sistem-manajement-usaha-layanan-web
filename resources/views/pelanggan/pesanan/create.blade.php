@extends('layouts.pelanggan', ['title' => 'Buat Pesanan'])

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    {{-- Modal Buat Pesanan (Ditampilkan sebagai modal pop-up dengan background blur) --}}
    @include('pelanggan.pesanan.partials.modal-pesanan', [
        'branches' => $branches,
        'services' => $services,
        'selectedBranchId' => $selectedBranchId,
        'selectedServiceId' => $selectedServiceId,
        'selectedService' => $selectedService,
        'isOpen' => true,
        'isStandalone' => true,
    ])
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endpush
