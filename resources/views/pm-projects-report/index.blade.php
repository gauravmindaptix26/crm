@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
    
    {{-- Page Header --}}
    <div class="bg-gradient-to-r from-blue-700 to-purple-700 text-white rounded-lg shadow-lg p-6 mb-6">
        <h2 class="text-3xl font-bold">📊 PM Projects Report</h2>
        <p class="text-sm mt-1 opacity-80">Overview of project status by Project Managers</p>
    </div>

    {{-- Filters and Action --}}
    <form method="GET" action="{{ route('pm-projects-report') }}" class="mb-6 bg-white shadow-md rounded-lg p-4 flex flex-wrap items-end gap-4">
        @php
            $currentMonth = request('month', now()->format('m'));
            $currentYear = request('year', now()->format('Y'));
        @endphp

        <div>
            <label class="block text-sm font-medium text-gray-700">Department</label>
            <select name="department_id" class="form-select rounded border-gray-300 w-48">
                <option value="">All</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ $selectedDept == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Month</label>
            <select name="month" class="form-select rounded border-gray-300 w-32">
                @foreach(range(1,12) as $m)
                    <option value="{{ sprintf('%02d', $m) }}" {{ $currentMonth == sprintf('%02d', $m) ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Year</label>
            <select name="year" class="form-select rounded border-gray-300 w-32">
                @foreach(range(date('Y'), 2020) as $y)
                    <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <button class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 transition shadow-lg" type="submit">
                🔍 Search
            </button>
        </div>

        <!-- <div class="ml-auto">
            <a href="#" class="bg-green-600 text-white px-5 py-2 rounded hover:bg-green-700 transition shadow-lg">
                ➕ Add PM Project Team
            </a>
        </div> -->
    </form>
    <!-- Sales People Modal -->
<div id="salesPeopleModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6 relative">
        <h2 class="text-xl font-semibold mb-4">Sales Persons for <span id="modalStatusLabel"></span> Projects</h2>
        <ul id="salesPeopleList" class="list-disc list-inside text-gray-800 space-y-1">
            <!-- Dynamically filled via JS -->
        </ul>
        <button onclick="closeSalesPeopleModal()" class="absolute top-2 right-3 text-gray-500 hover:text-gray-800 text-xl">
            &times;
        </button>
    </div>
</div>


{{-- Report Table --}}
<div class="overflow-auto bg-white rounded-lg shadow-md">
    <table class="min-w-full border-collapse">
        <thead class="bg-blue-100 text-gray-800">
            <tr>
                <th class="px-4 py-3 text-left">PM</th>
                <th class="px-4 py-3 text-left">✅ Complete</th>
                <th class="px-4 py-3 text-left">⏸ Paused</th>
                <th class="px-4 py-3 text-left">⚠️ Issues</th>
                <th class="px-4 py-3 text-left">⛔ Hold</th>
                <!-- <th class="px-4 py-3 text-left">🔁 Rehire</th> -->
                <th class="px-4 py-3 text-left">🔧 Working</th>
            </tr>
        </thead>
        <tbody class="text-sm text-gray-700">
            @php
                $totals = [
                    'Complete' => 0,
                    'Paused' => 0,
                    'Issues' => 0,
                    'Hold' => 0,
                    'Rehire' => 0,
                    'Working' => 0,
                ];
            @endphp

            @forelse($report as $row)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium">{{ $row['pm']->name }}</td>

                    @foreach (['Complete', 'Paused', 'Issues', 'Hold', 'Working'] as $status)
    <td class="px-4 py-3">
        @if(($row['statusCounts'][$status] ?? 0) > 0)
            @if($status === 'Working')
                <a 
                    href="" 
                    class="text-blue-600 font-bold hover:underline focus:outline-none"
                >
                    {{ $row['statusCounts'][$status] }}
                </a>
            @else
                <button 
                    type="button" 
                    onclick="({{ $row['pm']->id }}, '{{ $status }}')" 
                    class="text-blue-600 font-bold hover:underline focus:outline-none"
                >
                    {{ $row['statusCounts'][$status] }}
                </button>
            @endif
        @else
            0
        @endif
    </td>
@endforeach

                </tr>

                @php
                    foreach ($totals as $key => $value) {
                        $totals[$key] += $row['statusCounts'][$key] ?? 0;
                    }
                @endphp
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-4 text-center text-gray-500">
                        No data available for the selected filters.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot class="bg-black text-white font-semibold">
            <tr>
                <td class="px-4 py-3 text-right">Total</td>
                <td class="px-4 py-3">{{ $totals['Complete'] }}</td>
                <td class="px-4 py-3">{{ $totals['Paused'] }}</td>
                <td class="px-4 py-3">{{ $totals['Issues'] }}</td>
                <td class="px-4 py-3">{{ $totals['Hold'] }}</td>
                <!-- <td class="px-4 py-3">{{ $totals['Rehire'] }}</td> -->
                <td class="px-4 py-3">{{ $totals['Working'] }}</td>
            </tr>
        </tfoot>
    </table>
</div>

{{-- Filtered Project List --}}
@if(request()->pm_id && request()->status && isset($filteredProjects))
    <div class="mt-6 bg-white p-5 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4 text-blue-600">
            🔧 Projects for PM ID {{ request()->pm_id }} — Status: {{ request()->status }}
        </h2>

        @if($filteredProjects->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full border text-sm">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="px-4 py-2 text-left">Project Name</th>
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2 text-left">Country</th>
                            <th class="px-4 py-2 text-left">Department</th>
                            <th class="px-4 py-2 text-left">Month</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @foreach($filteredProjects as $project)
                            <tr class="border-t">
                                <td class="px-4 py-2">{{ $project->name }}</td>
                                <td class="px-4 py-2">{{ $project->project_status }}</td>
                                <td class="px-4 py-2">{{ optional($project->country)->name }}</td>
                                <td class="px-4 py-2">{{ optional($project->department)->name }}</td>
                                <td class="px-4 py-2">{{ \Carbon\Carbon::parse($project->project_month)->format('F Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500">No matching projects found for this filter.</p>
        @endif
    </div>
@endif


<script>

function showSalesPeopleModal(pmId, status) {
    fetch(`pm-projects-report/sales-persons?pm_id=${pmId}&status=${status}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('modalStatusLabel').textContent = status;
            const list = document.getElementById('salesPeopleList');
            list.innerHTML = '';

            if (data.length === 0) {
                list.innerHTML = '<li class="text-gray-500">No sales persons found.</li>';
            } else {
                data.forEach(salesPerson => {
                    const li = document.createElement('li');
                    li.textContent = salesPerson.name;
                    list.appendChild(li);
                });
            }

            document.getElementById('salesPeopleModal').classList.remove('hidden');
            document.getElementById('salesPeopleModal').classList.add('flex');
        });
}


function closeSalesPeopleModal() {
    document.getElementById('salesPeopleModal').classList.add('hidden');
    document.getElementById('salesPeopleModal').classList.remove('flex');
}


    </script>
@endsection
