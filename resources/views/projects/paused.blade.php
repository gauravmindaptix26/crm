@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">Paused Projects</h1>

    <!-- Controls row -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-4">
        <!-- Entries per page -->
        <div class="flex items-center space-x-2">
            <label for="entriesPerPage" class="text-sm font-medium text-gray-600">Show</label>
            <select id="entriesPerPage" name="entries" onchange="this.form.submit()"
                    class="border border-gray-300 rounded-md px-3 py-1.5 focus:ring focus:border-blue-500 text-sm">
                <option value="10" {{ request('entries') == 10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ request('entries') == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('entries') == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('entries') == 100 ? 'selected' : '' }}>100</option>
            </select>
            <span class="text-sm font-medium text-gray-600">entries</span>
        </div>

        <!-- Search box -->
        <div>
            <input type="text" id="searchInput" name="search" value="{{ request('search') }}"
                   placeholder="Search Paused projects..."
                   class="border border-gray-300 rounded-md px-4 py-2 w-full md:w-72 focus:ring focus:border-indigo-500 shadow-md text-gray-700 transition" />
        </div>
    </div>

    <form method="GET" id="filterForm" class="flex flex-wrap items-center gap-4 mb-4">
        <input type="hidden" name="entries" value="{{ request('entries', 20) }}">
        <input type="hidden" name="search" id="hiddenSearch" value="{{ request('search') }}">

        <div>
            <label for="project_month" class="block text-sm font-medium text-gray-700">Project Month</label>
            <input type="month" name="project_month" id="project_month" value="{{ request('project_month') }}"
                   class="border border-gray-300 rounded-md px-3 py-1.5">
        </div>

        <div>
            <label for="project_type" class="block text-sm font-medium text-gray-700">Project Type</label>
            <select name="project_type" id="project_type"
                    class="border border-gray-300 rounded-md px-3 py-1.5">
                <option value="">-- All Types --</option>
                <option value="Ongoing" {{ request('project_type') == 'Ongoing' ? 'selected' : '' }}>Ongoing</option>
                <option value="One-time" {{ request('project_type') == 'One-time' ? 'selected' : '' }}>One-time</option>
            </select>
        </div>

        <button type="submit"
                class="mt-6 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md shadow">
            Filter
        </button>
    </form>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-100 border-l-4 border-blue-500 p-4 rounded shadow">
            <h3 class="text-lg font-semibold text-blue-700">Total Paused Projects</h3>
            <p class="text-2xl font-bold">{{ $totalPaused }}</p>
        </div>
        <div class="bg-green-100 border-l-4 border-green-500 p-4 rounded shadow">
            <h3 class="text-lg font-semibold text-green-700">Total Amount</h3>
            <p class="text-2xl font-bold">${{ number_format($totalAmount, 2) }}</p>
        </div>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 rounded shadow">
            <h3 class="text-lg font-semibold text-yellow-700">Total Received</h3>
            <p class="text-2xl font-bold">${{ number_format($totalReceived, 2) }}</p>
        </div>
    </div>

    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="overflow-x-auto max-h-[600px]">
            <table class="w-full border-collapse border border-gray-200 text-sm text-gray-700">
                <thead class="bg-gray-100 sticky top-0 z-10">
                    <tr class="text-left">
                        <th class="border px-4 py-3">#</th>
                        <th class="border px-4 py-3">Project Name</th>
                        <th class="border px-4 py-3">Office Details</th>
                        <th class="border px-4 py-3">Client Info</th>
                        <th class="border px-4 py-3">Price</th>
                        <th class="border px-4 py-3">Amount Received</th>
                        <th class="border px-4 py-3">Added On</th>
                        <th class="border px-4 py-3">Paused Date</th>
                        <th class="border px-4 py-3">Paused By</th>
                        <th class="border px-4 py-3">Reason</th>
                        <th class="border px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $key => $project)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="border px-4 py-3">{{ $key + $projects->firstItem() }}</td>
                            <td class="border px-4 py-3 font-medium text-indigo-700">{{ $project->name_or_url }}</td>
                            <td class="border px-4 py-3 leading-6">
                                <strong>Business:</strong> {{ $project->business_type ?? '-' }}<br>
                                <strong>Grade:</strong> {{ $project->project_grade ?? '-' }}<br>
                                <strong>PM:</strong> {{ optional($project->projectManager)->name ?? '-' }}<br>
                                <strong>Sales:</strong> {{ optional($project->salesPerson)->name ?? '-' }}<br>
                                <strong>Department:</strong> {{ optional($project->department)->name ?? '-' }}
                            </td>
                            <td class="border px-4 py-3 leading-6">
                                <strong>Name:</strong> {{ $project->client_name ?? '-' }}<br>
                                <strong>Email:</strong> {{ $project->client_email ?? '-' }}<br>
                                <strong>Other Info:</strong>
                                <span class="relative group cursor-pointer text-blue-600 underline">
                                    {{ Str::limit($project->client_other_info, 20) }}
                                    <span class="absolute z-10 left-0 mt-1 w-64 p-2 text-xs text-white bg-gray-900 rounded-lg opacity-0 group-hover:opacity-100 transition">
                                        {{ $project->client_other_info }}
                                    </span>
                                </span>
                            </td>
                            <td class="border px-4 py-3 text-green-600 font-semibold">
                                ${{ number_format($project->price ?? 0, 2) }}
                            </td>
                            <td class="border px-4 py-3 text-blue-700 font-semibold">
                                ${{ number_format($project->payments->sum('payment_amount'), 2) }}
                            </td>
                            <td class="border px-4 py-3">{{ $project->created_at->format('d M Y') }}</td>
                            <td class="border px-4 py-3">
                                {{ optional($project->status_date) ? \Carbon\Carbon::parse($project->status_date)->format('d M Y') : '-' }}
                            </td>
                            <td class="border px-4 py-3">
                                {{ $project->closedByUser?->name ?? '-' }}
                            </td>
                            <td class="border px-4 py-3 text-gray-600">
                                {{ $project->reason_description ?? '-' }}
                            </td>
                            <td class="border px-4 py-3">
                                <div class="flex flex-col space-y-2 items-start">
                                    <a href="{{ route('project_payments.index', ['project_id' => $project->id]) }}"
                                       class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium px-3 py-1.5 rounded">
                                        View Payment
                                    </a>
                                    <span class="text-sm font-semibold text-gray-800">
                                        ${{ number_format($project->payments->sum('payment_amount'), 2) }}
                                    </span>

                                </div>
                                <button class="bg-green-600 hover:bg-green-700 text-white text-xs font-medium px-3 py-1.5 rounded open-followup-modal" data-project-id="{{ $project->id }}" data-client-email="{{ $project->client_email }}">Send Follow-Up</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-6 text-gray-500">No paused projects found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-gray-50 border-t text-center">
            {{ $projects->appends(['search' => request('search'), 'entries' => request('entries'), 'project_month' => request('project_month'), 'project_type' => request('project_type')])->links() }}
        </div>
    </div>
