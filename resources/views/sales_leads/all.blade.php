@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">

    <h2 class="text-2xl font-semibold mb-4">All Sales Leads</h2>

    {{-- Filter Form --}}
    <form method="GET" action="{{ route('all.sales.leads') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6">
        <select name="sales_person_id" class="form-select">
            <option value="">-- Sales Person --</option>
            @foreach($salesPersons as $person)
                <option value="{{ $person->id }}" {{ request('sales_person_id') == $person->id ? 'selected' : '' }}>
                    {{ $person->name }}
                </option>
            @endforeach
        </select>

        <select name="client_type" class="form-select">
            <option value="">-- Client Type --</option>
            <option value="New" {{ request('client_type') == 'New' ? 'selected' : '' }}>New</option>
            <option value="Existing" {{ request('client_type') == 'Existing' ? 'selected' : '' }}>Existing</option>
        </select>

        <select name="status" class="form-select">
            <option value="">-- Status --</option>
            <option value="Hired" {{ request('status', 'Hired') == 'Hired' ? 'selected' : '' }}>Hired</option>
            <option value="Bid" {{ request('status') == 'Bid' ? 'selected' : '' }}>Bid</option>
            <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
        </select>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Filter
        </button>

        <a href="{{ route('all.sales.leads') }}" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400 text-center">
            Clear
        </a>
    </form>

    {{-- Status Counts --}}
    <div class="flex space-x-4 mb-4">
        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full">Hired: {{ $hiredCount }}</span>
        <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full">Bid: {{ $bidCount }}</span>
    </div>

    {{-- Sales Leads Table --}}
    <div class="overflow-x-auto bg-white rounded shadow">
        <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-sm">
            <thead class="bg-gradient-to-r from-gray-100 to-gray-200 text-left text-gray-800">
                <tr>
                    <th class="py-3 px-6 border-b text-base font-bold">Sr. No.</th>
                    <th class="py-3 px-6 border-b text-base font-bold">Client Details</th>
                    <th class="py-3 px-6 border-b text-base font-bold">Status</th>
                    <th class="py-3 px-6 border-b text-base font-bold">Job Details</th>
                    <th class="py-3 px-6 border-b text-base font-bold">Lead Info</th>
                    <th class="py-3 px-6 border-b text-base font-bold">Description</th>
                </tr>
            </thead>
            <tbody class="text-gray-900 font-medium">
                @foreach($salesLeads as $lead)
                    <tr class="hover:bg-gray-50 transition-all border-b border-gray-200">
                        <td class="py-4 px-6 text-center">{{ $loop->iteration }}</td>

                        <td class="py-4 px-6 leading-relaxed text-sm">
                            <div><strong>Name:</strong> {{ $lead->client_name }}</div>
                            <div><strong>Email:</strong> {{ $lead->client_email }}</div>
                            <div><strong>Phone:</strong> {{ $lead->client_phone }}</div>
                            <div><strong>Client Type:</strong> {{ $lead->client_type }}</div>
                        </td>

                        <td class="py-4 px-6 text-center">
                            <span class="inline-block px-3 py-1 text-xs font-bold rounded-full tracking-wide
                                @if($lead->status === 'Hired')
                                    bg-green-600 text-white
                                @elseif($lead->status === 'Bid')
                                    bg-blue-600 text-white
                                @elseif($lead->status === 'Rejected')
                                    bg-red-600 text-white
                                @else
                                    bg-yellow-500 text-white
                                @endif
                            ">
                                {{ $lead->status ?? 'Progress' }}
                            </span>
                            <br>
                            <button onclick="openUpdateStatusModal({{ $lead->id }})"
                            class="mt-2 text-sm text-indigo-600 font-semibold hover:underline transition-all duration-200">
                            Update Status
                        </button>
                        <a target="_blank" href="{{ url('sales-lead/' . $lead->id) }}"
                               class="block bg-gray-100 hover:bg-blue-100 text-gray-700 px-3 py-2 rounded transition">
                               View
                            </a>
                        </td>

                        <td class="py-4 px-6 leading-relaxed text-sm">
                            <div><strong>Title:</strong> {{ $lead->job_title }}</div>
                            <div><strong>URL:</strong>
                                <a href="{{ $lead->job_url }}" target="_blank" class="text-blue-600 underline">View</a>
                            </div>
                            <div><strong>Department:</strong> {{ $lead->department->name ?? '-' }}</div>
                            <div><strong>Created:</strong> {{ $lead->created_at->format('d M, Y') }}</div>
                        </td>

                        <td class="py-4 px-6 leading-relaxed text-sm">
                            <div><strong>Sales Person:</strong> {{ $lead->salesPerson->name ?? '-' }}</div>
                            <div><strong>Country:</strong> {{ $lead->country->name ?? '-' }}</div>
                            <div><strong>Lead From:</strong> {{ $lead->leadFrom->name ?? '-' }}</div>
                        </td>

                        <td class="py-4 px-6 text-sm text-gray-700">
                            {{ \Str::limit($lead->description, 120) }}
                        </td>

                        <!-- <td class="py-4 px-6 text-center space-y-2 text-sm">
                            <button onclick="editLead({{ $lead->id }})"
                                    class="text-blue-600 hover:underline font-semibold">
                                Edit
                            </button>
                            <button type="button"
    class="text-red-600 hover:underline font-semibold delete-lead-btn"
    data-id="{{ $lead->id }}"
    onclick="deleteSalesLead({{ $lead->id }})">
    Delete
