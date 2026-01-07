@extends('layouts.dashboard')

@section('title', 'HR Dashboard')

@section('content')


    <!-- <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6 [height:max-content]"> -->
    <!-- Total Employees -->
    <!-- <div class="relative bg-gradient-to-r from-blue-500 to-indigo-600 p-6 shadow-xl rounded-2xl text-white transform transition-all duration-300 hover:scale-105 hover:shadow-2xl">
        <div class="absolute top-4 right-4 bg-white/30 p-3 rounded-full backdrop-blur-md">
            <i class="fas fa-users text-xl text-white"></i>
        </div>
        <h2 class="text-lg font-semibold">Total Employees</h2>
        <p class="text-4xl font-extrabold animate-bounce">{{ $totalUsers }}</p>
    </div> -->

    <!-- Performance Reviews -->
    <!-- <a href="{{ route('admin.reviews.index') }}" 
       class="relative bg-gradient-to-r from-green-500 to-emerald-600 p-6 shadow-xl rounded-2xl text-white transform transition-all duration-300 hover:scale-105 hover:shadow-2xl">
        <div class="absolute top-4 right-4 bg-white/30 p-3 rounded-full backdrop-blur-md">
            <i class="fas fa-star text-xl text-white"></i>
        </div>
        <h2 class="text-lg font-semibold">Performance Reviews</h2>
        <p class="text-2xl font-bold mt-2">View All</p>
    </a>
</div>


    </div> -->
    <div class="p-6">
    <h2 class="text-3xl text-gray-800 mb-8 font-semibold text-gray-800"> HR Dashboard</h2>

    {{-- Quick Links --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-10">
        <a href="{{ route('design-team-reports.index') }}" class="bg-white group bg-gradient-to-r  rounded-xl p-5 shadow-lg hover:scale-105 
        transition-transform duration-300 ease-in-out cursor-pointer">
            <div class="text-lg font-semibold mb-2 text-black">Total Employees</div>
            <div class="text-3xl font-extrabold bg-gradient-to-r 
           from-teal-700 via-teal-800 to-teal-900 
           bg-clip-text text-transparent animate-pulse">{{ $totalUsers }}</div>
        </a>
        <a href="{{ route('admin.reviews.index') }}" class="group bg-gradient-to-r bg-white  rounded-xl p-5 shadow-lg hover:scale-105 
        transition-transform duration-300 ease-in-out cursor-pointer">
            <div class="text-lg font-semibold mb-2 text-black">Performance Reviews</div>
            <div class="w-28 text-sm opacity-80 text-white font-medium px-6 py-2 
         rounded-lg shadow-md bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600
         hover:from-teal-500 hover:via-teal-600 hover:to-teal-700
         focus:outline-none">View All</div>
        </a>
     
    </div>

    @if(!$myTasks->isEmpty())
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 md:col-span-6">
            <div class="scroll-container dashboard-card">
                <h3 class="text-2xl font-semibold mb-5 flex items-center gap-2">📝 My Tasks for the Day</h3>

                <div class="overflow-auto rounded-lg border border-gray-300 shadow-sm max-h-[450px]">
                    <table class="table-fixed w-full border-collapse">
                        <thead class="bg-gray-100 sticky top-0 z-10">
                            <tr>
                                <th class="w-2/5 px-4 py-3 text-left text-gray-700 font-semibold border-b border-gray-300">Task Info</th>
                                <th class="w-3/5 px-4 py-3 text-left text-gray-700 font-semibold border-b border-gray-300">Task Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($myTasks as $task)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="border-r border-gray-300 px-4 py-4 align-top">
                                        <p class="mb-2 font-semibold text-gray-800">{{ $task->name }}</p>
                                        <p class="mb-4 text-sm text-gray-600">
                                            Assigned By: 
                                            <span class="font-medium text-gray-700">{{ $task->createdBy->name ?? 'N/A' }}</span>
                                        </p>
                                        <a href="{{ route('task.addMessageForm', $task->id) }}" 
                                           class="inline-block bg-green-600 text-white text-sm font-semibold py-2 px-4 rounded-md hover:bg-green-700 transition">
                                            ✅ Mark as Done
                                        </a>
                                    </td>
                                    <td class="px-4 py-4 align-top whitespace-pre-wrap text-gray-700">
                                        {!! nl2br(e($task->description)) !!}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
    @endif
@endsection
