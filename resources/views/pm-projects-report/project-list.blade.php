@extends('layouts.dashboard')

@section('content')
<div class="p-6 bg-white shadow rounded-lg">
    <h1 class="text-2xl font-bold mb-4">Projects Report - {{ $pm->name ?? '-' }} | Status: {{ ucfirst($status) }}</h1>

    {{-- Summary Section --}}
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


    {{-- Filter Form --}}
<form method="GET" action="{{ url()->current() }}" class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6 items-end">

    {{-- Existing Filters: Project Manager, Sales Person, Employee, Department --}}
    {{-- Add new filters below --}}

    <!-- Project Manager Filter -->
<div>
    <label for="project_manager" class="block text-sm font-medium text-gray-700">Project Manager</label>
    <select name="project_manager" id="project_manager" class="form-select w-full mt-1">
        <option value="">-- Select --</option>
        @foreach($projectManagers as $manager)
            <option value="{{ $manager->id }}" {{ request('project_manager', request('pm_id')) == $manager->id ? 'selected' : '' }}>
                {{ $manager->name }}
            </option>
        @endforeach
    </select>
</div>

    {{-- Team Lead --}}
    <div>
        <label for="team_lead" class="block text-sm text-gray-600 mb-1">Team Lead</label>
        <select name="team_lead" id="team_lead" class="form-select rounded-lg border-gray-300 w-full">
            <option value="">-- Select --</option>
            @foreach ($teamLeads as $lead)
                <option value="{{ $lead->id }}" {{ request('team_lead') == $lead->id ? 'selected' : '' }}>
                    {{ $lead->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Hired From --}}
    <div>
        <label for="hired_from" class="block text-sm text-gray-600 mb-1">Hired From</label>
        <select name="hired_from" id="hired_from" class="form-select rounded-lg border-gray-300 w-full">
            <option value="">-- Select --</option>
            <option value="Upwork" {{ request('hired_from') == 'Upwork' ? 'selected' : '' }}>Upwork</option>
            <option value="Website" {{ request('hired_from') == 'Website' ? 'selected' : '' }}>Website</option>
            <option value="Referral" {{ request('hired_from') == 'Referral' ? 'selected' : '' }}>Referral</option>
            <option value="Other" {{ request('hired_from') == 'Other' ? 'selected' : '' }}>Other</option>
        </select>
    </div>

    {{-- Month --}}
<div>
    <label for="month" class="block text-sm text-gray-600 mb-1">Month</label>
    <select name="month" id="month" class="form-select rounded-lg border-gray-300 w-full">
        @foreach(range(1, 12) as $m)
            <option value="{{ $m }}" 
                {{ (request('month', date('n')) == $m) ? 'selected' : '' }}>
                {{ date('F', mktime(0, 0, 0, $m, 10)) }}
            </option>
        @endforeach
    </select>
</div>

{{-- Year --}}
<div>
    <label for="year" class="block text-sm text-gray-600 mb-1">Year</label>
    <select name="year" id="year" class="form-select rounded-lg border-gray-300 w-full">
        @foreach(range(date('Y') - 5, date('Y') + 1) as $y)
            <option value="{{ $y }}" 
                {{ (request('year', date('Y')) == $y) ? 'selected' : '' }}>
                {{ $y }}
            </option>
        @endforeach
    </select>
</div>


    {{-- Status --}}
    <div>
        <label for="status" class="block text-sm text-gray-600 mb-1">Status</label>
        <select name="status" id="status" class="form-select rounded-lg border-gray-300 w-full">
            <option value="">-- Select --</option>
            @foreach(['Working', 'Complete', 'Paused', 'Hold', 'Issues', 'Temp Hold', 'Closed'] as $status)
                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                    {{ $status }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Client Type --}}
    <div>
        <label for="client_type" class="block text-sm text-gray-600 mb-1">Client Type</label>
        <select name="client_type" id="client_type" class="form-select rounded-lg border-gray-300 w-full">
            <option value="">-- Select --</option>
            <option value="New Client" {{ request('client_type') == 'New Client' ? 'selected' : '' }}>New Client</option>
            <option value="Old Client" {{ request('client_type') == 'Old Client' ? 'selected' : '' }}>Old Client</option>
        </select>
    </div>

    {{-- Submit Button --}}
    <div class="self-end">
        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700">
            Filter
        </button>
    </div>
