@extends('layouts.dashboard')

@section('title', 'Niches')

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
    <input type="text" id="searchInput" placeholder="Search niches..." 
        class="border border-gray-300 rounded-md px-4 py-2 w-72 shadow-md text-gray-700 transition">
</div>

<div class="bg-white shadow-md rounded-lg p-6">
    <div class="flex justify-between items-center mb-5">
        <h2 class="text-2xl font-semibold text-gray-800">Niches</h2>
        <button onclick="openModal('addModal')" class="bg-blue-600 text-white px-5 py-2 rounded-md shadow hover:bg-blue-700">
            + Add Niche
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse bg-white shadow-md rounded-lg text-left" id="nichesTable">
            <thead class="bg-gray-200 text-gray-700 font-semibold">
                <tr>
                    <th class="border px-5 py-3">S/N</th>
                    <th class="border px-5 py-3">Name</th>
                    <th class="border px-5 py-3">Added By</th>
                    <th class="border px-5 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($niches as $niche)
                    <tr id="niche-{{ $niche->id }}" class="hover:bg-gray-100 transition">
                        <td class="border px-5 py-3 text-gray-700">{{ $niches->firstItem() + $loop->index }}</td>
                        <td class="border px-5 py-3 font-medium text-gray-900">{{ $niche->name }}</td>
                        <td class="border px-5 py-3 text-gray-700">{{ $niche->addedBy->name ?? 'N/A' }}</td>
                        <td class="border px-5 py-3 flex justify-center space-x-2">
                            <button onclick="editNiche({{ $niche->id }})" 
                                class="bg-yellow-500 text-white px-3 py-1.5 rounded-md shadow hover:bg-yellow-600">
                                Edit
                            </button>
                            <button onclick="deleteNiche(this)" data-id="{{ $niche->id }}"
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
        {{ $niches->links() }}
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg w-1/3 shadow-lg relative">
        <button onclick="closeModal('addModal')" class="absolute top-3 right-3 text-2xl text-gray-500 hover:text-black">&times;</button>
        <h2 class="text-2xl font-semibold mb-6 text-gray-800" id="modalTitle">Add Niche</h2>
        <form id="nicheForm" method="POST">
            @csrf
            <input type="hidden" id="nicheId">

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Niche Name</label>
                <input type="text" id="name" name="name" 
                    class="w-full px-4 py-2 border rounded-md focus:ring focus:border-blue-500 text-gray-900 shadow-sm">
                <span id="nameError" class="text-red-500 text-sm"></span>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeModal('addModal')" 
                    class="px-4 py-2 bg-gray-500 text-white rounded-md shadow hover:bg-gray-600">
                    Cancel
                </button>
                <button type="submit" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md shadow hover:bg-blue-700">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg w-96 text-center">
        <h2 class="text-lg font-bold text-green-600">Success!</h2>
        <p id="successMessage" class="mt-2"></p>
        <button onclick="closeSuccessModal()" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded">OK</button>
    </div>
</div>

<script>
document.getElementById('nicheForm').addEventListener('submit', function (event) {
    event.preventDefault();

    let formData = new FormData(this);
    let nicheId = document.getElementById('nicheId').value;

    if (nicheId) formData.append('_method', 'PUT');

    let url = nicheId ? `niches/${nicheId}` : 'niches';

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) showSuccessModal(data.success);
        else displayErrors(data.errors);
    })
    .catch(error => console.error('Error:', error));
});

function displayErrors(errors) {
    document.getElementById('nameError').innerText = errors.name ? errors.name[0] : '';
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

function editNiche(id) {
    fetch(`niches/${id}/edit`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('modalTitle').innerText = 'Edit Niche';
            document.getElementById('nicheId').value = data.id;
            document.getElementById('name').value = data.name;
            openModal('addModal');
        });
}

function deleteNiche(button) {
    const id = button.getAttribute('data-id');
    if (confirm("Are you sure you want to delete this niche?")) {
        fetch(`niches/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`niche-${id}`).remove();
                showSuccessModal(data.success);
            }
        });
    }
}
</script>
@endsection
