@extends('layouts.app', ['title' => 'Log Notifikasi'])

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    @include('notifikasi.partials.header')

    {{-- Statistik --}}
    @include('notifikasi.partials.stats')

    {{-- Filter --}}
    @include('notifikasi.partials.filters')

    {{-- Tabel --}}
    @include('notifikasi.partials.table')
</div>
@endsection
