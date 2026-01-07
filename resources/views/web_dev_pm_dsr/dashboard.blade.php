@extends('layouts.dashboard')

@section('title', 'Web Dev PM DSR Dashboard')

@section('content')
<div class="max-w-4xl mx-auto py-12 px-6">
    <h1 class="text-4xl font-bold text-[#0d9488] mb-8">Web Dev PM DSR Dashboard</h1>

    <!-- Success Message with Auto Fade Out -->
    @if(session('success'))
        <div id="success-alert" class="fixed top-4 right-4 z-50 bg-green-600 text-white px-8 py-5 rounded-2xl shadow-2xl flex items-center gap-4 animate-pulse">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
            </svg>
            <div>
                <p class="text-xl font-bold">{{ session('success') }}</p>
                <p class="text-sm opacity-90">This message will disappear in 5 seconds</p>
            </div>
        </div>

        <script>
            // Auto-hide success message after 5 seconds
            setTimeout(function() {
                const alert = document.getElementById('success-alert');
                if (alert) {
                    alert.style.transition = 'opacity 1s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 1000);
                }
            }, 5000);
        </script>
    @endif

    <div class="bg-white rounded-2xl shadow-xl p-10 text-center">
        <svg class="w-24 h-24 text-green-500 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <h2 class="text-3xl font-bold text-gray-800 mb-4">Today's Report Submitted Successfully!</h2>
        <p class="text-xl text-gray-600 mb-2">Date: <span class="font-bold text-[#0d9488]">{{ now()->format('d F Y') }}</span></p>
        <p class="text-lg text-gray-500">You can submit tomorrow's report starting from midnight.</p>
        
        <!-- <div class="mt-10">
            <a href="{{ route('web.dev.pm.dsr.daily') }}" class="inline-block bg-[#0d9488] text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-[#0b7a70] transition shadow-lg">
                Go to Daily DSR Form
            </a>
        </div> -->
    </div> 
</div>
@endsection