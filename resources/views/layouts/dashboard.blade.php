<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4F46E5',
                        secondary: '#3B82F6',
                        dark: '#1E293B'
                    },
                    fontFamily: {
                        manrope: ['Manrope', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 font-manrope flex flex-col min-h-screen">
    <!-- Header: Moved outside the flex-1 container for full width -->
    @include('layouts.partials.header')

    <!-- Main Layout with Sidebar and Content -->
    <div class="flex flex-1">
        <!-- Include Sidebar -->
        @include('layouts.partials.sidebar')

        <!-- Main Content -->

        <div class="custom-main-bar w-full">
        <main class="flex-1 p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-1 gap-6">
      @if (session('login_success'))
    <div 
        x-data="{ show: true }" 
         x-init="setTimeout(() => { show = false; }, 1000)" 
        x-show="show"
        class="col-span-full relative bg-gradient-to-r from-teal-500 to-teal-600 text-white px-3 py-2 rounded-md mb-4 mx-auto max-w-md flex items-center justify-center gap-2 shadow-lg transform transition-all duration-300 ease-in-out hover:scale-102"
        role="alert"
        aria-live="assertive"
    >
        <!-- Checkmark Icon -->
        <i class="fas fa-check-circle text-xl text-white animate-pulse"></i>

        <!-- Message -->
        <span class="text-base font-medium font-manrope">{{ session('login_success') }}</span>
    </div>
@endif


            @yield('content')
        </main>
           <div class="cus-footer bottom-0 left-0 w-full">
            @include('layouts.partials.footer')
    </div>
    </div>

    <!-- Include Footer -->
 
    <!-- Yield scripts -->
    @yield('scripts')
</body>
</html>
