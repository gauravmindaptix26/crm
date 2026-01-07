@extends('layouts.dashboard')

@section('content')

<div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-semibold text-gray-800 mb-6">Closed Projects</h1>

    <!-- Controls row -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <!-- Entries per page -->
        <div class="flex items-center gap-2">
            <label for="entriesPerPage" class="text-sm text-gray-600">Show</label>
            <select id="entriesPerPage" name="per_page" onchange="this.form.submit()" form="filterForm" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
            </select>
            <span class="text-sm text-gray-600">entries</span>
        </div>

        <!-- Search -->
        <div class="w-full md:w-72">
            <input type="text" id="searchInput" name="search" placeholder="Search closed projects..." value="{{ request('search') }}"
                form="filterForm" class="w-full border border-gray-300 rounded-md px-4 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm" />
        </div>
    </div>

    <!-- 🔍 Filter Form -->
    <form id="filterForm" method="GET" action="{{ route('projects.closed') }}" class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4 bg-white p-4 rounded shadow">
        <!-- Year -->
        <div>
            <label for="report_year" class="block text-sm font-semibold">Year</label>
            <select name="report_year" id="report_year" class="w-full px-3 py-2 border rounded">
                <option value="">Select Year</option>
                @for ($year = date('Y'); $year >= date('Y') - 5; $year--)
                    <option value="{{ $year }}" {{ request('report_year', date('Y')) == $year ? 'selected' : '' }}>{{ $year }}</option>
                @endfor
            </select>
        </div>

        <!-- Month -->
        <div>
            <label for="project_month" class="block text-sm font-semibold">Month</label>
            <select name="project_month" id="project_month" class="w-full px-3 py-2 border rounded">
                <option value="ALL" {{ request('project_month') == 'ALL' ? 'selected' : '' }}>All</option>
                <option value="Jan" {{ request('project_month') == 'Jan' ? 'selected' : '' }}>January</option>
                <option value="Feb" {{ request('project_month') == 'Feb' ? 'selected' : '' }}>February</option>
                <option value="Mar" {{ request('project_month') == 'Mar' ? 'selected' : '' }}>March</option>
                <option value="Apr" {{ request('project_month') == 'Apr' ? 'selected' : '' }}>April</option>
                <option value="May" {{ request('project_month') == 'May' ? 'selected' : '' }}>May</option>
                <option value="Jun" {{ request('project_month') == 'Jun' ? 'selected' : '' }}>June</option>
                <option value="Jul" {{ request('project_month') == 'Jul' ? 'selected' : '' }}>July</option>
                <option value="Aug" {{ request('project_month') == 'Aug' ? 'selected' : '' }}>August</option>
                <option value="Sep" {{ request('project_month') == 'Sep' ? 'selected' : '' }}>September</option>
                <option value="Oct" {{ request('project_month') == 'Oct' ? 'selected' : '' }}>October</option>
                <option value="Nov" {{ request('project_month') == 'Nov' ? 'selected' : '' }}>November</option>
                <option value="Dec" {{ request('project_month') == 'Dec' ? 'selected' : '' }}>December</option>
            </select>
        </div>

        <!-- Project Type -->
        <div>
            <label class="block text-sm font-semibold">Project Type</label>
            <select name="project_type" class="w-full px-3 py-2 border rounded">
                <option value="ALL" {{ request('project_type') == 'ALL' ? 'selected' : '' }}>All</option>
                <option value="Ongoing" {{ request('project_type') == 'Ongoing' ? 'selected' : '' }}>Ongoing</option>
                <option value="One-time" {{ request('project_type') == 'One-time' ? 'selected' : '' }}>One-time</option>
            </select>
        </div>

        <!-- Submit -->
        <div class="flex items-end">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">🔍 Filter</button>
        </div>
    </form>

    <!-- Export Button -->
    <!-- <div class="mb-6">
        <a href="" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">Export to CSV</a>
    </div> -->

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-green-100 border border-green-400 text-green-800 px-6 py-4 rounded shadow">
            <h3 class="text-lg font-bold">✅ Total Closed Projects</h3>
            <p class="text-2xl mt-1 font-semibold">{{ $totalClosed }}</p>
        </div>
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-6 py-4 rounded shadow">
            <h3 class="text-lg font-bold">💵 Total Project Amount</h3>
            <p class="text-2xl mt-1 font-semibold">${{ number_format($totalAmount, 2) }}</p>
        </div>
        <div class="bg-blue-100 border border-blue-400 text-blue-800 px-6 py-4 rounded shadow">
            <h3 class="text-lg font-bold">💰 Total Amount Received</h3>
            <p class="text-2xl mt-1 font-semibold">${{ number_format($totalReceived, 2) }}</p>
        </div>
        <!-- <div class="bg-purple-100 border border-purple-400 text-purple-800 px-6 py-4 rounded shadow">
            <h3 class="text-lg font-bold">⭐ Average Rating</h3>
            <p class="text-2xl mt-1 font-semibold">{{ number_format($avgRating, 2) }}</p>
        </div> -->
    </div>

    <!-- Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto max-h-[600px]">
            <table class="w-full text-sm text-gray-700">
                <thead class="bg-indigo-50 text-gray-700 sticky top-0 z-10">
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">Project Name</th>
                        <th class="px-4 py-3 text-left">Office Details</th>
                        <th class="px-4 py-3 text-left">Client Info</th>
                        <th class="px-4 py-3 text-left">Price</th>
                        <th class="px-4 py-3 text-left">Amount Received</th>
                        <th class="px-4 py-3 text-left">Added On</th>
                        <th class="px-4 py-3 text-left">Closed Date</th>
                        <!-- <th class="px-4 py-3 text-left">Closed By</th> -->
                        <th class="px-4 py-3 text-left">Reason</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $key => $project)
                        <tr class="border-t">
                            <td class="px-4 py-3">{{ $key + $projects->firstItem() }}</td>
                            <td class="px-4 py-3 font-medium text-indigo-600">{{ $project->name_or_url }}</td>
                            <td class="px-4 py-3 leading-6">
                                <div><strong>Business:</strong> {{ $project->business_type ?? '-' }}</div>
                                <div><strong>Grade:</strong> {{ $project->project_grade ?? '-' }}</div>
                                <div><strong>PM:</strong> {{ optional($project->projectManager)->name ?? '-' }}</div>
                                <div><strong>Sales:</strong> {{ optional($project->salesPerson)->name ?? '-' }}</div>
                                <div><strong>Department:</strong> {{ optional($project->department)->name ?? '-' }}</div>
                                <div><strong>Assigned Employee:</strong> {{ optional($project->employee)->name ?? '-' }}</div>

                            </td>
                            <td class="px-4 py-3 leading-6">
                                <div><strong>Name:</strong> {{ $project->client_name ?? '-' }}</div>
                                <div><strong>Email:</strong> {{ $project->client_email ?? '-' }}</div>
                                <div><strong>Info:</strong>
                                    <span class="relative group text-blue-600 underline cursor-help">
                                        {{ Str::limit($project->client_other_info, 20) }}
                                        <span class="absolute z-10 w-64 mt-1 p-2 text-xs text-white bg-gray-900 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity">
                                            {{ $project->client_other_info }}
                                        </span>
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-green-600 font-semibold">
                                ${{ number_format($project->price ?? 0, 2) }}
                            </td>
                            <td class="px-4 py-3 text-blue-700 font-semibold">
                                ${{ number_format($project->payments->sum('payment_amount'), 2) }}
                            </td>
                            <td class="px-4 py-3">{{ $project->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3">{{ optional($project->status_date) ? \Carbon\Carbon::parse($project->status_date)->format('d M Y') : '-' }}</td>
                            <!-- <td class="px-4 py-3">{{ optional($project->closedByUser)->name ?? '-' }}</td> -->
                            <td class="px-4 py-3 text-gray-600">
                                {{ Str::limit($project->reason_description, 100) }}
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('project_payments.index', ['project_id' => $project->id]) }}"
                                    class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium px-3 py-1.5 rounded shadow-sm">
                                    View Payment
                                </a>
                                <div class="mt-2 text-sm font-semibold text-gray-800">
                                    ${{ number_format($project->payments->sum('payment_amount'), 2) }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-6 text-gray-500">No closed projects found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 bg-gray-50 border-t text-center">
            {{ $projects->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    let entriesSelect = document.getElementById("entriesPerPage");
    let searchInput = document.getElementById("searchInput");
    let form = document.getElementById("filterForm");
    let debounceTimeout;

    // Submit form on entries per page change
    entriesSelect.addEventListener("change", () => {
        form.submit();
    });

    // Debounced search on keyup
    searchInput.addEventListener("input", () => {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(() => {
            form.submit();
        }, 500);
    });
});
</script>

@endsection