@extends('layouts.dashboard')
@section('title', 'Admin Dashboard')

@section('content')
<div class="p-6">
    <h2 class="text-3xl text-gray-800 mb-8 font-semibold text-gray-800">📊 Admin Dashboard</h2>

    {{-- Quick Links --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-10">
        <a href="{{ route('design-team-reports.index') }}"
           class="bg-white group bg-gradient-to-r rounded-xl p-5 shadow-lg hover:scale-105 transition-transform duration-300 ease-in-out cursor-pointer">
            <div class="text-lg font-semibold mb-2 text-black">Design Team Report</div>
            <div class="text-sm opacity-80 text-black">View Monthly Design Overview</div>
        </a>
        <a href="{{ route('pm-projects-report') }}"
           class="group bg-gradient-to-r bg-white rounded-xl p-5 shadow-lg hover:scale-105 transition-transform duration-300 ease-in-out cursor-pointer">
            <div class="text-lg font-semibold mb-2 text-black">PM Project Report</div>
            <div class="text-sm opacity-80 text-black">Analyze Project Management Data</div>
        </a>
        <a href="{{ route('team-reports.index') }}"
           class="group bg-gradient-to-r bg-white rounded-xl p-5 shadow-lg hover:scale-105 transition-transform duration-300 ease-in-out cursor-pointer">
            <div class="text-lg font-semibold mb-2 text-black">Team Reports</div>
            <div class="text-sm opacity-80 text-black">All Team-wise Stats</div>
        </a>
        <a href="{{ route('sales.team.projects') }}"
           class="group bg-white text-white rounded-xl p-5 shadow-lg hover:scale-105 transition-transform duration-300 ease-in-out cursor-pointer">
            <div class="text-lg font-semibold mb-2 text-black">Sales Team Projects</div>
            <div class="text-sm opacity-80 text-black">Sales Pipeline Reports</div>
        </a>
    </div>

    {{-- Filter Form --}}
    <form method="GET" action="" class="flex flex-wrap gap-4 mb-10 items-end bg-white p-5 rounded-xl shadow-md">
        <div>
            <label class="block text-sm font-semibold mb-1 text-gray-700">Select Month</label>
            <select name="month"
                    class="w-44 border border-gray-300 rounded-md px-3 py-2 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                @foreach(range(1, 12) as $month)
                    <option value="{{ $month }}" {{ $currentMonth == $month ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $month, 10)) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1 text-gray-700">Select Year</label>
            <select name="year"
                    class="w-44 border border-gray-300 rounded-md px-3 py-2 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                @foreach(range(date('Y'), 2020) as $year)
                    <option value="{{ $year }}" {{ $currentYear == $year ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <button type="submit"
                    class="text-white bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600 hover:bg-gradient-to-br px-5 py-2 rounded-md shadow hover:shadow-lg transition-all">
                Apply Filter
            </button>
        </div>
    </form>

    {{-- Total Amount Received Card --}}
    <div class="mb-10">
        <div class="w-full sm:w-2/3 md:w-1/2 lg:w-1/4">
            <div class="relative text-white bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600 hover:bg-gradient-to-br rounded-2xl p-4 shadow-xl 
                        overflow-hidden hover:scale-105 transition-transform duration-300 ease-in-out cursor-pointer">
                <div class="absolute right-4 top-4 text-white opacity-20 text-6xl pointer-events-none">
                    💰
                </div>
                <h4 class="text-1xl font-bold mb-2">Total Amount Received</h4>
                <p class="text-2xl font-extrabold tracking-wide">${{ number_format($totalAmountReceivedAllDepartments, 2) }}</p>
                <p class="text-sm mt-2 text-white/80 font-medium">
                    {{ date('F', mktime(0, 0, 0, $currentMonth, 10)) }} {{ $currentYear }}
                </p>
            </div>
        </div>
    </div>

    {{-- Department-wise Report --}}
    <h3 class="text-3xl text-gray-800 mb-8 font-semibold text-gray-800">📊 Department-wise Report</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($reportData as $data)
            @if ($data['department']->name !== 'Department 0')
                <div class="bg-gradient-to-tr from-blue-50 to-white rounded-2xl shadow-xl p-6 transform transition-all duration-300 hover:scale-105 hover:shadow-2xl border-t-4 border-teal-700">
                    <h4 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M4 4h16v16H4z" />
                        </svg>
                        {{ $data['department']->name }}
                    </h4>
                    <div class="space-y-4 text-sm text-gray-700">
                        @php
                            $items = [
                                ['label' => 'New Projects', 'color' => 'blue', 'count' => $data['new_projects'], 'status' => 'ALL'],
                                ['label' => 'Active Projects', 'color' => 'green', 'count' => $data['active_projects'], 'status' => 'Working'],
                                ['label' => 'Closed Projects', 'color' => 'red', 'count' => $data['closed_projects'], 'status' => 'Complete'],
                                ['label' => 'Paused Projects', 'color' => 'yellow', 'count' => $data['paused_projects'], 'status' => 'Paused'],
                                ['label' => 'Issue Projects', 'color' => 'orange', 'count' => $data['issue_projects'], 'status' => 'Issues']
                            ];
                        @endphp
                        @foreach ($items as $item)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-2.5 h-2.5 rounded-full bg-{{ $item['color'] }}-500"></div>
                                    <span>{{ $item['label'] }}</span>
                                </div>
                                @if ($item['label'] === 'New Projects')
                                    <span class="text-{{ $item['color'] }}-600 font-semibold">{{ $item['count'] }}</span>
                                @else
                                    <a href="{{ route('projects.byStatus', ['status' => $item['status']]) }}?department_id={{ $data['department']->id }}&report_month={{ $currentMonth }}&report_year={{ $currentYear }}"
                                       class="text-{{ $item['color'] }}-600 hover:underline font-semibold" target="_blank">
                                        {{ $item['count'] }}
                                    </a>
                                @endif
                            </div>
                        @endforeach
                        <div class="flex items-center justify-between pt-2 border-t mt-3">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3a3 3 0 100-6z"/>
                                </svg>
                                <span>Total Amount</span>
                            </div>
                            <span class="font-bold text-gray-800">${{ number_format($data['total_amount'], 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-green-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M12 8v4l3 3" />
                                    <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/>
                                </svg>
                                <span>Amount Received</span>
                            </div>
                            <span class="font-bold text-green-700">${{ number_format($data['amount_received'], 2) }}</span>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    {{-- Summary Cards --}}
    <div class="p-4 bg-gray-50">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"> 
            <!-- New Projects Card -->
            <div class="bg-white rounded-lg shadow-lg">
                <div class="p-4 rounded-t-lg text-white bg-blue-500 flex justify-between items-center">
                    <span class="text-3xl font-semibold">{{ array_sum(array_column($reportData, 'new_projects')) }}</span>
                    <span class="text-sm">New Projects</span>
                </div>
                <div class="p-2 divide-y divide-gray-200">
                    @foreach ($reportData as $data)
                        @if ($data['department']->name !== 'Department 0')
                            <div class="flex justify-between items-center py-2 px-2 text-sm text-gray-700">
                                <span>{{ $data['department']->name }}</span>
                                <a href="{{ route('projects.byStatus', ['status' => 'new']) }}?department_id={{ $data['department']->id }}&report_month={{ $currentMonth }}&report_year={{ $currentYear }}"
                                   class="font-medium text-blue-600 hover:underline">
                                    {{ $data['new_projects'] }}
                                </a>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Active Projects Card -->
            <div class="bg-white rounded-lg shadow-lg">
                <div class="p-4 rounded-t-lg text-white bg-purple-600 flex justify-between items-center">
                    <span class="text-3xl font-semibold">{{ array_sum(array_column($reportData, 'active_projects')) }}</span>
                    <span class="text-sm">Active Projects</span>
                </div>
                <div class="p-2 divide-y divide-gray-200">
                    @foreach ($reportData as $data)
                        @if ($data['department']->name !== 'Department 0')
                            <div class="flex justify-between items-center py-2 px-2 text-sm text-gray-700">
                                <span>{{ $data['department']->name }}</span>
                                <a href="{{ route('projects.byStatus', ['status' => 'Working']) }}?department_id={{ $data['department']->id }}&report_month={{ $currentMonth }}&report_year={{ $currentYear }}"
                                   class="font-medium text-purple-600 hover:underline">
                                    {{ $data['active_projects'] }}
                                </a>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Total Amount Card -->
            <div class="bg-white rounded-lg shadow-lg">
                <div class="p-4 rounded-t-lg text-white bg-green-500 flex justify-between items-center">
                    <span class="text-3xl font-semibold">$ {{ number_format(array_sum(array_column($reportData, 'total_amount')), 0) }}</span>
                    <span class="text-sm">Total Amount</span>
                </div>
                <div class="p-2 divide-y divide-gray-200">
                    @foreach ($reportData as $data)
                        @if ($data['department']->name !== 'Department 0')
                            <div class="flex justify-between items-center py-2 px-2 text-sm text-gray-700">
                                <span>{{ $data['department']->name }}</span>
                                <a href="{{ route('projects.byStatus', ['status' => 'ALL']) }}?department_id={{ $data['department']->id }}&report_month={{ $currentMonth }}&report_year={{ $currentYear }}"
                                   class="font-medium text-green-600 hover:underline">
                                    {{ number_format($data['total_amount'], 0) }}
                                </a>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Amount Received Card -->
            <div class="bg-white rounded-lg shadow-lg">
                <div class="p-4 rounded-t-lg text-white bg-orange-500 flex justify-between items-center">
                    <span class="text-3xl font-semibold">$ {{ number_format($totalAmountReceivedAllDepartments, 0) }}</span>
                    <span class="text-sm">Amount Received</span>
                </div>
                <div class="p-2 divide-y divide-gray-200">
                    @foreach ($reportData as $data)
                        @if ($data['department']->name !== 'Department 0')
                            <div class="flex justify-between items-center py-2 px-2 text-sm text-gray-700">
                                <span>{{ $data['department']->name }}</span>
                                <a href="{{ route('projects.byPayment', ['department_id' => $data['department']->id]) }}?report_month={{ $currentMonth }}&report_year={{ $currentYear }}"
                                   class="font-medium text-orange-600 hover:underline">
                                    {{ number_format($data['amount_received'], 0) }}
                                </a>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Closed Projects Card -->
            <div class="bg-white rounded-lg shadow-lg">
                <div class="p-4 rounded-t-lg text-white bg-slate-800 flex justify-between items-center">
                    <span class="text-3xl font-semibold">{{ array_sum(array_column($reportData, 'closed_projects')) }}</span>
                    <span class="text-sm">Closed Projects</span>
                </div>
                <div class="p-2 divide-y divide-gray-200">
                    @foreach ($reportData as $data)
                        @if ($data['department']->name !== 'Department 0')
                            <div class="flex justify-between items-center py-2 px-2 text-sm text-gray-700">
                                <span>{{ $data['department']->name }}</span>
                                <a href="{{ route('projects.byStatus', ['status' => 'Closed,Complete']) }}?department_id={{ $data['department']->id }}&report_month={{ $currentMonth }}&report_year={{ $currentYear }}"
                                   class="font-medium text-gray-600 hover:underline">
                                    {{ $data['closed_projects'] }}
                                </a>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Paused Projects Card -->
            <div class="bg-white rounded-lg shadow-lg">
                <div class="p-4 rounded-t-lg text-white bg-red-600 flex justify-between items-center">
                    <span class="text-3xl font-semibold">{{ array_sum(array_column($reportData, 'paused_projects')) }}</span>
                    <span class="text-sm">Paused Projects</span>
                </div>
                <div class="p-2 divide-y divide-gray-200">
                    @foreach ($reportData as $data)
                        @if ($data['department']->name !== 'Department 0')
                            <div class="flex justify-between items-center py-2 px-2 text-sm text-gray-700">
                                <span>{{ $data['department']->name }}</span>
                                <a href="{{ route('projects.byStatus', ['status' => 'Paused']) }}?department_id={{ $data['department']->id }}&report_month={{ $currentMonth }}&report_year={{ $currentYear }}"
                                   class="font-medium text-red-600 hover:underline">
                                    {{ $data['paused_projects'] }}
                                </a>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Issue Projects Card -->
            <div class="bg-white rounded-lg shadow-lg">
                <div class="p-4 rounded-t-lg text-white bg-violet-600 flex justify-between items-center">
                    <span class="text-3xl font-semibold">{{ array_sum(array_column($reportData, 'issue_projects')) }}</span>
                    <span class="text-sm">Issue Projects</span>
                </div>
                <div class="p-2 divide-y divide-gray-200">
                    @foreach ($reportData as $data)
                        @if ($data['department']->name !== 'Department 0')
                            <div class="flex justify-between items-center py-2 px-2 text-sm text-gray-700">
                                <span>{{ $data['department']->name }}</span>
                                <a href="{{ route('projects.byStatus', ['status' => 'Issues']) }}?department_id={{ $data['department']->id }}&report_month={{ $currentMonth }}&report_year={{ $currentYear }}"
                                   class="font-medium text-violet-600 hover:underline">
                                    {{ $data['issue_projects'] }}
                                </a>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection