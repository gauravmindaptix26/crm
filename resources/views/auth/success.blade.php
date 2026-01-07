<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Success Message (Flash from Login) -->
            @if (session('login_success'))
                <div 
                    x-data="{ show: true }" 
                    x-init="setTimeout(() => { show = false; }, 5000)"  {{-- Auto-hide after 5 seconds --}}
                    x-show="show"
                    class="relative bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-6 rounded-lg mb-6 text-center shadow-xl transform transition-all duration-500 ease-in-out hover:scale-105"
                    role="alert"
                    aria-live="assertive"
                >
                    <!-- Checkmark Icon -->
                    <div class="flex justify-center mb-3">
                        <svg class="w-12 h-12 text-white animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>

                    <!-- Simple Message -->
                    <h2 class="text-lg font-semibold">{{ session('login_success') }}</h2>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>