</button>
                        </td> -->
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $salesLeads->appends(request()->query())->links() }}
    </div>
</div>
<!-- Modal for updating status -->
<div id="updateStatusModal" class="fixed inset-0 z-50 hidden bg-gray-800 bg-opacity-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg p-6 w-1/2">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Update Lead Status</h2>

            <!-- Update Status Form -->
            <form id="updateStatusForm" action="{{ route('sales-leads.updateStatus') }}" method="POST">
                @csrf
                <input type="hidden" name="lead_id" id="lead_id">

                <!-- Lead Status Dropdown -->
                <div class="mb-4">
    <label for="status" class="block text-gray-700">Lead Status</label>
    <select name="status" id="status" class="w-full border rounded p-2 bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        <option value="" disabled selected>Select Lead Status</option>  <!-- Default empty option -->
        <option value="Bid">Bid</option>
        <option value="Progress">Progress</option>
        <option value="Hired">Hired</option>
    </select>
</div>


                <!-- Date Picker -->
                <div class="mb-4">
                    <label for="date" class="block text-gray-700">Select Date</label>
                    <input type="date" name="date" id="date" class="w-full border rounded p-2">
                </div>

                <!-- Reason Textarea -->
                <div class="mb-4">
                    <label for="reason" class="block text-gray-700">Reason</label>
                    <textarea name="reason" id="reason" rows="4" class="w-full border rounded p-2"></textarea>
                </div>

                <!-- Actions -->
                <div class="flex justify-between">
                    <button type="button" class="bg-gray-500 text-white rounded px-4 py-2" onclick="closeUpdateStatusModal()">Cancel</button>
                    <button type="submit" class="bg-blue-500 text-white rounded px-4 py-2">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
     // Open modal with lead ID
 function openUpdateStatusModal(leadId) {
        document.getElementById('lead_id').value = leadId;
        document.getElementById('updateStatusModal').classList.remove('hidden');
    }

    function closeModal() {
    document.getElementById('leadModal').classList.add('hidden');
    document.getElementById('leadForm').reset();
    editMode = false;
    editId = null;
}
function closeUpdateStatusModal() {
        document.getElementById('updateStatusModal').classList.add('hidden');
    }
    document.getElementById('updateStatusForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message in modal
            const modal = document.getElementById('updateStatusModal');
            const successMessage = document.createElement('div');
            successMessage.className = "text-green-600 text-center my-4 font-semibold";
            successMessage.textContent = data.message;
            modal.querySelector('form').prepend(successMessage);

            // Wait 2 seconds, then redirect to sales lead listing
            setTimeout(() => {
                window.location.href = "{{ route('sales-leads.index') }}";
            }, 2000);
        } else {
            alert("Something went wrong.");
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
});
function deleteSalesLead(id) {
        if (!confirm('Are you sure you want to delete this lead?')) {
            return;
        }

        fetch(`sales-leads/${id}`, { 
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message); // You can replace this with a nicer toast
                location.reload();   // Automatically refreshes the page
            } else {
                alert('Failed to delete the lead.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Something went wrong.');
        });
    }
</script>
@endsection
