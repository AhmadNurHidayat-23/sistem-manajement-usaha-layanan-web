@extends('layouts.pelanggan', ['title' => 'SIMAUL - Sistem Informasi Manajemen Usaha Layanan'])

@section('content')
<div class="space-y-16 lg:space-y-24 pb-16 lg:pb-24">
    {{-- Hero --}}
    @include('landing.partials.hero')

    {{-- Cara Kerja --}}
    @include('landing.partials.workflow')

    {{-- Cabang Layanan --}}
    @include('landing.partials.cabang')

    {{-- Katalog Layanan --}}
    @include('landing.partials.layanan')

    {{-- Promo --}}
    @include('landing.partials.promo')

    {{-- Membership --}}
    @include('landing.partials.loyalty')

    {{-- Ulasan --}}
    @include('landing.partials.ulasan')

    {{-- CTA Akhir --}}
    @include('landing.partials.cta')
</div>
@endsection