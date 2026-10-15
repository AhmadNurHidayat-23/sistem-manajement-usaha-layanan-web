@extends('layouts.app', ['title' => 'Log Notifikasi'])

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    @include('shared.notifikasi.partials.header')

    {{-- Statistik --}}
    @include('shared.notifikasi.partials.stats')

    {{-- Filter --}}
    @include('shared.notifikasi.partials.filters')

    {{-- Tabel --}}
    @include('shared.notifikasi.partials.table')
</div>
@endsection
