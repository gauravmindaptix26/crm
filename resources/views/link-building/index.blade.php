@extends('layouts.dashboard')

@section('title', 'Link Building')

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
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between mb-4">
            <h2 class="text-xl font-bold">Link Building</h2>
            
            <button onclick="openModal()" class="bg-blue-500 text-white px-4 py-2 rounded">Add Entry</button>
        </div>

        <table class="w-full border-collapse border border-gray-200">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-4 py-2">S/N</th>
                    <th class="border px-4 py-2">Website</th>
                    <th class="border px-4 py-2">PA</th>
                    <th class="border px-4 py-2">DA</th>
                    <th class="border px-4 py-2">Niche</th>
                    <th class="border px-4 py-2">Countries</th>
                    <th class="border px-4 py-2">Type of Link</th>
                    <th class="border px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($linkBuildings as $entry)
                    <tr id="entry-{{ $entry->id }}">
                        <td class="border px-4 py-2">{{ $loop->iteration }}</td>
                        <td class="border px-4 py-2">{{ $entry->website }}</td>
                        <td class="border px-4 py-2">{{ $entry->pa }}</td>
                        <td class="border px-4 py-2">{{ $entry->da }}</td>
                        <td class="border px-4 py-2">{{ implode(', ', json_decode($entry->niche, true) ?? []) }}</td>
                        <td class="border px-4 py-2">{{ implode(', ', json_decode($entry->countries, true) ?? []) }}</td>
                        <td class="border px-4 py-2">{{ $entry->type_of_link }}</td>
                        <td class="border px-4 py-2">
                            <button onclick="editEntry({{ $entry->id }})" class="bg-yellow-500 text-white px-3 py-1 rounded">Edit</button>
                            <button onclick="deleteEntry({{ $entry->id }})" class="bg-red-500 text-white px-3 py-1 rounded">Delete</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div id="successModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg">
        <h2 class="text-xl font-bold text-green-600 mb-4">Success!</h2>
        <p>Link Building entry has been added successfully.</p>
        <button onclick="closeSuccessModal()" class="bg-green-500 text-white px-4 py-2 rounded mt-4">OK</button>
    </div>
</div>
<div id="modal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg w-1/3 max-h-[80vh] overflow-y-auto relative">
        <button onclick="closeModal()" class="absolute top-2 right-2 text-gray-600 hover:text-gray-900">&times;</button>
        <h2 id="modalTitle" class="text-xl font-bold mb-4">Add Entry</h2>
        <form id="entryForm" method="POST">
            @csrf
            <input type="hidden" id="entryId" name="entryId">

            <label class="block text-gray-700 font-bold mb-1">Website</label>
            <input type="text" id="website" name="website" placeholder="Website" class="w-full p-2 border rounded mb-2">

            <label for="pa" class="block text-gray-700 font-bold mb-1">PA</label>
            <select id="pa" name="pa" class="w-full p-2 border rounded mb-2">
                @for ($i = 1; $i <= 90; $i++)
                    <option value="{{ $i }}">{{ $i }}</option>
                @endfor
            </select>

            <label for="da" class="block text-gray-700 font-bold mb-1">DA</label>
            <select id="da" name="da" class="w-full p-2 border rounded mb-2">
                @for ($i = 1; $i <= 90; $i++)
                    <option value="{{ $i }}">{{ $i }}</option>
                @endfor
            </select>

            <div class="mb-2">
                <label class="block text-gray-700 font-bold mb-1">Niche</label>
                @foreach (['Warehouse/Logistics', 'Education', 'Seeking Article/Blog writer for Australian Education Website'] as $niche)
                    <input type="checkbox" name="niche[]" value="{{ $niche }}"> {{ $niche }}
                @endforeach
            </div>

            <div class="mb-2">
                <label class="block text-gray-700 font-bold mb-1">Countries</label>
                @foreach ($countries as $country)
                    <input type="checkbox" name="countries[]" value="{{ $country->name }}"> {{ $country->name }}
                @endforeach
            </div>

            <label class="block text-gray-700 font-bold mb-1">Type of Link</label>
            <input type="text" id="type_of_link" name="type_of_link" placeholder="Type of Link" class="w-full p-2 border rounded mb-2">

            <button type="submit" class="bg-blue-500 text-white p-2 rounded">Save</button>
        </form>
    </div>
</div>


@endsection

@section('scripts')
<script>

document.getElementById("entryForm").addEventListener("submit", function (e) {
    e.preventDefault();

    let formData = new FormData(this);
    formData.append("niche", JSON.stringify([...document.querySelectorAll("input[name='niche[]']:checked")].map(cb => cb.value)));
    formData.append("countries", JSON.stringify([...document.querySelectorAll("input[name='countries[]']:checked")].map(cb => cb.value)));

    let entryId = document.getElementById("entryId").value;
    let url = entryId ? `link-building/${entryId}` : "link-building"; 
    let method = entryId ? "PUT" : "POST";

    // If updating, we need to add _method=PUT to trick Laravel into recognizing it
    if (entryId) {
        formData.append("_method", "PUT");
    }

    fetch(url, {
        method: "POST", // Always use POST and let Laravel handle _method
        body: formData,
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal(); // Close the modal
            showSuccessModal(); // Show success message
        }
    })
    .catch(error => console.error("Error:", error));
});



// Function to show the success modal
function showSuccessModal() {
    document.getElementById("successModal").classList.remove("hidden");
}

// Function to close the success modal and refresh the page
function closeSuccessModal() {
    document.getElementById("successModal").classList.add("hidden");
    location.reload();
}

// Function to close the form modal
function closeModal() {
    document.getElementById("modal").classList.add("hidden");
}

    function openModal() {
        document.getElementById("modal").classList.remove("hidden");
        document.getElementById("modalTitle").innerText = "Add Link Building Entry";
        document.getElementById("entryId").value = "";
        document.getElementById("website").value = "";
        document.getElementById("pa").value = "";
        document.getElementById("da").value = "";
        document.getElementById("type_of_link").value = "";

        document.querySelectorAll("input[name='niche[]']").forEach(cb => cb.checked = false);
        document.querySelectorAll("input[name='countries[]']").forEach(cb => cb.checked = false);
    }

    function editEntry(id) {
    fetch(`link-building/${id}/edit`, {
        method: "GET",
        headers: { "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content") }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById("modalTitle").innerText = "Edit Link Building Entry";
        document.getElementById("entryId").value = data.id;
        document.getElementById("website").value = data.website;
        document.getElementById("pa").value = data.pa;
        document.getElementById("da").value = data.da;
        document.getElementById("type_of_link").value = data.type_of_link;

        let niches = JSON.parse(data.niche);
        document.querySelectorAll("input[name='niche[]']").forEach(cb => cb.checked = niches.includes(cb.value));

        let countries = JSON.parse(data.countries);
        document.querySelectorAll("input[name='countries[]']").forEach(cb => cb.checked = countries.includes(cb.value));

        document.getElementById("modal").classList.remove("hidden");
    })
    .catch(error => console.error("Error fetching entry:", error));
}


    function deleteEntry(id) {
        if (confirm("Are you sure you want to delete this entry?")) {
            fetch(`link-building/${id}`, {
                method: "DELETE",
                headers: { "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content") }
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
