@extends('layouts.dashboard')

@section('title', 'R&D Management')

@section('content')
<div class="bg-white shadow-md rounded-lg p-6 mb-6 flex justify-between items-center">
    <div class="flex items-center space-x-3">
        <label for="entriesPerPage" class="text-sm font-medium text-gray-600">Show</label>
        <select id="entriesPerPage" class="border border-gray-300 rounded-md px-3 py-1.5 text-sm">
            <option value="10" selected>10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
        <span class="text-sm font-medium text-gray-600">entries</span>
    </div>
    <input type="text" id="searchInput" placeholder="Search..." 
        class="border border-gray-300 rounded-md px-4 py-2 w-72 shadow-md text-gray-700 transition">
</div>

<div class="bg-white shadow-md rounded-lg p-6">
    <div class="flex justify-between items-center mb-5">
        <h2 class="text-2xl font-semibold text-gray-800">R&D List</h2>
        <button onclick="openModal('addModal')" class="bg-blue-600 text-white px-5 py-2 rounded-md shadow hover:bg-blue-700">
            + Add R&D
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse bg-white shadow-md rounded-lg text-left" id="rndTable">
            <thead class="bg-gray-200 text-gray-700 font-semibold">
                <tr>
                    <th class="border px-5 py-3">S/N</th>
                    <th class="border px-5 py-3">Title</th>
                    <th class="border px-5 py-3">Description</th>
                    <th class="border px-5 py-3">Created By</th>
                    <th class="border px-5 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($allRnds as $rnd)
                    <tr id="rnd-{{ $rnd->id }}" class="hover:bg-gray-100 transition">
                        <td class="border px-5 py-3">{{ $loop->iteration }}</td>
                        <td class="border px-5 py-3">{{ $rnd->title }}</td>
                        <td class="border px-5 py-3">{{ Str::limit($rnd->description, 50) }}</td>
                        <td class="border px-5 py-3">{{ $rnd->createdBy->name ?? 'N/A' }}</td>
                        <td class="border px-5 py-3 flex justify-center space-x-2">
                            <button onclick="editRnd({{ $rnd->id }})" 
                                class="bg-yellow-500 text-white px-3 py-1.5 rounded-md shadow hover:bg-yellow-600">
                                Edit
                            </button>
                            <button onclick="deleteRnd(this)" data-id="{{ $rnd->id }}"
                                class="bg-red-500 text-white px-3 py-1.5 rounded-md shadow hover:bg-red-600">
                                Delete
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6 flex justify-end">
        {{ $allRnds->links() }}
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-8 rounded-lg w-1/2 shadow-lg relative">
        <button onclick="closeModal('addModal')" class="absolute top-3 right-3 text-2xl text-gray-500 hover:text-black">&times;</button>
        <h2 class="text-2xl font-semibold mb-6 text-gray-800" id="modalTitle">Add R&D</h2>
        <form id="rndForm" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="rndId" name="id">

            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" id="title" name="title" class="w-full px-4 py-2 border rounded-md focus:ring text-gray-900 shadow-sm">
                <span id="titleError" class="text-red-500 text-sm"></span>
            </div>

            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea id="description" name="description" rows="6" class="w-full px-4 py-2 border rounded-md focus:ring text-gray-900 shadow-sm"></textarea>
                <span id="descriptionError" class="text-red-500 text-sm"></span>
            </div>

            <div class="mb-4">
                <label for="urls" class="block text-sm font-medium text-gray-700">URLs</label>
                <input type="text" id="urls" name="urls" class="w-full px-4 py-2 border rounded-md focus:ring text-gray-900 shadow-sm">
                <span id="urlsError" class="text-red-500 text-sm"></span>
            </div>

            <div class="mb-4">
                <label for="department_id" class="block text-sm font-medium text-gray-700">Department</label>
                <select id="department_id" name="department_id" class="w-full px-4 py-2 border rounded-md focus:ring text-gray-900 shadow-sm">
                    <option value="">Select Department</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
                <span id="department_idError" class="text-red-500 text-sm"></span>
            </div>

            <div class="mb-4">
                <label for="attachment" class="block text-sm font-medium text-gray-700">Attachment</label>
                <input type="file" id="attachment" name="attachment" class="w-full px-4 py-2 border rounded-md text-gray-900 shadow-sm">
                <span id="attachmentError" class="text-red-500 text-sm"></span>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeModal('addModal')" class="px-4 py-2 bg-gray-500 text-white rounded-md shadow hover:bg-gray-600">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md shadow hover:bg-blue-700">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg w-96 text-center">
        <h2 class="text-lg font-bold text-green-600">Success!</h2>
        <p id="successMessage" class="mt-2"></p>
        <button onclick="closeSuccessModal()" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded">OK</button>
    </div>
</div>

<script>
document.getElementById('rndForm').addEventListener('submit', function (event) {
    event.preventDefault();

    let form = this;
    let formData = new FormData(form);
    let rndId = document.getElementById('rndId').value;
    let url = rndId ? `all-rnds/${rndId}` : 'all-rnds';
    let method = rndId ? 'POST' : 'POST'; // still use POST, with _method for PUT

    if (rndId) formData.append('_method', 'PUT');

    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal('addModal');
            showSuccessModal(data.success);
        } else {
            displayErrors(data.errors);
        }
    })
    .catch(error => console.error('Error:', error));
});

function displayErrors(errors) {
    const fields = ['title', 'description', 'urls', 'department_id', 'attachment'];
    fields.forEach(field => {
        document.getElementById(`${field}Error`).innerText = errors[field] ? errors[field][0] : '';
    });
}

function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
    clearForm();
}

function clearForm() {
    document.getElementById('rndForm').reset();
    document.getElementById('rndId').value = '';
    document.getElementById('modalTitle').innerText = 'Add R&D';
    const errorFields = ['titleError', 'descriptionError', 'urlsError', 'department_idError', 'attachmentError'];
    errorFields.forEach(id => document.getElementById(id).innerText = '');
}

function showSuccessModal(message) {
    document.getElementById("successMessage").innerText = message;
    document.getElementById("successModal").classList.remove("hidden");
}

function closeSuccessModal() {
    document.getElementById("successModal").classList.add("hidden");
    location.reload();
}

function editRnd(id) {
    fetch(`all-rnds/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            // Populate the modal with the existing data
            document.getElementById('modalTitle').innerText = 'Edit R&D';
            document.getElementById('rndId').value = data.id;
            document.getElementById('title').value = data.title;
            document.getElementById('description').value = data.description;
            document.getElementById('urls').value = data.urls ?? '';
            document.getElementById('department_id').value = data.department_id ?? '';

            // If there is an attachment, you can show it in a preview or handle it appropriately
            // Example: Set a default preview image or a link
            // document.getElementById('attachmentPreview').href = data.attachment_url; 

            openModal('addModal');
        })
        .catch(error => console.error('Error fetching data:', error));
}


function deleteRnd(button) {
    const id = button.getAttribute('data-id');
    if (confirm("Are you sure you want to delete this R&D record?")) {
        fetch(`all-rnds/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessModal(data.success);
                document.getElementById(`rnd-${id}`).remove();
            }
        });
    }
}
</script>


@endsection