</form>


    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="min-w-full table-auto border-collapse border border-gray-200 text-sm">
            <thead class="bg-gray-100 text-gray-700 font-semibold">
                <tr>
                    <th class="border px-4 py-2">#</th>
                    <th class="border px-4 py-2">Name / URL</th>
                    <th class="border px-4 py-2">Office Details</th>
                    <th class="border px-4 py-2">Price / Hours</th>
                    <th class="border px-4 py-2">Added On</th>
                    <th class="border px-4 py-2">Type</th>
                    <th class="border px-4 py-2">Project Type</th>
                    <th class="border px-4 py-2">Status</th>
                    <th class="border px-4 py-2">Client Details</th>
                    <th class="border px-4 py-2">Action</th>
                    <th class="border px-4 py-2">Attachment</th>
                    <th class="border px-4 py-2">Description</th>
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
                        $statusRaw = $project->project_status ?? 'working';
                        $status = strtolower(trim($statusRaw));
                        $statusClass = $statusColors[$status] ?? 'bg-gray-200 text-gray-800';
                    @endphp
                    <tr class="hover:bg-gray-50 text-gray-800">
                        <td class="border px-4 py-2">{{ $index + 1 }}</td>
                        <td class="border px-4 py-2">
                            <strong>{{ $project->name_or_url }}</strong><br>
                            <a href="{{ $project->dashboard_url }}" class="text-blue-600 text-xs underline" target="_blank">Dashboard</a>
                        </td>
                        <td class="border px-4 py-2 leading-5">
                            <strong>Business:</strong> {{ $project->business_type }}<br>
                            <strong>Grade:</strong> {{ $project->project_grade }}<br>
                            <strong>PM:</strong> {{ optional($project->projectManager)->name ?? '-' }}<br>
                            <strong>TL:</strong> {{ optional($project->teamLead)->name ?? '-' }}<br>
                            <strong>Sales:</strong> {{ optional($project->salesPerson)->name ?? '-' }}<br>
                            <strong>Dept:</strong> {{ optional($project->department)->name ?? '-' }}<br>
                            <strong>Employee:</strong> {{ optional($project->assignMainEmployee)->name ?? '-' }}
                        </td>
                        <td class="border px-4 py-2">
                            <strong>Price:</strong> ${{ $project->price ?? '0' }}<br>
                            <strong>Hours:</strong> {{ $project->estimated_hours ?? 'N/A' }}<br>
                            <strong>Received:</strong> ${{ $project->received_price ?? '0' }}<br>
                            <strong>Duration:</strong> {{ $project->duration_days ?? '-' }} days
                        </td>
                        <td class="border px-4 py-2">{{ $project->created_at->format('d M Y') }}</td>
                        <td class="border px-4 py-2">
                            <strong>Project:</strong> {{ $project->project_type }}<br>
                            <strong>Report:</strong> {{ $project->report_type }}<br>
                            <strong>Client Type:</strong> {{ $project->client_type }}
                        </td>
                        <td class="border px-4 py-2 leading-5">
                            <strong>Category:</strong> {{ optional($project->projectCategory)->name ?? '-' }}<br>
                            <strong>Sub:</strong> {{ optional($project->projectSubCategory)->name ?? '-' }}<br>
                            <strong>Country:</strong> {{ optional($project->country)->name ?? '-' }}
                        </td>
                        <td class="border px-4 py-2">
                            <span class="inline-block px-2 py-1 text-xs rounded-full {{ $statusClass }}">
                                {{ ucfirst($statusRaw) }}
                            </span>
                        </td>
                        <td class="border px-4 py-2 text-xs leading-5">
                            <strong>Name:</strong> {{ $project->client_name }}<br>
                            <strong>Email:</strong> {{ $project->client_email }}<br>
                            <strong>Info:</strong>
                            <span class="tooltip">
                                {{ Str::limit($project->client_other_info, 20) }}
                                <span class="tooltiptext">{{ $project->client_other_info }}</span>
                            </span>
                        </td>
                        <td class="border px-4 py-2">
                            <div class="flex flex-col space-y-1">
                                <a href="{{ route('projects.status', $project->id) }}"
                                   class="{{ $project->project_status == 'Working' ? 'bg-green-500' : 'bg-blue-500' }} text-white px-3 py-1 rounded text-xs text-center">
                                    Update Status
                                </a>
                                <a href="{{ route('project_monthly_reports.index', ['project_id' => $project->id]) }}"
                                   class="bg-green-600 text-white px-3 py-1 rounded text-xs text-center hover:bg-green-700">
                                    Monthly Report
                                </a>
                                <a href="{{ route('project_payments.index', ['project_id' => $project->id]) }}"
                                   class="bg-indigo-600 text-white px-3 py-1 rounded text-xs text-center hover:bg-indigo-700">
                                    Payment
                                </a>
                                <a href="{{ route('projects.show', $project->id) }}"
                                   class="bg-blue-600 text-white px-3 py-1 rounded text-xs text-center hover:bg-blue-700">
                                    <i class="fas fa-eye mr-1"></i> View
                                </a>
                                <a href="{{ route('projects.edit.page', $project->id) }}"
                                   class="bg-yellow-500 text-white px-3 py-1 rounded text-xs text-center hover:bg-yellow-600">
                                    Edit
                                </a>
                            </div>
                        </td>
                        <td class="border px-4 py-2 text-xs space-y-1 max-w-xs break-words">
                            @forelse ($project->attachments as $attachment)
                                <a href="{{ asset('storage/' . $attachment->file_path) }}"
                                   target="_blank"
                                   class="text-blue-600 underline block truncate"
                                   title="{{ basename($attachment->file_path) }}">
                                   {{ basename($attachment->file_path) }}
                                </a>
                            @empty
                                <span class="text-gray-500">No attachments</span>
                            @endforelse
                            <a href="{{ route('projects.attachments.create', $project->id) }}"
                               class="inline-block mt-2 px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-xs">
                                Add Attachment
                            </a>
                        </td>
                        <td class="border px-4 py-2 text-xs">{{ Str::limit($project->description, 100) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center text-gray-500 py-4">No projects found for this status.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
