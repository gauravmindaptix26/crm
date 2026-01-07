<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div class="relative">
        <x-input-label for="update_password_current_password" :value="__('Current Password')" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full h-8 border border-gray-300 dark:border-gray-700 
         dark:bg-gray-900 dark:text-gray-300 
         focus:ring-[#4f46e5] dark:focus:ring-[#4f46e5] 
         rounded-md shadow-sm focus:outline-none pl-4" autocomplete="current-password" />
            <!-- Eye Icon -->
    <button type="button"
        onclick="togglePassword('update_password_current_password', this)" 
        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 pt-6">
        <svg xmlns="http://www.w3.org/2000/svg" 
             class="h-5 w-5" fill="none" viewBox="0 0 24 24" 
             stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" 
                  stroke-width="2" 
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" 
                  stroke-width="2" 
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 
                     8.268 2.943 9.542 7-1.274 4.057-5.065 
                     7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
    </button>
            
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div class="relative">
            <x-input-label for="update_password_password" :value="__('New Password')" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full h-8 border border-gray-300 dark:border-gray-700 
         dark:bg-gray-900 dark:text-gray-300 
         focus:ring-[#4f46e5] dark:focus:ring-[#4f46e5] 
         rounded-md shadow-sm focus:outline-none pl-4" autocomplete="new-password" />
            <!-- Eye Icon -->
    <button type="button"
        onclick="togglePassword('update_password_password', this)" 
        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 pt-6">
        <svg xmlns="http://www.w3.org/2000/svg" 
             class="h-5 w-5" fill="none" viewBox="0 0 24 24" 
             stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" 
                  stroke-width="2" 
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" 
                  stroke-width="2" 
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 
                     8.268 2.943 9.542 7-1.274 4.057-5.065 
                     7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
    </button>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div class="relative">
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full h-8 border border-gray-300 dark:border-gray-700 
         dark:bg-gray-900 dark:text-gray-300 
         focus:ring-[#4f46e5] dark:focus:ring-[#4f46e5] 
         rounded-md shadow-sm focus:outline-none pl-4" autocomplete="new-password" />
            <!-- Eye Icon -->
    <button type="button"
        onclick="togglePassword('update_password_password_confirmation', this)" 
        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 pt-6">
        <svg xmlns="http://www.w3.org/2000/svg" 
             class="h-5 w-5" fill="none" viewBox="0 0 24 24" 
             stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" 
                  stroke-width="2" 
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" 
                  stroke-width="2" 
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 
                     8.268 2.943 9.542 7-1.274 4.057-5.065 
                     7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
    </button>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'password-updated')
    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition
        x-init="setTimeout(() => show = false, 5000)"
        class="mt-4 p-4 rounded-lg bg-green-100 border border-green-300 text-green-800 dark:bg-green-800 dark:border-green-600 dark:text-green-100 shadow-md"
    >
        <div class="flex items-center">
            <!-- Success Icon -->
            <svg class="w-6 h-6 mr-2 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>

            <!-- Success Message -->
            <span class="font-semibold">Your password has been updated successfully!</span>
        </div>
    </div>
@endif
        </div>
    </form>
</section>
<script>
    function togglePassword(fieldId, button) {
        const field = document.getElementById(fieldId);
        const type = field.type === "password" ? "text" : "password";
        field.type = type;
        button.classList.toggle("text-gray-700");
    }
</script>

