@extends('layouts.dashboard')
@section('title', 'Pending Invoices')

@section('content')
@role('Admin')

<form method="GET" action="{{ route('projects.pending.invoices') }}" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-4">
    <!-- Project Manager -->
    <div>
        <label for="project_manager_id" class="block font-medium text-sm text-gray-700">Project Manager</label>
        <select name="project_manager_id" id="project_manager_id" class="w-full border-gray-300 rounded-md shadow-sm">
            <option value="">-- All --</option>
            @foreach($projectManagers as $manager)
                <option value="{{ $manager->id }}" {{ request('project_manager_id') == $manager->id ? 'selected' : '' }}>
                    {{ $manager->name }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- Department -->
    <div>
        <label for="department_id" class="block font-medium text-sm text-gray-700">Department</label>
        <select name="department_id" id="department_id" class="w-full border-gray-300 rounded-md shadow-sm">
            <option value="">-- All --</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                    {{ $dept->name }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- Month -->
    <div>
        <label for="project_month" class="block font-medium text-sm text-gray-700">Project Month</label>
        <input type="month" name="project_month" id="project_month" value="{{ request('project_month') }}" class="w-full border-gray-300 rounded-md shadow-sm">
    </div>

    <!-- Submit -->
    <div class="flex items-end">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">🔍 Filter</button>
    </div>
</form>
@endrole

{{-- Pending Amount Box --}}
<div class="flex justify-start mb-6">
    <div class="w-full sm:w-2/3 md:w-1/2 lg:w-1/3">
        <div class="bg-gradient-to-r from-cyan-100 to-teal-100 border border-teal-300 text-gray-800 px-6 py-5 rounded-2xl shadow-lg">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <span class="text-teal-700 text-xl">💰</span> Total Pending Amount
            </h3>
            <p class="text-3xl mt-2 font-extrabold text-teal-900 tracking-wide">
                ${{ number_format($totalPendingAmount, 2) }}
            </p>
        </div>
    </div>
</div>


<div class="p-6">
    <h2 class="text-2xl font-bold mb-4 text-gray-800">🧾 Pending Invoices</h2>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto max-h-[500px]">
            <table class="w-full border-collapse border border-gray-300 text-sm">
                <thead>
                    <tr>
                        <th class="border px-4 py-2">#</th>
                        <th class="border px-4 py-2">Name/URL</th>
                        <th class="border px-4 py-2">Office Details</th>
                        <th class="border px-4 py-2">Price</th>
                        <th class="border px-4 py-2">Added On</th>
                        <th class="border px-4 py-2">Client</th>
                        <th class="border px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($projects as $key => $project)
                        <tr>
                            <td class="border px-4 py-2">{{ $projects->firstItem() + $key }}</td>
                            <td class="border px-4 py-2">
                                <strong>{{ $project->name_or_url }}</strong><br>
                                <a href="{{ $project->dashboard_url }}" target="_blank" class="text-blue-500">Dashboard Link</a>
                            </td>
                            <td class="border px-4 py-2">
                                <strong>PM:</strong> {{ optional($project->projectManager)->name ?? '-' }}<br>
                                <strong>TL:</strong> {{ optional($project->teamLead)->name ?? '-' }}<br>
                                <strong>Sales:</strong> {{ optional($project->salesPerson)->name ?? '-' }}<br>
                                <strong>Dept:</strong> {{ optional($project->department)->name ?? '-' }}<br>
                                <strong>Main Emp:</strong> {{ optional($project->assignMainEmployee)->name ?? '-' }}<br>
                                @if($project->upsell_employee_id)
                                    <strong>Upsell:</strong> {{ optional($project->upsellEmployee)->name ?? '-' }}<br>
                                @endif
                            </td>
                            <td class="border px-4 py-2">
                                <strong>Price:</strong> {{ $project->price ?? '0' }}<br>
                            </td>
                            <td class="border px-4 py-2">{{ $project->created_at->format('d-M-Y') }}</td>
                           
                          
                            <td class="border px-4 py-2">
                                <strong>{{ $project->client_name }}</strong><br>
                                <strong>Email:</strong> {{ $project->client_email }}<br>
                                <strong>Info:</strong>
                                <span title="{{ $project->client_other_info }}">
                                    {{ Str::limit($project->client_other_info, 20) }}
                                </span>
                            </td>
                            <td class="border px-4 py-2 space-y-1">
                               
                                <a href="{{ route('project_payments.index', ['project_id' => $project->id]) }}" class="bg-indigo-600 text-white px-3 py-1 rounded">Update Payment</a>
                                <a href="{{ route('projects.show', $project->id) }}" class="bg-blue-600 text-white px-3 py-1 rounded">View</a>
                                
                            </td>
                          
                           
                        </tr>
                    @empty
                        <tr><td colspan="12" class="text-center py-6 text-gray-500">No pending invoices found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

       <!-- Pagination -->
<div class="mt-4">
    {{ $projects->appends(request()->all())->links() }}
</div>
    </div>
</div>
@endsection
