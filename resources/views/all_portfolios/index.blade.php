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
<div class="container mx-auto p-4">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">All Portfolios</h2>
        <button onclick="openAddModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Add Portfolio</button>
    </div>
    <form method="GET" action="{{ route('all-portfolios.index') }}" class="mb-4 flex flex-wrap gap-4 items-end">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Country</label>
        <select name="country_id" class="border border-gray-300 rounded-md px-3 py-2 w-48">
            <option value="">-- All Countries --</option>
            @foreach ($countries as $country)
                <option value="{{ $country->id }}" {{ request('country_id') == $country->id ? 'selected' : '' }}>
                    {{ $country->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Department</label>
        <select name="department_id" class="border border-gray-300 rounded-md px-3 py-2 w-48">
            <option value="">-- All Departments --</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                    {{ $department->name }}
                </option>
            @endforeach
        </select>
    </div>

    

    <div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md mt-1">Search</button>
    </div>
</form>

    <div class="bg-white shadow rounded p-4">
    <table class="min-w-full table-auto border">
        <thead>
            <tr class="bg-gray-100 text-left">
                <th class="p-2 border">Title</th>
                <th class="p-2 border">Country</th>
                <th class="p-2 border">Attachment</th>
                <th class="p-2 border">Department</th>
                <th class="p-2 border">Added By</th>
                <th class="p-2 border">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($portfolios as $portfolio)
                <tr>
                    <td class="p-2 border">{{ $portfolio->title }}</td>
                    <td class="p-2 border">{{ $portfolio->country->name ?? '-' }}</td>
                    <td class="p-2 border text-center">
    @if ($portfolio->attachment)
        @php
            $attachmentUrl = asset('storage/' . $portfolio->attachment);
            $extension = pathinfo($portfolio->attachment, PATHINFO_EXTENSION);
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        @endphp

        <div class="flex flex-col items-center space-y-1">
    @if (in_array(strtolower($extension), $imageExtensions))
        <img src="{{ $attachmentUrl }}" alt="Attachment" class="w-16 h-16 object-cover rounded border" />
    @else
        <span class="text-gray-700 text-sm">{{ basename($portfolio->attachment) }}</span>
    @endif

    <a href="{{ $attachmentUrl }}" target="_blank" class="text-blue-600 underline text-sm">View</a>
</div>

    @else
        -
    @endif
</td>

                    <td class="p-2 border">{{ $portfolio->department->name ?? '-' }}</td>
                    <td class="p-2 border">{{ $portfolio->creator->name ?? '-' }}</td>
                    <td class="p-2 border">
                        <button onclick="editPortfolio({{ $portfolio->id }})" class="text-blue-600 hover:underline">Edit</button>
                        <button onclick="deletePortfolio({{ $portfolio->id }})" class="text-red-600 hover:underline ml-2">Delete</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $portfolios->links() }}
    </div>
</div>

<!-- Modal -->
<div id="portfolioModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden z-50">
    <div class="bg-white p-6 rounded shadow w-full max-w-xl relative">
        <h3 id="modalTitle" class="text-xl font-bold mb-4">Add Portfolio</h3>
        <form id="portfolioForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" id="portfolio_id">

            <div class="mb-4">
                <label class="block font-semibold mb-1">Title</label>
                <input type="text" name="title" id="title" class="w-full border rounded p-2" required>
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-1">Country</label>
                <select name="country_id" id="country_id" class="w-full border rounded p-2" required>
                    <option value="">-- Select Country --</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-1">Department</label>
                <select name="department_id" id="department_id" class="w-full border rounded p-2" required>
                    <option value="">-- Select Department --</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-1">Description</label>
                <textarea name="description" id="description" rows="4" class="w-full border rounded p-2"></textarea>
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-1">Attachment</label>
                <input type="file" name="attachment" id="attachment" class="w-full border rounded p-2">
            </div>

            <div class="flex justify-end">
                <button type="button" onclick="closeModal()" class="bg-gray-500 text-white px-4 py-2 rounded mr-2">Cancel</button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('portfolioModal');
    const form = document.getElementById('portfolioForm');
    const titleField = document.getElementById('title');
    const countryField = document.getElementById('country_id');
    const departmentField = document.getElementById('department_id');
    const descriptionField = document.getElementById('description');
    const fileField = document.getElementById('attachment');
    const idField = document.getElementById('portfolio_id');
    const modalTitle = document.getElementById('modalTitle');

    function openAddModal() {
        form.reset();
        modalTitle.textContent = 'Add Portfolio';
        idField.value = '';
        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(form);
        const id = idField.value;
        const method = id ? 'POST' : 'POST';
        const url = id ? `all-portfolios/${id}` : 'all-portfolios';

        if (id) {
            formData.append('_method', 'PUT');
        }

        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alert(data.success);
            window.location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Something went wrong.');
        });
    });

    function editPortfolio(id) {
        fetch(`all-portfolios/${id}/edit`)
            .then(res => res.json())
            .then(data => {
                modalTitle.textContent = 'Edit Portfolio';
                idField.value = data.id;
                titleField.value = data.title;
                countryField.value = data.country_id;
                departmentField.value = data.department_id;
                descriptionField.value = data.description;
                modal.classList.remove('hidden');
            });
    }

    function deletePortfolio(id) {
        if (confirm('Are you sure you want to delete this portfolio?')) {
            fetch(`all-portfolios/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                alert(data.success);
                window.location.reload();
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
