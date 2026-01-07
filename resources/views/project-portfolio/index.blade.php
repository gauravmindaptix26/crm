@extends('layouts.dashboard')

@section('content')
<style>
    /* Sticky header */
    .table-container {
        max-height: 600px; /* Adjust height based on preference */
        overflow-y: auto;
    }
    thead tr th {
        position: sticky;
        top: 0;
        background: #f8f8f8;
        z-index: 10;
    }
    .tooltip {
    position: relative;
    cursor: pointer;
}
.tooltip .tooltiptext {
    visibility: hidden;
    width: 250px;
    background-color: #555;
    color: #fff;
    text-align: left;
    border-radius: 6px;
    padding: 10px;
    position: absolute;
    z-index: 1;
    bottom: 125%; 
    left: 0;
    opacity: 0;
    transition: opacity 0.3s;
}
.tooltip:hover .tooltiptext {
    visibility: visible;
    opacity: 1;
}
    /* Alternating row colors */
    tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    /* Tooltip */
    .tooltip {
        position: relative;
        display: inline-block;
        cursor: pointer;
    }
    .tooltip .tooltiptext {
        visibility: hidden;
        width: 200px;
        background-color: black;
        color: #fff;
        text-align: center;
        border-radius: 6px;
        padding: 5px;
        position: absolute;
        z-index: 1;
        bottom: 100%;
        left: 50%;
        margin-left: -100px;
        opacity: 0;
        transition: opacity 0.3s;
    }
    .tooltip:hover .tooltiptext {
        visibility: visible;
        opacity: 1;
    }
</style>

