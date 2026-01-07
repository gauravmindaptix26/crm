<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full h-8 border border-gray-300 dark:border-gray-700 
         dark:bg-gray-900 dark:text-gray-300 
         focus:ring-[#4f46e5] dark:focus:ring-[#4f46e5] 
         rounded-md shadow-sm focus:outline-none pl-2" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="bmt-1 block w-full h-8 border border-gray-300 dark:border-gray-700 
         dark:bg-gray-900 dark:text-gray-300 
         focus:ring-[#4f46e5] dark:focus:ring-[#4f46e5] 
         rounded-md shadow-sm focus:outline-none pl-2" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>
          <!-- Mobile Number -->
          <div>
            <x-input-label for="phone_number" :value="__('Mobile Number')" />
            <x-text-input id="phone_number" name="phone_number" type="tel" class="mt-1 block w-full h-8 border border-gray-300 dark:border-gray-700 
         dark:bg-gray-900 dark:text-gray-300 
         focus:ring-[#4f46e5] dark:focus:ring-[#4f46e5] 
         rounded-md shadow-sm focus:outline-none pl-2" :value="old('phone_number', $user->phone_number)" placeholder="Enter 10-digit mobile number" autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('phone_number')" />
        </div>

        <!-- Profile Photo -->
        <div>
            <x-input-label for="image" :value="__('Upload Profile Image')" />
            <input id="image" name="image" type="file" class="mt-1 block w-full text-gray-900 dark:text-gray-300 border-gray-300 dark:border-gray-600 rounded-md" accept="image/*" />
            <x-input-error class="mt-2" :messages="$errors->get('image')" />
            @if ($user->image)
                <!-- <div class="mt-2">
                    <img src="{{ asset('storage/' . $user->image) }}" alt="Profile Photo" class="w-20 h-20 rounded-full object-cover" />
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Current profile photo') }}</p>
                    <p>Debug Path: {{ $user->image }}</p> 
                    <p>Debug URL: {{ asset('storage/' . $user->image) }}</p> 
                </div> -->
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
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
            <span class="font-semibold">Your profile has been updated successfully!</span>
        </div>
    </div>
@endif

        </div>
    </form>
</section>