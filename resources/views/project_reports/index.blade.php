@extends('layouts.dashboard')

@section('content')
<div class="p-4">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">Project Monthly Reports</h2>
        <button id="addNewBtn" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
            + Add Report
        </button>
    </div>
    <div class="mb-4">
        @if (auth()->user()->hasRole('Employee'))
            <div class="flex gap-3 mb-4">
                <a href="{{ route('my.assigned.projects') }}"
                   class="bg-gray-500 text-white px-4 py-2.5 rounded-lg hover:bg-gray-600 transition duration-200 font-medium text-sm">
                    ← Back to  Assigned Projects
                </a>
            </div>
        @else
            <div class="flex gap-3 mb-4">
                <a href="{{ route('projects.index') }}"
                   class="bg-gray-500 text-white px-4 py-2.5 rounded-lg hover:bg-gray-600 transition duration-200 font-medium text-sm">
                    ← Back to Projects
                </a>
            </div>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full border text-sm text-left text-gray-700">
            <thead class="bg-gray-100 text-xs uppercase text-gray-700">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Report for Month</th>
                    <th class="px-4 py-2">Details</th>
                    <th class="px-4 py-2">Added By</th>
                    <th class="px-4 py-2">Added On</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $index => $report)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $index + 1 }}</td>
                        <td class="px-4 py-2">{{ \Carbon\Carbon::parse($report->report_for_month)->format('d M Y') }}</td>
                        <td class="px-4 py-2">{{ $report->details }}</td>
                        <td class="px-4 py-2">{{ $report->addedBy->name ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $report->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-2 flex space-x-2">
                            <!-- <button class="editBtn bg-yellow-500 text-white px-3 py-1 rounded" 
                                data-id="{{ $report->id }}">Edit</button> -->
                            <button class="deleteBtn bg-red-600 text-white px-3 py-1 rounded" 
                                data-id="{{ $report->id }}">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center px-4 py-4 text-gray-500">No reports found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $reports->withQueryString()->links() }}
    </div>
</div>

<!-- Modal -->
<div id="reportModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white w-full max-w-xl rounded-lg p-6 relative">
        <h3 class="text-lg font-semibold mb-4" id="modalTitle">Add Monthly Report</h3>
        <form id="reportForm">
            @csrf
            <input type="hidden" name="id" id="reportId">
            <input type="hidden" name="project_id" value="{{ $projectId }}">

            <div class="mb-4">
                <label for="report_for_month" class="block mb-1 font-medium">Report for Month</label>
                <input type="date" name="report_for_month" id="report_for_month"
                    class="w-full border rounded px-3 py-2">
                <span class="text-red-500 text-sm" id="error_report_for_month"></span>
            </div>

            <div class="mb-4">
                <label for="details" class="block mb-1 font-medium">Details</label>
                <textarea name="details" id="details" rows="4"
                    class="w-full border rounded px-3 py-2"></textarea>
                <span class="text-red-500 text-sm" id="error_details"></span>
            </div>

            <div class="flex justify-end space-x-2">
                <button type="button" id="closeModal"
                    class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save</button>
            </div>
        </form>
    </div>
</div>
<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 max-w-sm w-full text-center">
        <h2 class="text-green-600 text-xl font-semibold mb-2">Success!</h2>
        <p class="text-gray-700 mb-4">Monthly report has been saved successfully.</p>
        <button id="successOkBtn" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            OK
        </button>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('reportModal');
    const addBtn = document.getElementById('addNewBtn');
    const closeModal = document.getElementById('closeModal');
    const form = document.getElementById('reportForm');
    const modalTitle = document.getElementById('modalTitle');
    const reportId = document.getElementById('reportId');

    function showModal() {
        modal.classList.remove('hidden');
    }

    function hideModal() {
        modal.classList.add('hidden');
        form.reset();
        reportId.value = '';
        clearErrors();
        modalTitle.innerText = 'Add Monthly Report';
    }

    function clearErrors() {
        document.querySelectorAll('[id^=error_]').forEach(el => el.innerText = '');
    }

    addBtn.addEventListener('click', showModal);
    closeModal.addEventListener('click', hideModal);

    form.addEventListener('submit', function (e) {
    e.preventDefault();
    clearErrors();

    const formData = new FormData(form);
    const id = formData.get('id');
    const method = id ? 'PUT' : 'POST';
    const url = id 
        ? `project_monthly_reports/${id}`
        : `project_monthly_reports`;

    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': formData.get('_token'),
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.errors) {
            Object.keys(data.errors).forEach(field => {
                document.getElementById(`error_${field}`).innerText = data.errors[field][0];
            });
        } else {
            hideModal();
            document.getElementById('successModal').classList.remove('hidden');
        }
    })
    .catch(err => console.error(err));
});
document.getElementById('successOkBtn').addEventListener('click', function () {
    document.getElementById('successModal').classList.add('hidden');
    location.reload(); // Reload after user confirms
});
    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            fetch(`project_monthly_reports/${id}/edit`)
                .then(res => res.json())
                .then(data => {
                    modalTitle.innerText = 'Edit Monthly Report';
                    reportId.value = data.id;
                    document.getElementById('report_for_month').value = data.report_for_month;
                    document.getElementById('details').value = data.details;
                    showModal();
                });
        });
    });

    document.querySelectorAll('.deleteBtn').forEach(btn => {
        btn.addEventListener('click', function () {
            if (!confirm('Are you sure you want to delete this report?')) return;
            const id = this.dataset.id;

            fetch(`project_monthly_reports/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(res => res.json())
            .then(data => {
                location.reload();
            });
        });
    });
});
</script>
@endsection
