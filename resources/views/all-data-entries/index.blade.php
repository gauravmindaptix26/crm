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
    <input type="text" id="searchInput" placeholder="Search Data..." 
        class="border border-gray-300 rounded-md px-4 py-2 w-72 focus:ring focus:border-blue-500 shadow-md text-gray-700 transition">
</div>
<div class="p-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">All Data Entries</h1>
        <button id="addEntryBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">+ Add Entry</button>
    </div>

    {{-- Listing Table --}}
<div class="overflow-x-auto bg-white shadow rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-2 text-left">Name</th>
                <th class="px-4 py-2 text-left">Email</th>
                <th class="px-4 py-2 text-left">Phone Number</th>
                <th class="px-4 py-2 text-left">Data Option</th>
                <th class="px-4 py-2 text-left">Description</th>
                <th class="px-4 py-2 text-left">Added By</th>
                <th class="px-4 py-2 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($entries as $entry)
            <tr>
                <td class="px-4 py-2">{{ $entry->name }}</td>
                <td class="px-4 py-2">{{ $entry->email }}</td>
                <td class="px-4 py-2">{{ $entry->phone_number }}</td>
                <td class="px-4 py-2">{{ $entry->data_option }}</td>
                <td class="px-4 py-2">{{ Str::limit($entry->description, 50) }}</td>
                <td class="px-4 py-2">
    <div class="flex items-center">
        <span class="mr-2">By: {{ $entry->creator->name ?? 'N/A' }}</span>
        <span>At: {{ $entry->created_at->format('Y-m-d') }}</span>
    </div>
</td>
                <td class="px-4 py-2 text-right">
                    <button onclick="editEntry({{ $entry->id }})" class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded mr-2">Edit</button>
                    <button onclick="deleteEntry({{ $entry->id }})" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">Delete</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="p-4">
        {{ $entries->links() }}
    </div>
</div>


{{-- Modal --}}
<div id="entryModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white w-full max-w-xl p-6 rounded shadow-lg relative">
        <button onclick="closeModal()" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900 text-2xl">&times;</button>
        <h2 id="modalTitle" class="text-xl font-bold mb-4">Add Entry</h2>
        <form id="entryForm">
            @csrf
            <input type="hidden" name="id" id="entryId">

            <div class="mb-3">
                <label for="name" class="block font-medium">Name</label>
                <input type="text" name="name" id="name" class="w-full border border-gray-300 rounded px-3 py-2">
                <span class="text-red-500 text-sm error-text name_error"></span>
            </div>

            <div class="mb-3">
                <label for="phone_number" class="block font-medium">Phone Number</label>
                <input type="text" name="phone_number" id="phone_number" class="w-full border border-gray-300 rounded px-3 py-2">
                <span class="text-red-500 text-sm error-text phone_number_error"></span>
            </div>

            <div class="mb-3">
                <label for="email" class="block font-medium">Email</label>
                <input type="email" name="email" id="email" class="w-full border border-gray-300 rounded px-3 py-2">
                <span class="text-red-500 text-sm error-text email_error"></span>
            </div>

            <div class="mb-3">
                <label for="data_option" class="block font-medium">Data Option</label>
                <select name="data_option" id="data_option" class="w-full border border-gray-300 rounded px-3 py-2">
                    <option value="">-- Select --</option>
                    <option value="SSL">SSL</option>
                    <option value="Speed Optimization">Speed Optimization</option>
                    <option value="Live Chat">Live Chat</option>
                    <option value="Logo Design">Logo Design</option>
                    <option value="Cookie Policy">Cookie Policy</option>
                    <option value="Footer Optimization">Footer Optimization</option>
                </select>
                <span class="text-red-500 text-sm error-text data_option_error"></span>
            </div>

            <div class="mb-3">
                <label for="description" class="block font-medium">Description</label>
                <textarea name="description" id="description" class="w-full border border-gray-300 rounded px-3 py-2" rows="3"></textarea>
                <span class="text-red-500 text-sm error-text description_error"></span>
            </div>

            <div class="flex justify-end">
                <button type="button" onclick="closeModal()" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded mr-2">Cancel</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- Success Modal --}}
<div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white p-6 rounded shadow-lg">
        <p class="text-green-600 font-bold text-lg">Saved successfully!</p>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const modal = document.getElementById('entryModal');
    const form = document.getElementById('entryForm');

    document.getElementById('addEntryBtn').addEventListener('click', () => {
        form.reset();
        document.getElementById('entryId').value = '';
        document.getElementById('modalTitle').innerText = 'Add Entry';
        modal.classList.remove('hidden');
        clearErrors();
    });

    function closeModal() {
        modal.classList.add('hidden');
        clearErrors();
    }

    function clearErrors() {
        document.querySelectorAll('.error-text').forEach(el => el.textContent = '');
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        clearErrors();
        const id = document.getElementById('entryId').value;
        const url = id ? `all-data-entries/${id}` : 'all-data-entries';
        const method = id ? 'PUT' : 'POST';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': form.querySelector('input[name=_token]').value
            },
            body: JSON.stringify({
                name: form.name.value,
                phone_number: form.phone_number.value,
                email: form.email.value,
                data_option: form.data_option.value,
                description: form.description.value
            })
        })
        .then(res => {
            if (res.status === 422) {
                return res.json().then(data => {
                    Object.entries(data.errors).forEach(([key, val]) => {
                        document.querySelector(`.${key}_error`).textContent = val[0];
                    });
                });
            } else if (res.ok) {
                return res.json().then(() => {
                    modal.classList.add('hidden');
                    document.getElementById('successModal').classList.remove('hidden'); // Show success modal
                    setTimeout(() => {
                        document.getElementById('successModal').classList.add('hidden'); // Hide after 1 second
                        location.reload(); // Optionally reload to show updated data
                    }, 1000); // Adjust the duration as needed
                });
            }
        });
    });

    function editEntry(id) {
        fetch(`all-data-entries/${id}/edit`)
            .then(res => res.json())
            .then(data => {
                form.name.value = data.name;
                form.phone_number.value = data.phone_number;
                form.email.value = data.email;
                form.data_option.value = data.data_option;
                form.description.value = data.description;
                document.getElementById('entryId').value = data.id;
                document.getElementById('modalTitle').innerText = 'Edit Entry';
                modal.classList.remove('hidden');
                clearErrors();
            });
    }

    function deleteEntry(id) {
        if (confirm('Are you sure you want to delete this entry?')) {
            fetch(`all-data-entries/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': form.querySelector('input[name=_token]').value
                }
            }).then(() => location.reload());
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
