@extends('layouts.mobile')

@section('title', 'Coming Soon')

@section('content')
<div class="flex items-center justify-center min-h-screen">
    <div class="text-center">
        <i class="fas fa-tools text-4xl text-gray-400 mb-4"></i>
        <h2 class="text-xl font-semibold text-gray-700">Fitur Sedang Dibangun</h2>
        <p class="text-gray-500 mt-2">Fitur ini akan segera hadir</p>
        <a href="{{ route('dashboard') }}" class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded-lg">
            Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection