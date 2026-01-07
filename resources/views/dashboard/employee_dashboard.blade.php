@extends('layouts.dashboard')

@section('title', 'Employee Dashboard')

@section('content')
<div class="p-6">
    <h2 class="text-3xl font-bold text-gray-800 mb-8">📊 Employee Dashboard</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 xl:grid-cols-5 gap-6">
        <!-- @php
            $cards = [
                ['title' => 'All Projects', 'count' => $stats['all'], 'gradient' => 'bg-cyan-400 shadow-lg shadow-cyan-200/50 ', 'icon' => '📁'],
                ['title' => 'Working', 'count' => $stats['working'], 'gradient' => 'bg-yellow-400 shadow-lg shadow-yellow-200/50 ', 'icon' => '🛠️'],
                ['title' => 'Completed', 'count' => $stats['complete'], 'gradient' => 'bg-green-400 shadow-lg shadow-green-200/50 ', 'icon' => '✅'],
                ['title' => 'Pause', 'count' => $stats['pause'], 'gradient' => 'bg-purple-400 shadow-lg shadow-purple-200/50 ', 'icon' => '⏸️'],
                ['title' => 'Issue', 'count' => $stats['issue'], 'gradient' => 'bg-red-400 shadow-lg shadow-red-200/50 ', 'icon' => '🚫'],
                ['title' => 'Temp Hold', 'count' => $stats['temp_hold'], 'gradient' => 'bg-blue-400 shadow-lg shadow-blue-200/50 ', 'icon' => '🕒'],
                ['title' => 'User Rating', 'count' => $avgUserRating, 'gradient' =>'bg-purple-400 shadow-lg shadow-purple-200/50 ', 'icon' => '⭐'],
                ['title' => 'HR Rating', 'count' => $avgHrRating, 'gradient' => 'bg-pink-400 shadow-lg shadow-pink-200/50 ', 'icon' => '🌟'],
                ['title' => 'Fines', 'count' => $fineCount, 'gradient' => 'bg-red-400 shadow-lg shadow-red-200/50 ', 'icon' => '💸'],
                ['title' => 'Appreciations', 'count' => $appreciationCount, 'gradient' => 'bg-green-400 shadow-lg shadow-green-200/50 ', 'icon' => '🎉'],
               
            ];
        @endphp -->

       @php
            $cards = [
                ['title' => 'All Projects', 'count' => $stats['all'], 'gradient' => 'relative text-white bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600 hover:bg-gradient-to-br rounded-2xl p-6 shadow-xl 
        overflow-hidden hover:scale-105 transition-transform duration-300 ease-in-out cursor-pointer ', 'icon' => ''],
                ['title' => 'Working', 'count' => $stats['working'], 'gradient' => 'relative text-white bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600 hover:bg-gradient-to-br rounded-2xl p-6 shadow-xl 
        overflow-hidden hover:scale-105 transition-transform duration-300 ease-in-out cursor-pointer ', 'icon' => ''],
                ['title' => 'Completed', 'count' => $stats['complete'], 'gradient' => 'relative text-white bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600 hover:bg-gradient-to-br rounded-2xl p-6 shadow-xl 
    overflow-hidden hover:scale-105 transition-transform duration-300 ease-in-out cursor-pointer ', 'icon' => ''],
                ['title' => 'Pause', 'count' => $stats['pause'], 'gradient' => 'relative text-white bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600 hover:bg-gradient-to-br rounded-2xl p-6 shadow-xl 
        overflow-hidden hover:scale-105 transition-transform duration-300 ease-in-out cursor-pointer ', 'icon' => ''],
                ['title' => 'Issue', 'count' => $stats['issue'], 'gradient' => 'relative text-white bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600 hover:bg-gradient-to-br rounded-2xl p-6 shadow-xl 
        overflow-hidden hover:scale-105 transition-transform duration-300 ease-in-out cursor-pointer ', 'icon' => ''],
                ['title' => 'Temp Hold', 'count' => $stats['temp_hold'], 'gradient' => 'relative text-white bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600 hover:bg-gradient-to-br rounded-2xl p-6 shadow-xl 
        overflow-hidden hover:scale-105 transition-transform duration-300 ease-in-out cursor-pointer', 'icon' => ''],
                ['title' => 'User Rating', 'count' => $avgUserRating, 'gradient' =>'relative text-white bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600 hover:bg-gradient-to-br rounded-2xl p-6 shadow-xl 
        overflow-hidden hover:scale-105 transition-transform duration-300 ease-in-out cursor-pointer ', 'icon' => ''],
                ['title' => 'HR Rating', 'count' => $avgHrRating, 'gradient' => 'relative text-white bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600 hover:bg-gradient-to-br rounded-2xl p-6 shadow-xl 
        overflow-hidden hover:scale-105 transition-transform duration-300 ease-in-out cursor-pointer ', 'icon' => ''],
                ['title' => 'Fines', 'count' => $fineCount, 'gradient' => 'relative text-white bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600 hover:bg-gradient-to-br rounded-2xl p-6 shadow-xl 
        overflow-hidden hover:scale-105 transition-transform duration-300 ease-in-out cursor-pointer ', 'icon' => ''],
                ['title' => 'Appreciations', 'count' => $appreciationCount, 'gradient' => 'relative text-white bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600 hover:bg-gradient-to-br rounded-2xl p-6 shadow-xl 
        overflow-hidden hover:scale-105 transition-transform duration-300 ease-in-out cursor-pointer ', 'icon' => ''],
                ['title' => 'PM Review Rating', 'count' => $avgPmRating, 'gradient' => 'relative text-white bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600 hover:bg-gradient-to-br rounded-2xl p-6 shadow-xl 
        overflow-hidden hover:scale-105 transition-transform duration-300 ease-in-out cursor-pointer ', 'icon' => ''], //  NEW

               
            ];
        @endphp








      


        @foreach ($cards as $card)
            <div class="{{ $card['gradient'] }} text-white rounded-xl p-5 shadow-xl transform hover:scale-105 transition duration-300 ease-in-out">
                <div class="flex items-center space-x-4">
                    <div class="text-3xl">{{ $card['icon'] }}</div>
                    <div>
                        <div class="text-xl font-medium">{{ $card['title'] }}</div>
                        <div class="text-2xl font-bold">{{ $card['count'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
