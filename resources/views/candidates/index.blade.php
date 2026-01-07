@extends('layouts.dashboard')

@section('content')
<div class="bg-white shadow-md rounded-lg p-4 mb-4 flex justify-between items-center">
    <div class="flex items-center space-x-2">
        <label for="entriesPerPage" class="text-sm font-medium text-gray-700">Show</label>
        <select id="entriesPerPage" class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:border-blue-500 text-sm">
            <option value="10" selected>10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
        <span class="text-sm font-medium text-gray-700">entries</span>
    </div>
    <input type="text" id="searchInput" placeholder="Search..." 
           class="border border-gray-300 rounded-lg px-4 py-2 w-64 focus:ring focus:border-blue-500 shadow-sm">
</div>
<div class="bg-white p-6 shadow-md rounded-md">
    <form method="GET" action="{{ route('candidates.index') }}" id="candidateFilterForm">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="filter_department" class="block text-sm font-bold text-black mb-1">Department</label>
                <select id="filter_department" name="filter_department" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white shadow-sm focus:outline-none transition duration-150">
                    <option value="">-- Select --</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" {{ request('filter_department') == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter_status" class="block text-sm font-bold text-black mb-1">Status</label>
                <select id="filter_status" name="filter_status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white shadow-sm focus:outline-none transition duration-150">
                    <option value="">-- Select --</option>
                    <option value="Selected" {{ request('filter_status') == 'Selected' ? 'selected' : '' }}>Selected</option>
                    <option value="Shortlist" {{ request('filter_status') == 'Shortlist' ? 'selected' : '' }}>Shortlist</option>
                    <option value="Scheduled" {{ request('filter_status') == 'Scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="Offered" {{ request('filter_status') == 'Offered' ? 'selected' : '' }}>Offered</option>
                    <option value="Hired" {{ request('filter_status') == 'Hired' ? 'selected' : '' }}>Hired</option>
                    <option value="Rejection Due to Salary Issue" {{ request('filter_status') == 'Rejection Due to Salary Issue' ? 'selected' : '' }}>Rejection Due to Salary Issue</option>
                    <option value="Hold" {{ request('filter_status') == 'Hold' ? 'selected' : '' }}>Hold</option>
                    <option value="Blacklisted" {{ request('filter_status') == 'Blacklisted' ? 'selected' : '' }}>Blacklisted</option>
                    <option value="Technically Rejected" {{ request('filter_status') == 'Technically Rejected' ? 'selected' : '' }}>Technically Rejected</option>
                </select>
            </div>
            <div>
                <label for="filter_added_by" class="block text-sm font-bold text-black mb-1">Added By</label>
                <select id="filter_added_by" name="filter_added_by" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white shadow-sm focus:outline-none transition duration-150">
                    <option value="">-- Select --</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ request('filter_added_by') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button id="filterButton" class="w-full sm:w-auto text-white font-medium px-6 py-2 rounded-lg shadow-md 
                        bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600
                        hover:from-teal-500 hover:via-teal-600 hover:to-teal-700
                        focus:outline-none">Filter</button>
            </div>
        </div>
    </form>
</div>

<div class="bg-white shadow-lg rounded-lg p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-gray-700">Candidate Management</h2>
        <button onclick="openModal()" class="bg-black hover:bg-gray-900 text-white px-4 py-2 rounded-lg shadow-md transition-all flex items-center space-x-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                 stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M13.5 16.875h3.375m0 0h3.375m-3.375 0V13.5m0 3.375v3.375M6 10.5h2.25a2.25 2.25 0 0 0 2.25-2.25V6a2.25 2.25 0 0 0-2.25-2.25H6A2.25 2.25 0 0 0 3.75 6v2.25A2.25 2.25 0 0 0 6 10.5Zm0 9.75h2.25A2.25 2.25 0 0 0 10.5 18v-2.25a2.25 2.25 0 0 0-2.25-2.25H6a2.25 2.25 0 0 0-2.25 2.25V18A2.25 2.25 0 0 0 6 20.25Zm9.75-9.75H18a2.25 2.25 0 0 0 2.25-2.25V6A2.25 2.25 0 0 0 18 3.75h-2.25A2.25 2.25 0 0 0 13.5 6v2.25a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg> 
            <span>Add Candidate</span>
        </button>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse border border-gray-200 rounded-lg overflow-hidden" id="candidatesTable">
            <thead>
                <tr class="bg-[#14b8a6f2] text-white text-left">
                    <th class="border px-6 py-3">S/No</th>
                    <th class="border px-6 py-3">Basic Info</th>
                    <th class="border px-6 py-3">Experience</th>
                    <th class="border px-6 py-3">Current Salary</th>
                    <th class="border px-6 py-3">Expected Salary</th>
                    <th class="border px-6 py-3">Comments</th>
                    <th class="border px-6 py-3">Status</th>
                    <th class="border px-6 py-3">Offered Salary</th>
                    <th class="border px-6 py-3">Department</th>
                    <th class="border px-6 py-3">Added By</th>
                    <th class="border px-6 py-3">Date of Joining</th>
                    <th class="border px-6 py-3">Resume</th>
                    <th class="border px-6 py-3">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200" id="candidatesTableBody">
                @if ($candidates->count() > 0)
                    @foreach ($candidates as $candidate)
                        <tr id="candidate-{{ $candidate->id }}" class="hover:bg-gray-50 transition-all">
                            <td class="border px-6 py-4 text-center">{{ $candidates->firstItem() + $loop->index }}</td>
                            <td class="border px-6 py-4 text-sm text-gray-700 leading-relaxed">
                                <div><strong>Name:</strong> {{ $candidate->name }}</div>
                                <div><strong>Email:</strong> {{ $candidate->email }}</div>
                                <div><strong>Phone:</strong> {{ $candidate->phone_number }}</div>
                            </td>
                            <td class="border px-6 py-4 text-gray-600">{{ $candidate->experience ?? '-' }}</td>
                            <td class="border px-6 py-4 text-gray-600">{{ $candidate->current_salary ?? '-' }}</td>
                            <td class="border px-6 py-4 text-gray-600">{{ $candidate->expected_salary ?? '-' }}</td>
                            <td class="border px-6 py-4 text-gray-600">{{ $candidate->comments ?? '-' }}</td>
                            <td class="border px-6 py-4 text-gray-600">{{ $candidate->status }}</td>
                            <td class="border px-6 py-4 text-gray-600">{{ $candidate->offered_salary ?? '-' }}</td>
                            <td class="border px-6 py-4 text-gray-600">{{ $candidate->department->name ?? '-' }}</td>
                            <td class="border px-6 py-4 text-gray-600">{{ $candidate->addedBy->name ?? 'N/A' }}</td>
                            <td class="border px-6 py-4 text-gray-600">
                                {{ $candidate->date_of_joining ? \Carbon\Carbon::parse($candidate->date_of_joining)->format('d M, Y') : 'N/A' }}
                            </td>
                            <td class="border px-6 py-4 text-gray-600">
                                <div class="flex flex-col space-y-1">
                                    @if ($candidate->resume)
                                        <a href="{{ asset('storage/' . $candidate->resume) }}" class="text-green-600 hover:underline" target="_blank">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </a>
                                    @else
                                        -
                                    @endif
                                </div>
                            </td>
                            <td class="border px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="editCandidate({{ $candidate->id }})" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded shadow-md transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                    </button>
                                    <button onclick="deleteCandidate({{ $candidate->id }})" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded shadow-md transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="13" class="border px-6 py-4 text-center text-gray-500">No records found.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div id="paginationLinks" class="mt-4">
        {{ $candidates->links() }}
    </div>

    <!-- Add/Edit Candidate Modal -->
    <div id="candidateModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center p-4">
        <div class="bg-white p-6 rounded-lg w-full max-w-2xl relative overflow-y-auto max-h-[90vh]">
            <button onclick="closeModal()" class="absolute top-3 right-3 bg-black text-white text-2xl hover:bg-gray-800 rounded-full w-8 h-8 flex items-center justify-center">&times;</button>
            <h2 class="text-xl font-bold mb-4 text-center bg-[#14b8a6f2] text-white p-[10px] rounded" id="modalTitle">Add Candidate</h2>
            <form id="candidateForm" enctype="multipart/form-data" method="POST">
                @csrf
                <input type="hidden" id="candidateId" name="candidateId">
                <div class="grid grid-cols-3 gap-4">
                    <div><label class="mb-[3px] inline-block">Name</label><input type="text" id="name" name="name" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none"></div>
                    <div><label class="mb-[3px] inline-block">Email</label><input type="email" id="email" name="email" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none"></div>
                    <div><label class="mb-[3px] inline-block">Phone Number</label><input type="text" id="phone_number" name="phone_number" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none"></div>
                    <div><label class="mb-[3px] inline-block">Experience (Years)</label><input type="text" id="experience" name="experience" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none"></div>
                    <div><label class="mb-[3px] inline-block">Current Salary</label><input type="text" id="current_salary" name="current_salary" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none"></div>
                    <div><label class="mb-[3px] inline-block">Expected Salary</label><input type="text" id="expected_salary" name="expected_salary" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none"></div>
                    <div><label class="mb-[3px] inline-block">Offered Salary</label><input type="text" id="offered_salary" name="offered_salary" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none"></div>
                    <div><label class="mb-[3px] inline-block">Date of Joining</label><input type="date" id="date_of_joining" name="date_of_joining" class="w-full px-3 py-2 border rounded"></div>
                    <div><label class="mb-[3px] inline-block">Department</label>
                        <select id="department_id" name="department_id" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
                            <option value="">-- Select --</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-[3px] inline-block">Status</label>
                        <select id="status" name="status" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
                            <option value="">Please select</option>
                            <option value="Selected">Selected</option>
                            <option value="Shortlist">Shortlist</option>
                            <option value="Scheduled">Scheduled</option>
                            <option value="Offered">Offered</option>
                            <option value="Hired">Hired</option>
                            <option value="Rejection Due to Salary Issue">Rejection Due to Salary Issue</option>
                            <option value="Hold">Hold</option>
                            <option value="Blacklisted">Blacklisted</option>
                            <option value="Technically Rejected">Technically Rejected</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="mb-[3px] inline-block">File Upload</label>
                        <input type="file" id="resume" name="resume" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none">
                        <div id="pdfPreview" class="mt-2"></div>
                    </div>
                    <div class="col-span-3"><label class="mb-[3px] inline-block">Comments</label>
                        <textarea id="comments" name="comments" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none"></textarea>
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600
                            hover:from-teal-500 hover:via-teal-600 hover:to-teal-700 text-white rounded hover:bg-blue-700 transition-all">Save</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
    }
});

