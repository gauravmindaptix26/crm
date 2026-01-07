<x-guest-layout>
    <div class="flex items-center justify-center px-3 py-3 bg-green-500 bg-opacity-90 dark:bg-gray-900">
        <div class="w-full max-w-md bg-white dark:bg-gray-800 shadow-2xl rounded-lg  space-y-6 px-4 py-4">
            <h2 class="text-center text-3xl font-bold text-gray-800 dark:text-white">
                Create an Account
            </h2>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Name -->
                <div>
                    <x-input-label for="name" :value="__('Full Name')" class="text-gray-700 dark:text-gray-300" />
                    <x-text-input id="name" class="block mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-3 py-2 focus:ring-green-500 focus:border-green-500" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <!-- Email Address -->
                <div class="mt-4">
                    <x-input-label for="email" :value="__('Email Address')" class="text-gray-700 dark:text-gray-300" />
                    <x-text-input id="email" class="block mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-3 py-2 focus:ring-green-500 focus:border-green-500" type="email" name="email" :value="old('email')" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Role Selection -->
                <div class="mt-4">
                    <x-input-label for="role" :value="__('Select Role')" class="text-gray-700 dark:text-gray-300" />
                    <select id="role" name="role" required class="block mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-3 py-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">-- Select Role --</option>
                        <option value="HR">HR</option>
                        <option value="Team Lead">Team Lead</option>
                        <option value="Employee">Employee</option>
                        <option value="Freelancer">Freelancer</option>
                        <option value="Project Manager">Project Manager</option>
                        <option value="Sales Team">Sales Team</option>

                    </select>
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <x-input-label for="password" :value="__('Password')" class="text-gray-700 dark:text-gray-300" />
                    <x-text-input id="password" class="block mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-3 py-2 focus:ring-green-500 focus:border-green-500" type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div class="mt-4">
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-gray-700 dark:text-gray-300" />
                    <x-text-input id="password_confirmation" class="block mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-3 py-2 focus:ring-green-500 focus:border-green-500" type="password" name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <!-- Submit & Login Redirect -->
                <div class="flex items-center justify-between mt-6">
                    <a class="text-sm text-gray-600 dark:text-gray-400 hover:text-green-400 transition" href="{{ route('login') }}">
                        {{ __('Already registered?') }}
                    </a>

                    <x-primary-button class="px-5 py-2 text-white bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-400 rounded-lg shadow-md transition">
                        {{ __('Register') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
