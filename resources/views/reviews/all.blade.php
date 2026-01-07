@extends('layouts.dashboard')

@section('title', 'Employee Reviews')

@section('content')
<div class="container-fluid py-6 px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow-lg rounded-xl p-6 mb-6 border border-gray-100">
        <div class="flex justify-between items-center mb-6 gap-4">
            <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Employee Reviews</h2>
            <div class="flex items-center space-x-4 justify-end">
                <form method="GET" action="{{ route('admin.reviews.index') }}" id="perPageForm" class="flex items-center">
                    <div class="flex items-center space-x-2">
                        <label for="entriesPerPage" class="text-sm font-medium text-gray-700">Show</label>
                        <select id="entriesPerPage" name="per_page" class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm transition duration-150">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        <span class="text-sm font-medium text-gray-700">entries</span>
                    </div>
                    <input type="hidden" name="department_id" value="{{ request('department_id') }}">
                    <input type="hidden" name="project_manager_id" value="{{ request('project_manager_id') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="month" value="{{ request('month', now()->month) }}">
                </form>
                <div class="relative flex items-center">
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0a7 7 0 111.415-1.414L21 21z"></path>
                    </svg>
                    <input type="text" id="searchInput" placeholder="Search reviews..." value="{{ request('search') }}" class="pl-10 pr-4 py-2 w-64 sm:w-72 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 shadow-sm transition duration-150">
                </div>
            </div>
        </div>

        <!-- Department, Project Manager, and Month Filter -->
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="mb-6" id="filterForm">
            <div class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                    <select id="department_id" name="department_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm transition duration-150" onchange="this.form.submit()">
                        <option value="">-- All Departments --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label for="project_manager_id" class="block text-sm font-medium text-gray-700 mb-1">Project Manager</label>
                    <select id="project_manager_id" name="project_manager_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm transition duration-150" onchange="this.form.submit()">
                        <option value="">-- Select Project Manager --</option>
                        @foreach($projectManagers as $pm)
                            <option value="{{ $pm->id }}" {{ request('project_manager_id') == $pm->id ? 'selected' : '' }}>
                                {{ $pm->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                    <select id="month" name="month" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm transition duration-150" onchange="this.form.submit()">
                        <option value="">-- All Months --</option>
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ request('month', now()->month) == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
            </div>
        </form>

        @section('table')
        <!-- Reviews Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden" id="reviewsTable">
            <div class="table-responsive">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Employee</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Department</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Reviewed By</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold">Communication</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold">Teamwork</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold">Quality of Work</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold">Leadership</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold">Overall</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Comments</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reviews as $review)
                            @php
                                $ratings = [
                                    $review->communication,
                                    $review->team_collaboration,
                                    $review->quality_of_work,
                                    $review->ownership,
                                ];
                                $validRatings = array_filter($ratings);
                                $overall = count($validRatings) > 0 ? round(array_sum($validRatings) / count($validRatings), 1) : '-';
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="border-t px-4 py-3 text-gray-800">{{ $review->employee->name }}</td>
                                <td class="border-t px-4 py-3 text-gray-600">{{ $review->employee->department->name ?? 'N/A' }}</td>
                                <td class="border-t px-4 py-3 text-gray-600">{{ $review->reviewer->name ?? 'N/A' }}</td>
                                <td class="border-t px-4 py-3 text-center text-gray-800">{{ $review->communication ?? '-' }}</td>
                                <td class="border-t px-4 py-3 text-center text-gray-800">{{ $review->team_collaboration ?? '-' }}</td>
                                <td class="border-t px-4 py-3 text-center text-gray-800">{{ $review->quality_of_work ?? '-' }}</td>
                                <td class="border-t px-4 py-3 text-center text-gray-800">{{ $review->ownership ?? '-' }}</td>
                                <td class="border-t px-4 py-3 text-center font-semibold text-blue-600">{{ $overall }}</td>
                                <td class="border-t px-4 py-3 text-gray-600">{{ Str::limit($review->comments ?? '-', 50) }}</td>
                                <td class="border-t px-4 py-3 text-gray-600">{{ $review->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="border-t px-4 py-3 text-center text-gray-500">No reviews found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6" id="pagination">
            {{ $reviews->links() }}
        </div>
        @show
    </div>
</div>

<style>
    .table-responsive {
        -webkit-overflow-scrolling: touch;
    }
    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    th, td {
        border-bottom: 1px solid #e5e7eb;
    }
    th {
        background-color: #f9fafb;
        font-weight: 600;
        color: #374151;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    tbody tr {
        transition: background-color 0.2s ease;
    }
    tbody tr:hover {
        background-color: #f9fafb;
    }
    tbody tr:last-child td {
        border-bottom: none;
    }
    .container-fluid {
        max-width: 1400px;
        margin-left: auto;
        margin-right: auto;
    }
</style>

<script>
    document.getElementById('entriesPerPage')?.addEventListener('change', function() {
        document.getElementById('perPageForm').submit();
    });

    document.getElementById('searchInput')?.addEventListener('input', function() {
        clearTimeout(this.timeout);
        this.timeout = setTimeout(() => {
            const search = this.value;
            const departmentId = '{{ request('department_id') }}';
            const projectManagerId = '{{ request('project_manager_id') }}';
            const perPage = '{{ request('per_page', 10) }}';
            const month = '{{ request('month', now()->month) }}';

            fetch('{{ route('admin.reviews.index') }}?' + new URLSearchParams({
                search: search,
                department_id: departmentId,
                project_manager_id: projectManagerId,
                per_page: perPage,
                month: month,
                _token: '{{ csrf_token() }}'
            }), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('reviewsTable').innerHTML = data.html;
                document.getElementById('pagination').innerHTML = data.pagination;
            })
            .catch(error => console.error('Error:', error));
        }, 300); // Debounce to prevent excessive requests
    });
</script>
@endsection