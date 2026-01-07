<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Website Submission')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">

    <!-- Navbar -->
    <nav class="bg-white shadow p-4">
        <div class="container mx-auto flex justify-between">
            <a href="{{ url('/') }}" class="font-bold text-xl text-gray-800">MySite</a>
            <div>
                @guest
                    <a href="{{ route('login') }}" class="px-3">Login</a>
                    <a href="{{ route('register') }}" class="px-3">Register</a>
                @endguest
                @auth
                    <span class="px-3">Hi, {{ auth()->user()->name }}</span>
                    <a href="{{ route('dashboard') }}" class="px-3">Dashboard</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto py-6">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white p-4 text-center mt-6">
        &copy; {{ date('Y') }} MySite. All rights reserved.
    </footer>

</body>
</html>