function openModal() {
    document.getElementById('candidateModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('candidateModal').classList.add('hidden');
    document.getElementById('candidateForm').reset();
    document.getElementById('candidateId').value = '';
    document.getElementById('modalTitle').innerText = 'Add Candidate';
    document.getElementById('pdfPreview').innerHTML = '';
}

$('#candidateForm').submit(function(event) {
    event.preventDefault();
    let candidateId = $('#candidateId').val();
    let url = candidateId ? `candidates/${candidateId}` : "{{ route('candidates.store') }}";
    let formData = new FormData(this);
    if (candidateId) {
        formData.append('_method', 'PUT');
    }
    $.ajax({
        url: url,
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            $('#candidateModal').addClass('hidden');
            alert(response.message);
            location.reload();
        },
        error: function(xhr) {
            $('.error-message').remove();
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                let errors = xhr.responseJSON.errors;
                for (let key in errors) {
                    let errorMessage = `<span class="text-red-500 text-sm error-message">${errors[key][0]}</span>`;
                    $(`[name="${key}"]`).after(errorMessage);
                }
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                alert(`Error: ${xhr.responseJSON.message}`);
            } else {
                alert('Unexpected error occurred. Check console for details.');
                console.error(xhr);
            }
        }
    });
});

function redirectToListing() {
    $('#successModal').remove();
    location.reload();
}

