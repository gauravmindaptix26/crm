<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Form Title -->
    <h2 class="text-2xl font-bold text-center text-black mb-6">
        {{ __('Sign In') }}
    </h2>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
        <x-input-label for="email" :value="__('Email')" class="text-black" style="font-size: 18px; line-height: 28px;"></x-input-label>



            <x-text-input id="email"
                class="block mt-1 w-full font-medium text-gray-900 placeholder-gray-500 bg-white border border-gray-300 rounded focus:outline-none focus:ring-0"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-500" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" class="text-black" style="font-size: 18px; line-height: 28px;" />
            <x-text-input id="password"
                class="block mt-1 w-full font-medium text-gray-900 placeholder-gray-500 bg-white border border-gray-300 rounded focus:outline-none focus:ring-0"
                type="password"
                name="password"
                required
                autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-500" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox"
                    class="rounded bg-white border-gray-300 text-indigo-600 shadow-sm focus:outline-none focus:ring-0"
                    name="remember">
                <span class="ms-2 text-sm text-black">
                    {{ __('Remember me') }}
                </span>
            </label>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-back hover:text-gray-100 rounded-md focus:outline-none focus:ring-0"
                   href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3 focus:outline-none focus:ring-0">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
