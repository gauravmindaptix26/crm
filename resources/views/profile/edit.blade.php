@extends('layouts.dashboard')

@section('title', 'Profile')

@section('content')
<div class="flex items-center justify-center min-h-screen bg-gray-100 dark:bg-gray-900 px-4 py-10">
    <div class="w-full max-w-2xl bg-white dark:bg-gray-800 shadow-lg rounded-lg p-8 space-y-6" x-data="{ 
         profileOpen: {{ $errors->updateProfileInformation->any() || session('status') === 'profile-updated' ? 'true' : 'false' }},
         passwordOpen: {{ $errors->updatePassword->any() || session('status') === 'password-updated' ? 'true' : 'false' }},
         deleteOpen: {{ $errors->userDeletion->any() ? 'true' : 'false' }}
     }">
        
        <!-- Profile Header -->
        <div class="flex flex-col items-center">
            <div class="w-40 h-40 rounded-full bg-blue-500 flex items-center justify-center text-white text-2xl font-bold shadow-md">
                @if (auth()->user()->image)
                    <img src="{{ asset('storage/' . auth()->user()->image) }}" alt="" class="w-full h-full rounded-full object-cover" />
                @else
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                @endif
            </div>
            <div class="text-center pt-2">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-200">{{ auth()->user()->name }}</h2>
                <p class="text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</p>
            </div>
        </div> 

        <hr class="border-gray-300 dark:border-gray-700">

        <!-- Profile Sections -->
        <div class="space-y-4">
            <!-- Profile Information Toggle -->
            <div>
                <button @click="profileOpen = !profileOpen" class="flex items-center justify-between w-full text-gray-700 dark:text-gray-300 text-lg font-medium p-3 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">
                    <span>Profile Information</span>
                    <svg x-show="!profileOpen" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                    </svg>
                    <svg x-show="profileOpen" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"></path>
                    </svg>
                </button>
                <div x-show="profileOpen" class="mt-3 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow-sm">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <!-- Change Password Toggle -->
            <div>
                <button @click="passwordOpen = !passwordOpen" class="flex items-center justify-between w-full text-gray-700 dark:text-gray-300 text-lg font-medium p-3 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">
                    <span>Change Password</span>
                    <svg x-show="!passwordOpen" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                    </svg>
                    <svg x-show="passwordOpen" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"></path>
                    </svg>
                </button>
                <div x-show="passwordOpen" class="mt-3 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow-sm">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- Delete Account Toggle -->
            <div>
                <button @click="deleteOpen = !deleteOpen" class="flex items-center justify-between w-full text-red-600 dark:text-red-400 text-lg font-medium p-3 rounded-md hover:bg-red-50 dark:hover:bg-red-700">
                    <span>Delete Account</span>
                    <svg x-show="!deleteOpen" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                    </svg>
                    <svg x-show="deleteOpen" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"></path>
                    </svg>
                </button>
                <div x-show="deleteOpen" class="mt-3 bg-red-50 dark:bg-red-700 p-4 rounded-lg shadow-sm">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Include Alpine.js for interactive toggles -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x/dist/cdn.min.js" defer></script>
@endpush
