@extends('layouts.dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-3xl font-extrabold text-primary">Dashboard Data</h2>
    </div>

<!-- 📊 Project Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    <!-- <div class="bg-white shadow rounded-lg p-4 text-center border-t-4 border-primary">
        <h4 class="text-sm font-semibold text-gray-500">All Projects</h4>
        <p class="text-2xl font-bold text-primary">{{ $allProjectsCount }}</p>
    </div> -->
    <div class="bg-white shadow rounded-lg p-4 text-center border-t-4 border-blue-600">
        <h4 class="text-sm font-semibold text-gray-500">Working</h4>
        <p class="text-2xl font-bold text-blue-600">{{ $workingCount }}</p>
    </div>
    <!-- <div class="bg-white shadow rounded-lg p-4 text-center border-t-4 border-green-600">
        <h4 class="text-sm font-semibold text-gray-500">Completed</h4>
        <p class="text-2xl font-bold text-green-600">{{ $completedCount }}</p>
    </div> -->
    <div class="bg-white shadow rounded-lg p-4 text-center border-t-4 border-yellow-500">
        <h4 class="text-sm font-semibold text-gray-500">Paused</h4>
        <p class="text-2xl font-bold text-yellow-500">{{ $pausedCount }}</p>
    </div>
    <div class="bg-white shadow rounded-lg p-4 text-center border-t-4 border-red-500">
        <h4 class="text-sm font-semibold text-gray-500">Issue</h4>
        <p class="text-2xl font-bold text-red-500">{{ $issueCount }}</p>
    </div>
    <div class="bg-white shadow rounded-lg p-4 text-center border-t-4 border-gray-500">
        <h4 class="text-sm font-semibold text-gray-500">Temp Hold</h4>
        <p class="text-2xl font-bold text-gray-700">{{ $tempHoldCount }}</p>
    </div>
</div>






    <!-- 🔍 Filter Form -->
    <form method="GET" class="bg-white p-4 rounded-lg shadow-md mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <!-- Start Date -->
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" name="start_date" id="start_date"
                    value="{{ request('start_date') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
            </div>

            <!-- End Date -->
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                <input type="date" name="end_date" id="end_date"
                    value="{{ request('end_date') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
            </div>

            <!-- Lead From -->
            <div>
                <label for="lead_from_id" class="block text-sm font-medium text-gray-700">Lead Source</label>
                <select name="lead_from_id" id="lead_from_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    <option value="">-- Select Source --</option>
                    @foreach($profiles as $profile)
                        <option value="{{ $profile->id }}" {{ request('lead_from_id') == $profile->id ? 'selected' : '' }}>
                            {{ $profile->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Button -->
            <div>
                <button type="submit"
                    class="inline-flex justify-center w-full px-4 py-2 bg-primary text-white text-sm font-medium rounded-md shadow-sm hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    Apply Filters
                </button>
            </div>
        </div>
    </form>


    {{-- Use 3 columns grid, responsive --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Card 1: Hired Projects -->
        <div class="bg-white rounded-xl shadow-lg p-4 flex flex-col">
            <!-- Top Summary -->
            <div class="flex flex-col justify-center items-center bg-green-600 text-white rounded-lg p-4 mb-4">
                <h5 class="text-lg font-semibold mb-2">Hired</h5>
                <h1 class="text-4xl font-bold">{{ $hiredLeads->sum() }}</h1>
                <i class="fas fa-briefcase fa-2x mt-3 opacity-90"></i>
            </div>

            <!-- List by Profile -->
            <h3 class="text-xl font-semibold mb-3">Hired Projects by Profile</h3>
            <div class="space-y-2 overflow-y-auto max-h-64 pr-2">
                @forelse ($profiles as $profile)
    @php
        $count = $hiredLeads[$profile->id] ?? 0;
        if ($count > 10) {
            $bgClass = 'bg-blue-600 text-white';
            $btnBg = 'bg-white text-blue-700';
        } elseif ($count > 5) {
            $bgClass = 'bg-yellow-400 text-gray-900';
            $btnBg = 'bg-white text-yellow-700';
        } elseif ($count > 0) {
            $bgClass = 'bg-cyan-500 text-white';
            $btnBg = 'bg-white text-cyan-700';
        } else {
            $bgClass = 'bg-gray-200 text-gray-600';
            $btnBg = 'bg-gray-300 text-gray-600';
        }
    @endphp

    <div class="flex items-center justify-between rounded-lg p-3 {{ $bgClass }}">
        <div>
            <div class="text-sm font-semibold">{{ $profile->name ?? 'N/A' }}</div>
            <div class="text-sm font-bold">{{ $count }}</div>
        </div>
        @if($count > 0)
            <a href="{{ route('all.sales.leads', ['sales_person_id' => $profile->id, 'status' => 'Hired']) }}"
               class="ml-4 inline-flex items-center px-2 py-1 {{ $btnBg }} text-xs font-semibold rounded shadow hover:bg-opacity-90 transition duration-150"
               title="View Hired Leads">
                <i class="fas fa-eye mr-1"></i> View
            </a>
        @endif
    </div>
@empty
    <div class="text-gray-500 text-sm">No hired leads found.</div>
@endforelse

            </div>
        </div>

        <!-- Card 2: Bids -->
        <div class="bg-white rounded-xl shadow-lg p-4 flex flex-col">
            <!-- Top Summary -->
            <div class="flex flex-col justify-center items-center bg-indigo-600 text-white rounded-lg p-4 mb-4">
                <h5 class="text-lg font-semibold mb-2">Bids</h5>
                <h1 class="text-4xl font-bold">{{ $bidLeads->sum() }}</h1>
                <i class="fas fa-file-alt fa-2x mt-3 opacity-90"></i>
            </div>

            <!-- List by Profile -->
            <h3 class="text-xl font-semibold mb-3">Bids by Profile</h3>
            <div class="space-y-2 overflow-y-auto max-h-64 pr-2">
                @forelse ($profiles as $profile)
                    @php
                        $count = $bidLeads[$profile->id] ?? 0;
                        if ($count > 10) {
                            $bgClass = 'bg-purple-600 text-white';
                        } elseif ($count > 5) {
                            $bgClass = 'bg-pink-400 text-gray-900';
                        } elseif ($count > 0) {
                            $bgClass = 'bg-yellow-300 text-gray-800';
                        } else {
                            $bgClass = 'bg-gray-200 text-gray-600';
                        }
                    @endphp
                    <!-- <a href="{{ route('all.sales.leads', ['sales_person_id' => $profile->id, 'status' => 'Bids']) }}" class="block"> -->
                        <div class="flex justify-between items-center rounded-lg p-2 {{ $bgClass }} hover:bg-gray-300 transition duration-200">
                            <span class="text-sm font-semibold">{{ $profile->name ?? 'N/A' }}</span>
                            <span class="text-sm font-bold">{{ $count }}</span>
                        </div>
                    <!-- </a> -->
                @empty
                    <div class="text-gray-500 text-sm">No bids found.</div>
                @endforelse
            </div>
        </div>

        <!-- Card 3: Good Bids -->
        <div class="bg-white rounded-xl shadow-lg p-4 flex flex-col">
            <!-- Top Summary -->
            <div class="flex flex-col justify-center items-center bg-emerald-600 text-white rounded-lg p-4 mb-4">
                <h5 class="text-lg font-semibold mb-2">Good Bids</h5>
                <h1 class="text-4xl font-bold">{{ $goodBidLeads->sum() }}</h1>
                <i class="fas fa-thumbs-up fa-2x mt-3 opacity-90"></i>
            </div>

            <!-- List by Profile -->
            <h3 class="text-xl font-semibold mb-3">Good Bids by Profile</h3>
            <div class="space-y-2 overflow-y-auto max-h-64 pr-2">
                @forelse ($profiles as $profile)
                    @php
                        $count = $goodBidLeads[$profile->id] ?? 0;
                        if ($count > 10) {
                            $bgClass = 'bg-emerald-700 text-white';
                        } elseif ($count > 5) {
                            $bgClass = 'bg-lime-400 text-gray-900';
                        } elseif ($count > 0) {
                            $bgClass = 'bg-green-300 text-gray-800';
                        } else {
                            $bgClass = 'bg-gray-200 text-gray-600';
                        }
                    @endphp
                    <!-- <a href="{{ route('all.sales.leads', ['sales_person_id' => $profile->id, 'status' => 'GoodBids']) }}" class="block"> -->
                        <div class="flex justify-between items-center rounded-lg p-2 {{ $bgClass }} hover:bg-gray-300 transition duration-200">
                            <span class="text-sm font-semibold">{{ $profile->name ?? 'N/A' }}</span>
                            <span class="text-sm font-bold">{{ $count }}</span>
                        </div>
                    <!-- </a> -->
                @empty
                    <div class="text-gray-500 text-sm">No good bids found.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
    @foreach ([
        '2nd Day Follow-up' => $followUps2,
        '3rd Day Follow-up' => $followUps3,
        '7th Day Follow-up' => $followUps7,
        '30th Day Follow-up' => $followUps30
    ] as $title => $leads)
        <div class="bg-white rounded-xl shadow-lg p-4 flex flex-col">
            <!-- Header -->
            <div class="flex flex-col justify-center items-center bg-blue-600 text-white rounded-lg p-4 mb-4">
                <h1 class="text-3xl font-bold">{{ explode(' ', $title)[0] }}</h1>
                <h6 class="text-sm font-medium">{{ $title }}</h6>
            </div>

            <!-- Lead List -->
            <ul class="space-y-2 overflow-y-auto max-h-64 pr-1 text-sm">
                @if ($leads->isEmpty())
                    <li class="text-gray-500 italic">No Leads</li>
                @else
                    @foreach ($leads as $lead)
                        <li>
                            <a target="_blank" href="{{ url('sales-lead/' . $lead->id) }}"
                               class="block bg-gray-100 hover:bg-blue-100 text-gray-700 px-3 py-2 rounded transition">
                                {{ $lead->client_name ?? $lead->name ?? 'Unnamed Lead' }}
                            </a>
                        </li>
                    @endforeach
                @endif
            </ul>
        </div>
    @endforeach
</div>


@endsection