<div class="container mx-auto p-6">
    <!-- Page Header -->
    <!-- <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">Portfolio Projects</h2>
        <button onclick="openModal('projectFormModal')" class="bg-blue-600 text-white px-4 py-2 rounded">+ Add Project</button>
    </div> -->

 <!-- Filters Section -->
    <div id="filtersSection" class="hidden mt-4">
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-semibold">Project Grade</label>
                <select id="filter_grade" class="w-full px-3 py-2 border rounded">
                    <option value="">Select</option>
                    <option value="A">A</option>
                    <option value="AA">AA</option>
                    <option value="AAA">AAA</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold">Project Manager</label>
                <select id="filter_manager" class="w-full px-3 py-2 border rounded">
                    <option value="">-- Select --</option>
                    @foreach ($projectManagers as $manager)
                        <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="pt-6">
                <button class="bg-blue-600 text-white px-4 py-2 rounded">Apply</button>
            </div>
        </div>
    </div>




    <form method="GET" action="{{ route('project-portfolios.index') }}" class="mb-6 grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
    {{-- Project Manager --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Project Manager</label>
        <select name="project_manager_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All</option>
            @foreach($projectManagers as $manager)
                <option value="{{ $manager->id }}" {{ request('project_manager_id') == $manager->id ? 'selected' : '' }}>
                    {{ $manager->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Sales Person --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Sales Person</label>
        <select name="sales_person_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All</option>
            @foreach($salesPersons as $sales)
                <option value="{{ $sales->id }}" {{ request('sales_person_id') == $sales->id ? 'selected' : '' }}>
                    {{ $sales->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Department --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
        <select name="department_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                    {{ $dept->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Assign Main Employee --}}
    <div>
        <label for="assign_main_employee_id" class="block text-sm font-medium text-gray-700 mb-1">Assign Main Employee</label>
        <select name="assign_main_employee_id" id="assign_main_employee_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All</option>
            @foreach($employees as $employee)
                <option value="{{ $employee->id }}" {{ request('assign_main_employee_id') == $employee->id ? 'selected' : '' }}>
                    {{ $employee->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Project Status --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Project Status</label>
        <select name="project_status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:border-blue-500">
            <option value="">All</option>
            <option value="Complete" {{ request('project_status') == 'Complete' ? 'selected' : '' }}>Complete</option>
            <option value="Hold" {{ request('project_status') == 'Hold' ? 'selected' : '' }}>Hold</option>
            <option value="Paused" {{ request('project_status') == 'Paused' ? 'selected' : '' }}>Paused</option>
            <option value="Working" {{ request('project_status') == 'Working' ? 'selected' : '' }}>Working</option>
            <option value="Issues" {{ request('project_status') == 'Issues' ? 'selected' : '' }}>Issues</option>
            <option value="Temp Hold" {{ request('project_status') == 'Temp Hold' ? 'selected' : '' }}>Temp Hold</option>
            <option value="Closed" {{ request('project_status') == 'Closed' ? 'selected' : '' }}>Closed</option>
        </select>
    </div>
    {{-- Client Type --}}
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Client Type</label>
    <select name="client_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:border-blue-500">
        <option value="">All</option>
        <option value="New Client" {{ request('client_type') == 'New Client' ? 'selected' : '' }}>New Client</option>
        <option value="Old Client" {{ request('client_type') == 'Old Client' ? 'selected' : '' }}>Old Client</option>
    </select>
</div>


    {{-- Filter Button --}}
    <div class="flex">
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow">
            Filter
        </button>
    </div>
</form>







  <!-- Project Listing Table -->
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div class="overflow-x-auto max-h-[500px]">
        <table class="w-full border-collapse border border-gray-300">
                <tr>
                    <th class="border border-gray-300 px-4 py-2">#</th>
                    <th class="border border-gray-300 px-4 py-2">Name/URL</th>
                    <th class="border border-gray-300 px-4 py-2">Office Details</th>
                    <th class="border border-gray-300 px-4 py-2">Hours</th>
                    <th class="border border-gray-300 px-4 py-2">Added On</th>
                    <th class="border border-gray-300 px-4 py-2">Type</th>
                    <th class="border border-gray-300 px-4 py-2">Project Type</th>
                    <th class="border border-gray-300 px-4 py-2">Project Status</th>
                    <th class="border border-gray-300 px-4 py-2">Action</th>
                    <th class="border border-gray-300 px-4 py-2">Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach($projects as $key => $project)
                <tr>
                <td class="border border-gray-300 px-4 py-2">{{ $projects->firstItem() + $key }}</td>
                <td class="border border-gray-300 px-4 py-2">
                        <strong>{{ $project->name_or_url }}</strong><br>
                        <a href="{{ $project->dashboard_url }}" target="_blank" class="text-blue-500">Dashboard Link</a>
                    </td>
                    <td class="border border-gray-300 px-4 py-2">
                        <strong>Business:</strong> {{ $project->business_type }}<br>
                        <strong>Grade:</strong> {{ $project->project_grade }}<br>
                        <strong>PM:</strong> {{ optional($project->projectManager)->name ?? '-' }}<br>
                        <strong>Sales:</strong> {{ optional($project->salesPerson)->name ?? '-' }}<br>
                        <strong>Department:</strong> {{ optional($project->department)->name ?? '-' }}<br>
                        <strong>Assigned Employee:</strong> {{ optional($project->assignMainEmployee)->name ?? '-' }}
                    </td>
                    <td class="border border-gray-300 px-4 py-2">
    <strong>Project Duration:</strong> {{ $project->estimated_hours ? $project->estimated_hours . ' hours' : 'N/A' }}
</td>

                    <td class="border border-gray-300 px-4 py-2">
                        {{ $project->created_at->format('d-M-Y') }}
                    </td>
                    <td class="border border-gray-300 px-4 py-2">
                        <strong>Project:</strong> {{ $project->project_type }}<br>
                        <strong>Report:</strong> {{ $project->report_type }}<br>
                        <strong>Client Type:</strong> {{ $project->client_type }}
                    </td>
                    <td class="border border-gray-300 px-4 py-2">
                        <strong>Category:</strong> {{ optional($project->projectCategory)->name ?? '-' }}<br>
                        <strong>Sub Category:</strong> {{ optional($project->projectSubCategory)->name ?? '-' }}<br>
                        <strong>Country:</strong> {{ optional($project->country)->name ?? '-' }}
                    </td>
                    @php
                        $statusColors = [
                            'complete'   => 'text-green-600 font-bold',
                            'working'    => 'text-green-600 font-bold',
                            'hold'       => 'text-yellow-600 font-bold',
                            'paused'     => 'text-purple-600 font-bold',
                            'issues'     => 'text-red-600 font-bold',
                            'temp hold'  => 'text-orange-600 font-bold',
                            'closed'     => 'text-gray-600 font-bold',
                        ];
                        $statusRaw = $project->project_status ?? 'working';
                        $status = strtolower(trim($statusRaw));
                        $statusClass = $statusColors[$status] ?? 'text-gray-500 font-bold';
                    @endphp
                    <td class="border border-gray-300 px-4 py-2">
                        <span class="{{ $statusClass }}">
                            {{ ucfirst($statusRaw) }}
                        </span>
                    </td>
                    <td class="border border-gray-300 px-4 py-2">
                        <strong>Name:</strong> {{ $project->client_name }}<br>
                        <strong>Email:</strong> {{ $project->client_email }}<br>
                        <strong>Other Info:</strong>
                        <span class="tooltip">
                            {{ Str::limit($project->client_other_info, 20) }}
                            <span class="tooltiptext">{{ $project->client_other_info }}</span>
                        </span>
                    </td>
                    
                  
                    <td class="border border-gray-300 px-4 py-2">
                        <span class="tooltip">
                            {{ Str::limit($project->description, 100) }}
                            <span class="tooltiptext">{{ $project->description }}</span>
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $projects->links() }}
    </div>
</div>
@endsection
