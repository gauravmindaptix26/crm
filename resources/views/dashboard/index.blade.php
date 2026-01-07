@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-4">
    <h2 class="text-3xl font-bold mb-6 text-gray-800">🎨 Design Team Report</h2>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('design-team-reports.index') }}" class="mb-8 bg-white shadow rounded p-4 flex flex-wrap items-end gap-4">
        @php
            $currentMonth = request('month', now()->format('m'));
            $currentYear = request('year', now()->format('Y'));
        @endphp

        <div class="flex-1 min-w-[150px]">
            <label for="month" class="block text-sm font-semibold text-gray-700 mb-1">Select Month</label>
            <select name="month" id="month" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @foreach(range(1, 12) as $month)
                    <option value="{{ sprintf('%02d', $month) }}" {{ $currentMonth == sprintf('%02d', $month) ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($month)->format('F') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex-1 min-w-[150px]">
            <label for="year" class="block text-sm font-semibold text-gray-700 mb-1">Select Year</label>
            <select name="year" id="year" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @foreach(range(date('Y'), date('Y') - 5) as $year)
                    <option value="{{ $year }}" {{ $currentYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
        </div>

        <div class="mt-6">
            <button type="submit" class="bg-blue-600 text-white font-semibold px-6 py-2 rounded hover:bg-blue-700 transition">
                🔍 Search
            </button>
        </div>
    </form>

    <!-- Overview Section -->
    <div class="bg-white p-6 rounded shadow mb-8">
        <h3 class="text-2xl font-semibold text-gray-700 mb-4">📊 Overview</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-gray-700 text-sm">
            <div class="bg-blue-50 p-4 rounded shadow-sm">
                <div class="font-semibold text-gray-600">Total Projects</div>
                <div class="text-lg font-bold text-blue-800">{{ $totalProjects }}</div>
            </div>
            <div class="bg-green-50 p-4 rounded shadow-sm">
                <div class="font-semibold text-gray-600">Total Budget</div>
                <div class="text-lg font-bold text-green-700">${{ number_format($totalBudget, 2) }}</div>
            </div>
            <div class="bg-yellow-50 p-4 rounded shadow-sm">
                <div class="font-semibold text-gray-600">Estimated Hours</div>
                <div class="text-lg font-bold text-yellow-700">{{ $totalEstimatedHours ? $totalEstimatedHours . ' hrs' : '-' }}</div>
            </div>
            <div class="bg-purple-50 p-4 rounded shadow-sm">
                <div class="font-semibold text-gray-600">Worked Hours</div>
                <div class="text-lg font-bold text-purple-700">{{ $totalWorkedHours ? $totalWorkedHours . ' hrs' : '-' }}</div>
            </div>
        </div>
    </div>

    <!-- Employee Stats -->
    <div class="bg-white p-6 rounded shadow mb-8">
        <h3 class="text-2xl font-semibold text-gray-700 mb-4">👥 Employee Statistics</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($employeeStats as $stat)
                <div class="bg-gray-50 p-4 rounded border border-gray-200 shadow-sm">
                    <div class="font-semibold text-gray-700">{{ $stat['name'] }}</div>
                    <div class="text-blue-600 font-bold">{{ $stat['hours'] > 0 ? $stat['hours'] . ' hrs' : '0 hrs' }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Projects Table -->
    <div class="bg-white p-6 rounded shadow">
        <h3 class="text-2xl font-semibold text-gray-700 mb-4">📁 Projects List</h3>
        <div class="overflow-auto">
            <table class="min-w-full text-sm border border-gray-200">
                <thead class="bg-gray-100 text-gray-700 font-semibold">
                    <tr>
                        <th class="px-4 py-2 text-left">Project Name</th>
                        <th class="px-4 py-2 text-left">Budget</th>
                        <th class="px-4 py-2 text-left">Estimated Hours</th>
                        <th class="px-4 py-2 text-left">Worked Hours</th>
                        @foreach($employees as $employee)
                            <th class="px-4 py-2 text-left">{{ $employee->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @php
                        $totalEmployeeHours = [];
                        foreach($employees as $employee) {
                            $totalEmployeeHours[$employee->id] = 0;
                        }
                        $totalProjectBudget = 0;
                        $totalEstimatedHours = 0;
                        $totalWorkedHours = 0;
                    @endphp

                    @foreach($projects as $project)
                        @php
                            $status = strtolower($project->project_status);
                            $rowClass = match ($status) {
                                'paused' => 'bg-red-50 text-red-700 font-semibold',
                                'complete' => 'bg-green-50 text-green-700 font-semibold',
                                default => '',
                            };
                        @endphp
                        <tr class="border-t hover:bg-gray-50 {{ $rowClass }}">
                            <td class="px-4 py-2">{{ $project->name_or_url ?? '-' }}</td>
                            <td class="px-4 py-2">${{ number_format($project->price, 2) }}</td>
                            <td class="px-4 py-2">{{ $project->estimated_hours ? $project->estimated_hours . ' hrs' : '-' }}</td>
                            <td class="px-4 py-2">{{ $project->dsrs->sum('hours') ? $project->dsrs->sum('hours') . ' hrs' : '-' }}</td>

                            @foreach($employees as $employee)
                                @php
                                    $assignedHours = $employee->dsrs->where('project_id', $project->id)->sum('hours');
                                    $totalEmployeeHours[$employee->id] += $assignedHours;
                                @endphp
                                <td class="px-4 py-2">{{ $assignedHours > 0 ? $assignedHours . ' hrs' : '-' }}</td>
                            @endforeach
                        </tr>
                        @php
                            $totalProjectBudget += $project->price;
                            $totalEstimatedHours += $project->estimated_hours;
                            $totalWorkedHours += $project->dsrs->sum('hours');
                        @endphp
                    @endforeach
                </tbody>

                <tfoot class="bg-gray-100 font-semibold">
                    <tr>
                        <td class="px-4 py-2">Totals:</td>
                        <td class="px-4 py-2">${{ number_format($totalProjectBudget, 2) }}</td>
                        <td class="px-4 py-2">{{ $totalEstimatedHours ? $totalEstimatedHours . ' hrs' : '-' }}</td>
                        <td class="px-4 py-2">{{ $totalWorkedHours ? $totalWorkedHours . ' hrs' : '-' }}</td>
                        @foreach($employees as $employee)
                            <td class="px-4 py-2">
                                {{ $totalEmployeeHours[$employee->id] > 0 ? $totalEmployeeHours[$employee->id] . ' hrs' : '-' }}
                            </td>
                        @endforeach
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
