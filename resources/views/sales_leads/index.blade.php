@extends('layouts.dashboard')

@section('content')
<div class="bg-white shadow-md rounded-lg p-6 mb-6 flex justify-between items-center">
    <div class="flex items-center space-x-3">
        <label for="entriesPerPage" class="text-sm font-medium text-gray-600">Show</label>
        <select id="entriesPerPage" class="border border-gray-300 rounded-md px-3 py-1.5 focus:ring focus:border-blue-500 text-sm">
            <option value="10" selected>10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
        <span class="text-sm font-medium text-gray-600">entries</span>
    </div>
    <input type="text" id="searchInput" placeholder="Search Sale Leads..." 
        class="border border-gray-300 rounded-md px-4 py-2 w-72 focus:ring focus:border-blue-500 shadow-md text-gray-700 transition">
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-blue-100 border-l-4 border-blue-500 p-4 rounded-lg shadow-sm">
        <h3 class="text-xl font-bold text-blue-700">Bid Leads</h3>
        <p class="text-3xl font-semibold text-blue-900">{{ $bidCount }}</p>
    </div>
    <div class="bg-green-100 border-l-4 border-green-500 p-4 rounded-lg shadow-sm">
        <h3 class="text-xl font-bold text-green-700">Hired Leads</h3>
        <p class="text-3xl font-semibold text-green-900">{{ $hiredCount }}</p>
    </div>
    <div class="bg-purple-100 border-l-4 border-purple-500 p-4 rounded-lg shadow-sm">
        <h3 class="text-xl font-bold text-purple-700">Good Bids</h3>
        <p class="text-3xl font-semibold text-purple-900">{{ $goodBidCount }}</p>
    </div>
    
</div>