function editCandidate(id) {
    $.ajax({
        url: `candidates/${id}/edit`,
        method: "GET",
        success: function(response) {
            $('#candidateId').val(response.id);
            $('#name').val(response.name);
            $('#email').val(response.email);
            $('#phone_number').val(response.phone_number);
            $('#experience').val(response.experience);
            $('#current_salary').val(response.current_salary);
            $('#expected_salary').val(response.expected_salary);
            $('#offered_salary').val(response.offered_salary);
            $('#date_of_joining').val(response.date_of_joining);
            $('#department_id').val(response.department_id);
            $('#status').val(response.status);
            $('#comments').val(response.comments);
            if (response.resume) {
                let fileUrl = `storage/${response.resume}`;
                $('#pdfPreview').html(`
                    <p><a href="${fileUrl}" target="_blank" class="text-blue-500 underline">View File</a></p>
                `);
            } else {
                $('#pdfPreview').html('');
            }
            openModal();
        },
        error: function(xhr) {
            console.error("Error fetching candidate data:", xhr.responseText);
            alert("Error fetching candidate data");
        }
    });
}

function deleteCandidate(id) {
    if (!confirm('Are you sure you want to delete this candidate?')) return;
    fetch(`candidates/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        alert(data.success);
        location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete candidate.');
    });
}

function fetchCandidates(url) {
    if (!url) {
        var formData = {
            search: $('#searchInput').val() || '',
            filter_department: $('#filter_department').val() || '',
            filter_status: $('#filter_status').val() || '',
            filter_added_by: $('#filter_added_by').val() || '',
            entries_per_page: $('#entriesPerPage').val() || '10',
            _t: new Date().getTime()
        };
        url = "{{ route('candidates.index') }}?" + $.param(formData);
    }
    console.log('Fetching candidates with URL:', url);
    $.ajax({
        url: url,
        type: "GET",
        cache: false,
        success: function(response) {
            console.log('Response received:', response);
            let tbody = $('#candidatesTableBody');
            let tempContainer = $('<div>').html(response);
            let newTbodyContent = tempContainer.find('#candidatesTableBody').html();
            if (newTbodyContent && $.trim(newTbodyContent)) {
                tbody.html(newTbodyContent);
            } else {
                tbody.html('<tr><td colspan="13" class="border px-6 py-4 text-center text-gray-500">No records found.</td></tr>');
            }
            let pagination = tempContainer.find('#paginationLinks').html();
            $('#paginationLinks').html(pagination || '');
        },
        error: function(xhr) {
            console.error('Error:', xhr.responseText);
            alert("Error fetching candidates. Please try again.");
        }
    });
}

// Initialize event listeners
$(document).ready(function() {
    fetchCandidates();
    $('#candidateFilterForm').submit(function(e) {
        e.preventDefault();
        fetchCandidates();
    });
    $('#searchInput').on('input', function() {
        fetchCandidates();
    });
    $('#entriesPerPage').change(function() {
        fetchCandidates();
    });
    $('#filterButton').click(function(e) {
        e.preventDefault();
        fetchCandidates();
    });
    $(document).on('click', '#paginationLinks a', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        console.log('Navigating to URL:', url);
        fetchCandidates(url);
    });
});

window.addEventListener("load", function() {
    if (performance.navigation.type === 1) {
        window.location.href = "{{ route('candidates.index') }}";
    }
});
</script>
@endsection