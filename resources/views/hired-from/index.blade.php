@extends('layouts.dashboard')

@section('title', 'Hired From')

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
    <input type="text" id="searchInput" placeholder="Search Data..." 
        class="border border-gray-300 rounded-md px-4 py-2 w-72 focus:ring focus:border-blue-500 shadow-md text-gray-700 transition">
</div>
<div class="bg-white shadow-md rounded-lg p-6 mb-6 flex justify-between items-center">
    <h2 class="text-2xl font-semibold text-gray-800">Hired From</h2>
    <button onclick="openModal('addModal')" class="bg-blue-600 text-white px-5 py-2 rounded-md shadow hover:bg-blue-700 transition duration-200">
        + Add Hired From
    </button>
</div>

<div class="bg-white shadow-md rounded-lg p-6">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse bg-white shadow-md rounded-lg text-left" id="hiredFromTable">
            <thead class="bg-gray-200 text-gray-700 font-semibold">
                <tr>
                    <th class="border px-5 py-3">S/N</th>
                    <th class="border px-5 py-3">Name</th>
                    <th class="border px-5 py-3">Description</th>
                    <th class="border px-5 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($hiredFroms as $hired)
                    <tr id="hired-{{ $hired->id }}" class="hover:bg-gray-100 transition">
                    <td class="border px-5 py-3 text-gray-700">{{ $hiredFroms->firstItem() + $loop->index }}</td>
                    <td class="border px-5 py-3">{{ $hired->name }}</td>
                        <td class="border px-5 py-3">{{ $hired->description }}</td>
                        <td class="border px-5 py-3 text-center space-x-2">
                            <button onclick="editHiredFrom({{ $hired->id }})" class="bg-yellow-500 text-white px-3 py-1.5 rounded-md shadow hover:bg-yellow-600 transition">
                                Edit
                            </button>
                            <button onclick="deleteHiredFrom(this)" data-id="{{ $hired->id }}" class="bg-red-500 text-white px-3 py-1.5 rounded-md shadow hover:bg-red-600 transition">
                                Delete
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
 <!-- Pagination (Aligned Right) -->
 <div class="mt-6 w-full overflow-x-auto">
    <div class="flex justify-end">
        {{ $hiredFroms->links('pagination::tailwind') }}
    </div>
</div>
<!-- Add/Edit Modal -->
<div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg w-1/3 shadow-lg relative border-4" 
        style="border-image-source: linear-gradient(to right, #0178bc, #00bdda);
               border-image-slice: 1;">
        <button onclick="closeModal('addModal')" class="absolute top-3 right-3 text-2xl text-gray-500 hover:text-black">&times;</button>

        <h2 class="text-2xl font-semibold mb-6 text-gray-800" id="modalTitle">Add Hired From</h2>

        <form id="hiredFromForm">
            @csrf
            <input type="hidden" id="hiredFromId">

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" id="name" name="name" class="w-full px-4 py-2 border rounded-md focus:ring focus:border-blue-500 text-gray-900 shadow-sm">
                <span id="nameError" class="text-red-500 text-sm"></span>
            </div>

            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea id="description" name="description" rows="4" class="w-full px-4 py-2 border rounded-md focus:ring focus:border-blue-500 text-gray-900 shadow-sm"></textarea>
                <span id="descriptionError" class="text-red-500 text-sm"></span>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeModal('addModal')" class="px-4 py-2 bg-gray-500 text-white rounded-md shadow hover:bg-gray-600 transition">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md shadow hover:bg-blue-700 transition">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg w-96 text-center">
        <h2 class="text-lg font-bold text-green-600">Success!</h2>
        <p id="successMessage" class="mt-2"></p>
        <button onclick="closeSuccessModal()" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded">OK</button>
    </div>
</div>

<script>
document.getElementById('hiredFromForm').addEventListener('submit', function (event) {
    event.preventDefault();

    let formData = new FormData(this);
    let id = document.getElementById('hiredFromId').value;
    if (id) formData.append('_method', 'PUT');

    let url = id ? `hired-from/${id}` : 'hired-from';

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showSuccessModal(data.message);
        } else {
            displayErrors(data.errors);
        }
    })
    .catch(error => console.error(error));
});

function displayErrors(errors) {
    document.getElementById('nameError').innerText = errors?.name?.[0] || '';
    document.getElementById('descriptionError').innerText = errors?.description?.[0] || '';
}

function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

function showSuccessModal(message) {
    document.getElementById("successMessage").innerText = message;
    document.getElementById("successModal").classList.remove("hidden");
}

function closeSuccessModal() {
    document.getElementById("successModal").classList.add("hidden");
    location.reload();
}

function editHiredFrom(id) {
    fetch(`hired-from/${id}/edit`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('modalTitle').innerText = 'Edit Hired From';
            document.getElementById('hiredFromId').value = data.id;
            document.getElementById('name').value = data.name;
            document.getElementById('description').value = data.description;
            openModal('addModal');
        });
}

function deleteHiredFrom(btn) {
    const id = btn.getAttribute('data-id');
    if (confirm('Are you sure you want to delete this?')) {
        fetch(`hired-from/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showSuccessModal(data.success);
            }
        });
    }
}
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
</script>
@endsection