<div class="p-4">
    <h1 class="text-2xl font-semibold mb-4">Sales Leads</h1>

    <div class="flex justify-end mb-4">
        <button onclick="openModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
            Add Sales Lead
        </button>
    </div>

    <form method="GET" class="mb-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="w-full">
        <label class="block text-sm font-medium text-gray-700 mb-1">Sales Person</label>
        <select name="sales_person_id" class="w-full rounded-md border-gray-300 shadow-sm h-[42px]">
            <option value="">All</option>
            @foreach ($salesPersons as $person)
                <option value="{{ $person->id }}" {{ request('sales_person_id') == $person->id ? 'selected' : '' }}>
                    {{ $person->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="w-full">
        <label class="block text-sm font-medium text-gray-700 mb-1">Sales Lead From</label>
        <select name="lead_from_id" class="w-full rounded-md border-gray-300 shadow-sm h-[42px]">
            <option value="">All</option>
            @foreach ($leadFroms as $lead)
                <option value="{{ $lead->id }}" {{ request('lead_from_id') == $lead->id ? 'selected' : '' }}>
                    {{ $lead->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="w-full">
        <label class="block text-sm font-medium text-gray-700 mb-1">Client Type</label>
        <select name="client_type" class="w-full rounded-md border-gray-300 shadow-sm h-[42px]">
            <option value="">All</option>
            <option value="Reseller" {{ request('client_type') == 'Reseller' ? 'selected' : '' }}>Reseller</option>
            <option value="General" {{ request('client_type') == 'General' ? 'selected' : '' }}>General</option>
            <option value="Premium" {{ request('client_type') == 'Premium' ? 'selected' : '' }}>Premium</option>
        </select>
    </div>

    <div class="w-full flex items-end">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md shadow hover:bg-blue-700 transition w-full md:w-auto">
            Search
        </button>
    </div>
</form>



    {{-- Success Message --}}
    <div id="successMessage" class="hidden text-green-600 font-medium mb-4"></div>

    {{-- Table --}}
    <div class="overflow-auto">
        <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-sm">
            <thead class="bg-gradient-to-r from-gray-100 to-gray-200 text-left text-gray-800">
                <tr>
                    <th class="py-3 px-6 border-b text-base font-bold">Sr. No.</th>
                    <th class="py-3 px-6 border-b text-base font-bold">Client Details</th>
                    <th class="py-3 px-6 border-b text-base font-bold">Status</th>
                    <th class="py-3 px-6 border-b text-base font-bold">Job Details</th>
                    <th class="py-3 px-6 border-b text-base font-bold">Description</th>
                    <th class="py-3 px-6 border-b text-base font-bold">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-900 font-medium">
                @foreach($salesLeads as $lead)
                    <tr class="hover:bg-gray-50 transition-all border-b border-gray-200">
                    <td class="py-4 px-6 text-center">{{ $salesLeads->firstItem() + $loop->index }}</td>

                        <td class="py-4 px-6 leading-relaxed text-sm">
                            <div><strong>Name:</strong> {{ $lead->client_name }}</div>
                            <div><strong>Email:</strong> {{ $lead->client_email }}</div>
                            <div><strong>Phone:</strong> {{ $lead->client_phone }}</div>
                            <div><strong>Type:</strong> {{ $lead->client_type }}</div>
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
                        </td>

                        <td class="py-4 px-6 leading-relaxed text-sm">
                            <div><strong>Job Title:</strong> {{ $lead->job_title }}</div>
                            <div><strong>Job URL:</strong>
                                <a href="{{ $lead->job_url }}" target="_blank" class="text-blue-600 underline">View Job</a>
                            </div>
                            <div><strong>Department:</strong> {{ $lead->department->name ?? '-' }}</div>
                            <div><strong>Created:</strong> {{ \Carbon\Carbon::parse($lead->created_at)->format('d-F-Y') }}</div>
                            <div><strong>Sales Person:</strong> {{ $lead->salesPerson->name ?? '-' }}</div>
                            <div><strong>Country:</strong> {{ $lead->country->name ?? '-' }}</div>
                        </td>

                        <td class="py-4 px-6 text-sm text-gray-700 whitespace-pre-line">
    {{ $lead->description }}
</td>

                        <td class="py-4 px-6 text-center space-y-2 text-sm">
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
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-6">
            {{ $salesLeads->links() }}
        </div>
    </div>
</div>



<!-- Modal -->
<div id="leadModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white w-full max-w-3xl rounded-2xl shadow-lg p-6 relative">
        <button onclick="closeModal()" class="absolute top-3 right-4 text-gray-500 text-2xl font-bold hover:text-gray-700">&times;</button>
        <h2 class="text-2xl font-semibold mb-6" id="modalTitle">Add Sales Lead</h2>

        <form id="leadForm" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Client Name -->
                <div>
                    <label class="block text-sm font-medium mb-1">Client Name</label>
                    <input type="text" name="client_name" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium mb-1">Client Email</label>
                    <input type="email" name="client_email" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm font-medium mb-1">Client Phone</label>
                    <input type="text" name="client_phone" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                </div>

                <!-- Client Type -->
                <div>
                    <label class="block text-sm font-medium mb-1">Client Type</label>
                    <select name="client_type" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                        <option value="">Select</option>
                        <option value="Reseller">Reseller</option>
                        <option value="Premium">Premium</option>
                        <option value="General">General</option>
                    </select>
                </div>

                <!-- Job Title -->
                <div>
                    <label class="block text-sm font-medium mb-1">Job Title</label>
                    <input type="text" name="job_title" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>

                <!-- Job URL -->
                <div>
                    <label class="block text-sm font-medium mb-1">Job URL</label>
                    <input type="url" name="job_url" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>

                <!-- Department -->
                <div>
                    <label class="block text-sm font-medium mb-1">Department</label>
                    <select name="department_id" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                        <option value="">Select</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Sales Person -->
                @php
    $loggedInUser = Auth::user();
@endphp

<div>
    <label class="block text-sm font-medium mb-1">Sales Person</label>
    <select name="sales_person_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        <option value="">Select</option>
        @foreach($salesPersons as $person)
            <option value="{{ $person->id }}"
                @if(
                    old('sales_person_id') == $person->id || 
                    (!old('sales_person_id') && $loggedInUser->hasRole('Sales Team') && $loggedInUser->id == $person->id)
                )
                    selected
                @endif
            >
                {{ $person->name }}
            </option>
        @endforeach
    </select>
</div>


                <!-- Country -->
                <div>
                    <label class="block text-sm font-medium mb-1">Location</label>
                    <select name="country_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">Select</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Lead From -->
                <div>
                    <label class="block text-sm font-medium mb-1">Lead From</label>
                    <select name="lead_from_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">Select</option>
                        @foreach($leadFroms as $leadFrom)
                            <option value="{{ $leadFrom->id }}">{{ $leadFrom->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea name="description" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
            </div>

            <!-- Submit Button -->
            <div class="text-right">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg">Save</button>
            </div>
        </form>
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

<!-- Scripts -->
<script>
let editMode = false;
let editId = null;

function openModal() {
    document.getElementById('leadModal').classList.remove('hidden');
    document.getElementById('modalTitle').innerText = editMode ? 'Edit Sales Lead' : 'Add Sales Lead';
}

function closeModal() {
    document.getElementById('leadModal').classList.add('hidden');
    document.getElementById('leadForm').reset();
    editMode = false;
    editId = null;
}

document.getElementById('leadForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const url = editMode
        ? `{{ url('sales-leads') }}/${editId}`
        : `{{ route('sales-leads.store') }}`;
    const method = editMode ? 'POST' : 'POST';

    if (editMode) {
        formData.append('_method', 'PUT');
    }

    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            document.getElementById('successMessage').classList.remove('hidden');
            document.getElementById('successMessage').innerText = data.message;
            closeModal();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            alert('Something went wrong.');
        }
    } catch (error) {
        console.error(error);
        alert('Failed to save sales lead.');
    }
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
function editLead(id) {
    fetch(`sales-leads/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            const form = document.getElementById('leadForm');
            editMode = true;
            editId = id;

            form.client_name.value = data.client_name;
            form.client_email.value = data.client_email;
            form.client_phone.value = data.client_phone;
            form.client_type.value = data.client_type;
            form.job_title.value = data.job_title || '';
            form.job_url.value = data.job_url || '';
            form.department_id.value = data.department_id || '';
            form.sales_person_id.value = data.sales_person_id || '';
            form.country_id.value = data.country_id || '';
            form.lead_from_id.value = data.lead_from_id || '';
            form.description.value = data.description || '';

            openModal();
        })
        .catch(error => {
            console.error('Error fetching lead:', error);
        });
}
document.querySelectorAll('form[data-delete]').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!confirm('Delete this lead?')) return;

        const url = this.action;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: new URLSearchParams({
                    '_method': 'DELETE'
                })
            });

            const data = await response.json();

            if (data.success) {
                document.getElementById('successMessage').classList.remove('hidden');
                document.getElementById('successMessage').innerText = data.message;
                setTimeout(() => window.location.reload(), 1000);
            } else {
                alert('Failed to delete sales lead.');
            }
        } catch (error) {
            console.error(error);
            alert('Something went wrong.');
        }
    });
});
document.addEventListener("DOMContentLoaded", function () {
    let searchInput = document.getElementById("searchInput");
    let entriesSelect = document.getElementById("entriesPerPage");
    let table = document.querySelector("table tbody");
    let rows = table.getElementsByTagName("tr");

    // Function to filter table rows based on search input
    function filterTable() {
        let searchText = searchInput.value.toLowerCase();

        Array.from(rows).forEach(row => {
            let textContent = row.innerText.toLowerCase();
            row.style.display = textContent.includes(searchText) ? "" : "none";
        });
    }

    // Function to control entries per page
    function updateEntriesPerPage() {
        let numEntries = parseInt(entriesSelect.value);
        let totalRows = rows.length;

        // Show only selected number of rows
        Array.from(rows).forEach((row, index) => {
            row.style.display = index < numEntries ? "" : "none";
        });
    }

    // Event listeners
    searchInput.addEventListener("keyup", filterTable);
    entriesSelect.addEventListener("change", updateEntriesPerPage);

    // Initialize the table with the default entries per page
    updateEntriesPerPage();
});
 // Open modal with lead ID
 function openUpdateStatusModal(leadId) {
        document.getElementById('lead_id').value = leadId;
        document.getElementById('updateStatusModal').classList.remove('hidden');
    }

    // Close the modal
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
$(document).on('click', '.delete-btn', function () {
    const id = $(this).data('id');

    if (confirm('Are you sure you want to delete this lead?')) {
        $.ajax({
            url: `sales-leads/${id}`,
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    alert(response.message);
                    // Optional: remove the deleted row from the table or reload
                    $(`#row-${id}`).remove();
                }
            },
            error: function (xhr) {
                alert('Something went wrong.');
            }
        });
    }
});

</script>

@endsection
