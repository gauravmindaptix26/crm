@extends('layouts.dashboard')

@section('content')
<div class="p-4 bg-white shadow rounded-lg">
    <h2 class="text-xl font-bold mb-4">Projects Audit</h2>

    <!-- 🔹 Filter Form -->
    <form method="GET" action="{{ route('projects.audit') }}" class="flex flex-wrap gap-4 mb-4">
        <!-- Hired From Filter -->
        <div>
            <!-- <label class="block text-sm font-semibold mb-1">Hired From</label>
            <select name="hired_from_id" class="border border-gray-300 rounded-lg px-3 py-2">
                <option value="">-- Select --</option>
                @foreach($hiredFroms as $hf)
                    <option value="{{ $hf->id }}" {{ request('hired_from_id') == $hf->id ? 'selected' : '' }}>
                        {{ $hf->name }}
                    </option>
                @endforeach
            </select> -->
        </div>
  <!-- Project Manager Filter (Admin Only) -->
  @if(auth()->user()->hasRole('Admin'))
        <div>
            <label class="block text-sm font-semibold mb-1">Filter By Project Manager</label>
            <select name="project_manager_id" class="border border-gray-300 rounded-lg px-3 py-2">
                <option value="">-- All Project Managers --</option>
                @foreach($projectManagers as $pm)
                    <option value="{{ $pm->id }}" {{ request('project_manager_id') == $pm->id ? 'selected' : '' }}>
                        {{ $pm->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif
        <!-- Duration Filter -->
        <div>
            <label class="block text-sm font-semibold mb-1">Project Duration</label>
            <select name="duration" class="border border-gray-300 rounded-lg px-3 py-2">
                <option value="">-- Select --</option>
                @foreach([2,4,6,8,10] as $month)
                <option value="{{ $month }}" {{ $duration == $month ? 'selected' : '' }}>
    {{ $month }} Months
</option>
                @endforeach
            </select>
        </div>

      

        <div class="flex items-end">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700">
                Filter
            </button>
        </div>
    </form>

    <hr class="my-4">

    <!-- 🔹 Projects Listing -->
    <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-300 text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-3 py-2">#</th>
                    <th class="border px-3 py-2">Name/URL</th>
                    <th class="border px-3 py-2">Office Details</th>
                    <th class="border px-3 py-2">Price/Hours</th>
                    <th class="border px-3 py-2">Added On</th>
                    <th class="border px-3 py-2">Type</th>
                    <th class="border px-3 py-2">Status</th>

                    <th class="border px-3 py-2">Client Details</th>
                    <th class="border px-3 py-2">Description</th>
                    <th class="border px-3 py-2 text-center">Action</th> <!-- New Column -->

                </tr>
            </thead>
            <tbody>
                @forelse($paginatedProjects as $key => $project)
                @php
                    $receivedAmount = $project->projectPayments->sum('payment_amount');

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
                <tr>
                    <td class="border px-3 py-2">SEODIS-{{ $paginatedProjects->firstItem() + $key }}</td>
                    <td class="border px-3 py-2">
                        <strong>{{ $project->name_or_url }}</strong><br>
                        @if($project->dashboard_url)
                        <a href="{{ $project->dashboard_url }}" target="_blank" class="text-blue-500 underline"></a>
                        @endif
                    </td>
                    <td class="border px-3 py-2">
                        <div><strong>Grade:</strong> {{ $project->project_grade ?? 'NA' }}</div>
                        <div><strong>PM:</strong> {{ optional($project->projectManager)->name ?? 'NA' }}</div>
                        <div><strong>Sales:</strong> {{ optional($project->salesPerson)->name ?? 'NA' }}</div>
                        <div><strong>Employee:</strong> {{ optional($project->assignMainEmployee)->name ?? 'NA' }}</div>
                        <div><strong>Department:</strong> {{ optional($project->department)->name ?? 'NA' }}</div>
                    </td>
                    <td class="border px-3 py-2">
                        <div class="mb-2"><strong>Price:</strong> ${{ number_format($project->display_price ?? 0, 2) }}</div>
                        <div class="mb-2"><strong>Hours:</strong> {{ $project->display_hours ?? '0' }}</div>
                        <div class="mb-2">
                            <a target="_blank" href="{{ route('project_payments.index', ['project_id' => $project->id]) }}">
                                <span class="px-3 py-1 text-white rounded-lg shadow-md
                                    bg-gradient-to-r from-blue-400 via-blue-500 to-blue-600
                                    hover:from-blue-500 hover:via-blue-600 hover:to-blue-700
                                    inline-flex items-center gap-2">
                                    Received Price: {{ $receivedAmount }}
                                </span>
                            </a>
                        </div>
                        <div>
                            @php
                                $duration = $project->created_at ? $project->created_at->diff(now()) : null;
                            @endphp

                            @if($duration)
                                <span class="px-3 py-1 text-white rounded-lg shadow-md
                                    bg-gradient-to-r from-blue-400 via-blue-500 to-blue-600
                                    hover:from-blue-500 hover:via-blue-600 hover:to-blue-700
                                    inline-flex items-center gap-2">
                                    Project Duration:
                                    @php
                                        $parts = [];
                                        if ($duration->y > 0) $parts[] = $duration->y . ' year(s)';
                                        if ($duration->m > 0) $parts[] = $duration->m . ' month(s)';
                                        $parts[] = $duration->d . ' day(s)'; // ✅ shows 0 if today
                                    @endphp
                                    {{ implode(' ', $parts) }}
                                </span>
                            @else
                                <span class="text-gray-400">Project Duration: N/A</span>
                            @endif
                        </div>
                    </td>
                    <td class="border px-3 py-2">{{ $project->created_at->format('d-M-Y') }}</td>
                    <td class="border px-3 py-2">{{ $project->project_type ?? '-' }}</td>
                    <td class="border px-3 py-2">
                        <span class="{{ $statusClass }}">{{ ucfirst($statusRaw) }}</span>
                    </td>
                    <td class="border px-3 py-2">
                <div><strong>Client Name:</strong> {{ $project->client_name ?? 'NA' }}</div>
               <div><strong>Client Email:</strong> {{ $project->client_email ?? 'NA' }}</div>
              <div><strong>Other Info:</strong> {{ $project->client_other_info ?? 'NA' }}</div>
</td>

                 
                    <td class="border px-3 py-2">{{ Str::limit($project->display_description, 50) }}</td>
                    <td class="border px-3 py-2 text-center">
                            <!-- View Button -->
                            <a href="{{ route('projects.show', $project->id) }}"
                               class="inline-block px-4 py-2 bg-indigo-600 text-white text-xs font-medium rounded-lg shadow hover:bg-indigo-700 transition">
                                View
                            </a>
                        </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-4 text-gray-500">No projects found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $paginatedProjects->links() }}
    </div>
</div>
@endsection