</div>
<!-- Follow-Up Modal -->
<div id="followupModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-800 bg-opacity-75 flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md">
        <h2 class="text-xl font-bold mb-4 text-gray-800">Send Follow-Up Email</h2>
        <form id="followupForm" method="POST">
            @csrf
            <input type="hidden" id="project_id" name="project_id">
            <div class="mb-4">
                <label for="client_email" class="block text-sm font-medium text-gray-700">Client Email</label>
                <input type="email" id="client_email" name="client_email" class="border border-gray-300 rounded-md px-3 py-2 w-full" readonly>
                @error('client_email')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-4">
                <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                <input type="text" id="subject" name="subject" class="border border-gray-300 rounded-md px-3 py-2 w-full" required value="Follow-Up on Paused Project: {{ $project->name_or_url ?? '' }}">
                @error('subject')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-4">
                <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                <textarea id="message" name="message" rows="5" class="border border-gray-300 rounded-md px-3 py-2 w-full" required placeholder="Enter your message to the client..."></textarea>
                @error('message')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" id="closeModal" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Cancel</button>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">Send</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    let searchInput = document.getElementById("searchInput");
    let hiddenSearch = document.getElementById("hiddenSearch");
    let filterForm = document.getElementById("filterForm");
    let entriesSelect = document.getElementById("entriesPerPage");

    // Update hidden search input and submit form on search
    searchInput.addEventListener("keyup", function () {
        hiddenSearch.value = searchInput.value;
        filterForm.submit();
    });

    // Submit form when entries per page changes
    entriesSelect.addEventListener("change", function () {
        hiddenSearch.value = searchInput.value;
        filterForm.submit();
    });
});
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('followupModal');
        const form = document.getElementById('followupForm');
        const closeBtn = document.getElementById('closeModal');

        // Open modal
        document.querySelectorAll('.open-followup-modal').forEach(button => {
            button.addEventListener('click', function () {
                const projectId = this.dataset.projectId;
                const clientEmail = this.dataset.clientEmail;
                document.getElementById('project_id').value = projectId;
                document.getElementById('client_email').value = clientEmail;
                document.getElementById('subject').value = 'Follow-Up on Paused Project';
                modal.classList.remove('hidden');
            });
        });

        // Close modal
        closeBtn.addEventListener('click', function () {
            modal.classList.add('hidden');
            form.reset();
        });

        // Submit form via AJAX
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const projectId = document.getElementById('project_id').value;
            const formData = new FormData(form);

            fetch(`projects/${projectId}/followup/send`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.success);
                    modal.classList.add('hidden');
                    form.reset();
                    location.reload(); // Refresh to update UI
                } else {
                    alert(data.error || 'Failed to send follow-up.');
                }
            })
            .catch(error => alert('Error: ' + error.message));
        });
    });
</script>
@endsection