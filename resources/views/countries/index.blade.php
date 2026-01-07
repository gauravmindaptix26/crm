@extends('layouts.dashboard')

@section('title', 'Countries')

@section('content')

<div class="flex space-x-4 mb-4 bg-white shadow-md p-4 rounded-lg">
    <a href="{{ route('project-tasks.index') }}" 
       class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md shadow-md transition">
        📊 Manage Project Task
    </a>
</div>
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
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between mb-4">
            <h2 class="text-xl font-bold">Countries</h2>
            <button onclick="openModal('addModal')" class="bg-blue-500 text-white px-4 py-2 rounded">Add Country</button>
        </div>
        <table class="w-full border-collapse border border-gray-200">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-4 py-2">S/N</th>
                    <th class="border px-4 py-2">Country Name</th>
                    <th>Added On & Added By</th>

                    <th class="border px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($countries as $country)
                    <tr id="country-{{ $country->id }}">
                    <td class="border px-4 py-2">{{ $countries->firstItem() + $loop->index }}</td>
                    <td class="border px-4 py-2">{{ $country->name }}</td>
                        <td class="border px-4 py-2">{{ $country->created_at->format('d-m-Y') }}
                        By: {{ $country->creator ? $country->creator->name : 'Unknown' }}</td>
                        <td class="border px-4 py-2">
                        <a href="{{ route('geo-targets.index', ['country_id' => $country->id]) }}" class="bg-green-500 text-white px-4 py-2 rounded">
            Manage Geo Targets
        </a>
                        </td> 
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $countries->links() }}
        </div>
    </div>

    <div id="addModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg w-1/3 relative">
            <button onclick="closeModal('addModal')" class="absolute top-3 right-3 text-xl text-gray-500 hover:text-black">&times;</button>
            <h2 class="text-xl font-bold mb-4" id="modalTitle">Add Country</h2>
            <form id="countryForm">
                @csrf
                <input type="hidden" id="countryId">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Country Name</label>
                    <input type="text" id="name" name="name" class="w-full px-3 py-2 border rounded">
                    <span id="nameError" class="text-red-500 text-sm"></span>
                </div>
                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" onclick="closeModal('addModal')" class="px-4 py-2 bg-gray-500 text-white rounded">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        function editCountry(id) {
            fetch(`countries/${id}/edit`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('modalTitle').innerText = 'Edit Country';
                    document.getElementById('countryId').value = data.id;
                    document.getElementById('name').value = data.name;
                    openModal('addModal');
                });
        }

        document.getElementById('countryForm').addEventListener('submit', function (e) {
            e.preventDefault();
            let id = document.getElementById('countryId').value;
            let url = id ? `countries/${id}` : 'countries';
            let method = id ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                },
                body: JSON.stringify({ name: document.getElementById('name').value }),
            })
            .then(response => response.json())
            .then(data => {
                alert(data.success);
                location.reload();
            });
        });

        function deleteCountry(id) {
            if (!confirm('Are you sure?')) return;
            fetch(`countries/${id}`, { method: 'DELETE' })
            .then(response => response.json())
            .then(data => {
                alert(data.success);
                location.reload();
            });
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
