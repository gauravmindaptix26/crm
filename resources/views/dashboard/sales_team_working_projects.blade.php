@extends('layouts.dashboard')

@section('content')
<div class="p-6 bg-white shadow rounded-lg">
<h1 class="text-2xl font-bold mb-4">
    Sales Working Projects | Status: {{ ucfirst($status ?? 'Working') }}
</h1>


    {{-- Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-100 p-4 rounded-lg shadow text-center">
            <p class="text-sm text-gray-600">Total Projects</p>
            <h2 class="text-2xl font-bold text-blue-700">{{ $projects->count() }}</h2>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div class="bg-yellow-100 p-4 rounded-lg shadow text-center">
            <p class="text-sm text-gray-600">Prediction Amount ({{ $monthName }})</p>
            <h2 class="text-2xl font-bold text-yellow-700">${{ number_format($predictionAmount, 2) }}</h2>
        </div>

        <div class="bg-indigo-100 p-4 rounded-lg shadow text-center">
            <p class="text-sm text-gray-600">Amount Received ({{ $monthName }})</p>
            <h2 class="text-2xl font-bold text-indigo-700">${{ number_format($amountReceived, 2) }}</h2>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ url()->current() }}" class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6 items-end">
        <div>
            <label class="block text-sm text-gray-600 mb-1">Project Manager</label>
            <select name="project_manager" class="form-select w-full">
                <option value="">-- Select --</option>
                @foreach($projectManagers as $manager)
                    <option value="{{ $manager->id }}" {{ request('project_manager') == $manager->id ? 'selected' : '' }}>
                        {{ $manager->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">Team Lead</label>
            <select name="team_lead" class="form-select w-full">
                <option value="">-- Select --</option>
                @foreach($teamLeads as $lead)
                    <option value="{{ $lead->id }}" {{ request('team_lead') == $lead->id ? 'selected' : '' }}>
                        {{ $lead->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
    <label class="block text-sm text-gray-600 mb-1">Sales Person</label>
    <select name="sales_person" class="form-select w-full">
        <option value="">-- Select --</option>
        @foreach($salesPersons as $sales)
            <option value="{{ $sales->id }}" {{ request('sales_person') == $sales->id ? 'selected' : '' }}>
                {{ $sales->name }}
            </option>
        @endforeach
    </select>
</div>


        <div>
            <label class="block text-sm text-gray-600 mb-1">Month</label>
            <select name="month" class="form-select w-full">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ request('month', now()->month) == $m ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $m, 10)) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">Year</label>
            <select name="year" class="form-select w-full">
                @foreach(range(date('Y') - 5, date('Y') + 1) as $y)
                    <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">Status</label>
            <select name="status" class="form-select w-full">
                <option value="">-- Select --</option>
                @foreach(['Working', 'Complete', 'Paused', 'Hold', 'Issues', 'Temp Hold', 'Closed'] as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 w-full">Filter</button>
        </div>
    </form>

    {{-- Table --}}
<div class="overflow-x-auto">
    <table class="min-w-full table-auto border-collapse border border-gray-300 text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="border px-4 py-2">#</th>
                <th class="border px-4 py-2">Name / URL</th>
                <th class="border px-4 py-2">Office Info</th>
                <th class="border px-4 py-2">Price / Received / Duration</th>
                <th class="border px-4 py-2">Action</th>
                <th class="border px-4 py-2">Date</th>
                <th class="border px-4 py-2">Client Type</th>
                <th class="border px-4 py-2">Category</th>
                <th class="border px-4 py-2">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($projects as $index => $project)
                @php
                    $statusColors = [
                        'complete' => 'bg-blue-200 text-blue-800',
                        'working' => 'bg-green-200 text-green-800',
                        'hold' => 'bg-yellow-200 text-yellow-800',
                        'paused' => 'bg-purple-200 text-purple-800',
                        'issues' => 'bg-red-200 text-red-800',
                        'temp hold' => 'bg-orange-200 text-orange-800',
                        'closed' => 'bg-gray-200 text-gray-800',
                    ];
                    $statusKey = strtolower(trim($project->project_status ?? 'working'));
                    $statusClass = $statusColors[$statusKey] ?? 'bg-gray-200 text-gray-800';
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="border px-4 py-2">{{ $index + 1 }}</td>

                    <td class="border px-4 py-2">
                        <strong>{{ $project->name_or_url }}</strong><br>
                        <a href="{{ $project->dashboard_url }}" target="_blank" class="text-blue-600 text-xs underline">Dashboard</a>
                    </td>

                    <td class="border px-4 py-2 text-xs leading-5">
                        <strong>Sales:</strong> {{ optional($project->salesPerson)->name ?? '-' }}<br>
                        <strong>PM:</strong> {{ optional($project->projectManager)->name ?? '-' }}<br>
                        <strong>TL:</strong> {{ optional($project->teamLead)->name ?? '-' }}
                    </td>

                    <td class="border px-4 py-2 text-sm leading-5">
                        <div><strong>Price:</strong> ${{ number_format($project->price ?? 0, 2) }}</div>
                        <div class="my-2">
                            <a target="_blank" href="{{ route('project_payments.index', ['project_id' => $project->id]) }}">
                                <span class="inline-block bg-blue-500 text-white font-semibold px-3 py-1 rounded-md hover:bg-blue-600 transition whitespace-nowrap">
                                    Received: ${{ number_format($project->received_amount, 2) }}
                                </span>
                            </a>
                        </div>
                        <div><strong>Hours:</strong> {{ $project->estimated_hours ?? 'N/A' }}</div>
                        <div class="mt-2">
                            @if($project->duration_days)
                                <span class="inline-block bg-indigo-500 text-white text-xs font-semibold px-2 py-1 rounded">
                                    Duration: {{ $project->duration_days }} day(s)
                                </span>
                            @else
                                <span class="text-gray-400 text-xs">Duration: N/A</span>
                            @endif
                        </div>
                    </td>

                    <td class="border px-4 py-2">
    <div class="flex flex-col gap-2">
        {{-- Update Status --}}
        <a href="{{ route('projects.status', $project->id) }}"
           class="text-white px-3 py-1 rounded-md shadow transition duration-300 inline-flex items-center gap-1 text-xs
           {{ $project->project_status == 'Working' ? 'bg-green-600 hover:bg-green-700' : 'bg-blue-600 hover:bg-blue-700' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M4 4v5h.582M20 20v-5h-.581M5.582 9A7 7 0 0119 15.418M18.418 15A7 7 0 015.582 9"/>
            </svg>
            Status
        </a>

        {{-- Monthly Report --}}
        <a href="{{ route('project_monthly_reports.index', ['project_id' => $project->id]) }}"
           class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-md shadow inline-flex items-center gap-1 text-xs">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12h6m-6 4h6m-3-8h.01M5 7h14a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2z"/>
            </svg>
            Report
        </a>

        {{-- Payment Details --}}
        <a href="{{ route('project_payments.index', ['project_id' => $project->id]) }}"
           class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded-md shadow inline-flex items-center gap-1 text-xs">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <rect width="20" height="14" x="2" y="5" rx="2" ry="2"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M2 10h20"/>
            </svg>
            Payment
        </a>

        {{-- View Project --}}
        <a href="{{ route('projects.show', $project->id) }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md shadow inline-flex items-center gap-1 text-xs">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            View
        </a>

        {{-- Edit Project --}}
        <a href="{{ route('projects.edit.page', $project->id) }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md shadow inline-flex items-center gap-1 text-xs">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M11 5h2M15 9l-6 6M7 17h10"/>
            </svg>
            Edit
        </a>
    </div>
</td>


                    <td class="border px-4 py-2">{{ $project->created_at->format('d M Y') }}</td>
                    <td class="border px-4 py-2">{{ $project->client_type }}</td>
                    <td class="border px-4 py-2">
                        {{ optional($project->projectCategory)->name ?? '-' }}<br>
                        {{ optional($project->projectSubCategory)->name ?? '-' }}
                    </td>
                    <td class="border px-4 py-2">
                        <span class="px-2 py-1 text-xs rounded-full {{ $statusClass }}">{{ ucfirst($statusKey) }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center text-gray-500 py-4">No projects found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    </div>
</div>
@endsection
