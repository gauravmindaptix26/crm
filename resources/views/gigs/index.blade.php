@extends('layouts.dashboard')

@section('title', 'Gigs')

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
    <input type="text" id="searchInput" placeholder="Search..." class="border border-gray-300 rounded-lg px-4 py-2 w-64 focus:ring focus:border-blue-500 shadow-sm">
</div>

<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">Gigs</h2>
        <button id="addGigButton" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add Gig</button>
    </div>

    <div id="successModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-96 text-center">
            <h2 class="text-lg font-bold text-green-600">Success!</h2>
            <p id="successMessage" class="mt-2"></p>
            <button id="closeSuccessButton" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">OK</button>
        </div>
    </div>

    <table class="w-full border-collapse border border-gray-200">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">S/N</th>
                <th class="border px-4 py-2">Website</th>
                <th class="border px-4 py-2">Price</th>
                <th class="border px-4 py-2">Gig Link</th>
                <th class="border px-4 py-2">Gig On</th>
                <th class="border px-4 py-2">Created By</th>
                <th class="border px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody id="gigsTableBody">
            @foreach ($gigs as $gig)
                <tr id="gig-{{ $gig->id }}">
                    <td class="border px-4 py-2 serial-number">{{ $loop->iteration }}</td>
                    <td class="border px-4 py-2">{{ $gig->website }}</td>
                    <td class="border px-4 py-2">{{ $gig->price }}</td>
                    <td class="border px-4 py-2"><a href="{{ $gig->gig_link }}" target="_blank" class="text-blue-500 underline">{{ Str::limit($gig->gig_link, 30) }}</a></td>
                    <td class="border px-4 py-2">{{ $gig->gig_on }}</td>
                    <td class="border px-4 py-2">
                        By: {{ $gig->user?->name ?? 'N/A' }}<br>
                        At: {{ $gig->created_at }}
                    </td>
                    <td class="border px-4 py-2">
                        <button onclick="editGig({{ $gig->id }})" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Edit</button>
                        <button onclick="deleteGig({{ $gig->id }})" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Delete</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $gigs->links() }}
    </div>
</div>

<div id="gigModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-40">
    <div class="bg-white p-6 rounded-lg w-11/12 md:w-1/3 max-h-[80vh] overflow-y-auto relative">
        <button id="closeModalButton" class="absolute top-3 right-3 text-xl text-gray-500 hover:text-black">×</button>
        <h2 class="text-xl font-bold mb-4" id="modalTitle">Add Gig</h2>
        <form id="gigForm">
            @csrf
            <input type="hidden" id="gigId" name="gig_id">

            <div class="mb-4">
                <label for="website" class="block text-sm font-medium text-gray-700">Website <span class="text-red-500">*</span></label>
                <input type="text" id="website" name="website" class="w-full px-3 py-2 border rounded" placeholder="Enter website">
                <div id="website-error" class="text-red-500 text-sm mt-1"></div>
            </div>

            <div class="mb-4">
                <label for="price" class="block text-sm font-medium text-gray-700">Price <span class="text-red-500">*</span></label>
                <input type="number" id="price" name="price" class="w-full px-3 py-2 border rounded" placeholder="Enter price" step="0.01" min="0">
                <div id="price-error" class="text-red-500 text-sm mt-1"></div>
            </div>

            <div class="mb-4">
                <label for="gig_link" class="block text-sm font-medium text-gray-700">Gig Link <span class="text-red-500">*</span></label>
                <input type="url" id="gig_link" name="gig_link" class="w-full px-3 py-2 border rounded" placeholder="Enter gig link">
                <div id="gig_link-error" class="text-red-500 text-sm mt-1"></div>
            </div>

            <div class="mb-4">
                <label for="gig_on" class="block text-sm font-medium text-gray-700">Gig On <span class="text-red-500">*</span></label>
                <select id="gig_on" name="gig_on" class="w-full px-3 py-2 border rounded">
                    <option value="">Select platform</option>
                    <option value="Fiverr">Fiverr</option>
                    <option value="PPH">PPH</option>
                    <option value="Upwork">Upwork</option>
                    <option value="Other">Other</option>
                </select>
                <div id="gig_on-error" class="text-red-500 text-sm mt-1"></div>
            </div>

            <div class="flex justify-end space-x-2">
                <button type="button" id="cancelModalButton" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const addButton = document.getElementById("addGigButton");
    const form = document.getElementById("gigForm");
    const modal = document.getElementById("gigModal");
    const closeModalButton = document.getElementById("closeModalButton");
    const cancelModalButton = document.getElementById("cancelModalButton");
    const closeSuccessButton = document.getElementById("closeSuccessButton");
    const tableBody = document.getElementById("gigsTableBody");

    // Open modal for adding
    addButton.addEventListener("click", function () {
        console.log("Opening modal for add"); // Debug
        openModal(true);
    });

    // Open modal
    function openModal(isAdd = true) {
        modal.classList.remove("hidden");
        modal.classList.add("flex");
        if (isAdd) {
            document.getElementById("modalTitle").innerText = "Add Gig";
            document.getElementById("gigId").value = "";
            form.reset();
        }
        clearErrors();
    }

    // Close modal
    function closeModal() {
        console.log("Closing modal"); // Debug
        modal.classList.add("hidden");
        modal.classList.remove("flex");
        clearErrors();
    }

    // Clear form errors
    function clearErrors() {
        console.log("Clearing errors"); // Debug
        document.querySelectorAll(".error-message").forEach(el => el.remove());
        document.querySelectorAll(".border-red-500").forEach(el => el.classList.remove("border-red-500"));
        document.querySelectorAll("[id$='-error']").forEach(el => el.innerText = "");
    }

    // Show success modal
    function showSuccessModal(message) {
        console.log("Showing success modal:", message); // Debug
        document.getElementById("successMessage").innerText = message;
        document.getElementById("successModal").classList.remove("hidden");
        document.getElementById("successModal").classList.add("flex");
    }

    // Close success modal
    function closeSuccessModal() {
        console.log("Closing success modal"); // Debug
        document.getElementById("successModal").classList.add("hidden");
        document.getElementById("successModal").classList.remove("flex");
    }

    // Bind close events
    closeModalButton.addEventListener("click", closeModal);
    cancelModalButton.addEventListener("click", closeModal);
    closeSuccessButton.addEventListener("click", closeSuccessModal);

    // Append new gig to table
    function appendGig(data) {
        console.log("Appending gig:", data); // Debug
        const row = document.createElement("tr");
        row.id = `gig-${data.id}`;
        row.innerHTML = `
            <td class="border px-4 py-2 serial-number"></td>
            <td class="border px-4 py-2">${data.website || ''}</td>
            <td class="border px-4 py-2">${data.price || ''}</td>
            <td class="border px-4 py-2"><a href="${data.gig_link || ''}" target="_blank" class="text-blue-500 underline">${data.gig_link ? data.gig_link.substring(0, 30) + (data.gig_link.length > 30 ? '...' : '') : ''}</a></td>
            <td class="border px-4 py-2">${data.gig_on || ''}</td>
            <td class="border px-4 py-2">
                By: ${data.creator_name || 'N/A'}<br>
                At: ${data.created_at || ''}
            </td>
            <td class="border px-4 py-2">
                <button onclick="editGig(${data.id})" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Edit</button>
                <button onclick="deleteGig(${data.id})" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Delete</button>
            </td>
        `;
        tableBody.prepend(row);
        updateSerialNumbers();
    }

    // Update existing gig in table
    function updateGig(data) {
        console.log("Updating gig:", data); // Debug
        const row = document.getElementById(`gig-${data.id}`);
        if (row) {
            row.innerHTML = `
                <td class="border px-4 py-2 serial-number"></td>
                <td class="border px-4 py-2">${data.website || ''}</td>
                <td class="border px-4 py-2">${data.price || ''}</td>
                <td class="border px-4 py-2"><a href="${data.gig_link || ''}" target="_blank" class="text-blue-500 underline">${data.gig_link ? data.gig_link.substring(0, 30) + (data.gig_link.length > 30 ? '...' : '') : ''}</a></td>
                <td class="border px-4 py-2">${data.gig_on || ''}</td>
                <td class="border px-4 py-2">
                    By: ${data.creator_name || 'N/A'}<br>
                    At: ${data.created_at || ''}
                </td>
                <td class="border px-4 py-2">
                    <button onclick="editGig(${data.id})" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Edit</button>
                    <button onclick="deleteGig(${data.id})" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Delete</button>
                </td>
            `;
            updateSerialNumbers();
        }
    }

    // Update serial numbers
    function updateSerialNumbers() {
        console.log("Updating serial numbers"); // Debug
        const rows = tableBody.querySelectorAll("tr");
        rows.forEach((row, index) => {
            const serialCell = row.querySelector(".serial-number");
            if (serialCell) {
                serialCell.textContent = index + 1;
            }
        });
    }

    // Form submission
    form.addEventListener("submit", function (e) {
        e.preventDefault();
        console.log("Form submitted"); // Debug

        clearErrors();

        const gigId = document.getElementById("gigId").value;
        const isEdit = !!gigId;
        const url = isEdit ? `gigs/${gigId}` : "gigs";
        const formData = new FormData(this);

        if (isEdit) {
            formData.append("_method", "PUT");
        }

        fetch(url, {
            method: "POST", // Laravel uses POST with _method for PUT
            body: formData,
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                "Accept": "application/json"
            },
        })
        .then(response => {
            console.log("Response status:", response.status); // Debug
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            console.log("Success response:", data); // Debug
            if (data.success) {
                closeModal();
                showSuccessModal(data.message);
                if (isEdit) {
                    updateGig(data.data);
                } else {
                    appendGig(data.data);
                }
            }
        })
        .catch(err => {
            console.error("Error response:", err); // Debug
            if (err.errors) {
                for (const [key, errors] of Object.entries(err.errors)) {
                    const input = document.getElementById(key);
                    if (input) {
                        input.classList.add("border-red-500");
                        document.getElementById(`${key}-error`).innerText = errors[0];
                        document.getElementById(`${key}-error`).classList.add("error-message");
                    } else {
                        form.insertAdjacentHTML("afterbegin", `<div class="error-message text-red-500 text-sm mb-4">${errors[0]}</div>`);
                    }
                }
            } else {
                form.insertAdjacentHTML("afterbegin", `<div class="error-message text-red-500 text-sm mb-4">${err.message || "An error occurred. Please try again."}</div>`);
            }
        });
    });

    // Edit gig
    window.editGig = function (id) {
        console.log("Editing gig:", id); // Debug
        fetch(`gigs/${id}/edit`, {
            method: "GET",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                "Accept": "application/json"
            }
        })
        .then(response => {
            console.log("Edit response status:", response.status); // Debug
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            console.log("Edit data:", data); // Debug
            if (data.success && data.data) {
                document.getElementById("gigId").value = data.data.id;
                document.getElementById("website").value = data.data.website || "";
                document.getElementById("price").value = data.data.price ?? "";
                document.getElementById("gig_link").value = data.data.gig_link || "";
                document.getElementById("gig_on").value = data.data.gig_on || "";
                document.getElementById("modalTitle").innerText = "Edit Gig";
                openModal(false);
            } else {
                console.error("Invalid edit data:", data);
                alert("Error: Could not load gig data.");
            }
        })
        .catch(error => {
            console.error("Error fetching gig:", error);
            alert("Error: Failed to load gig data.");
        });
    };

    // Delete gig
    window.deleteGig = function (id) {
        if (!confirm("Are you sure you want to delete this gig?")) {
            return;
        }
        console.log("Deleting gig:", id); // Debug
        fetch(`gigs/${id}`, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json"
            }
        })
        .then(response => {
            console.log("Delete response status:", response.status); // Debug
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            console.log("Delete response:", data); // Debug
            if (data.success) {
                document.getElementById(`gig-${id}`)?.remove();
                updateSerialNumbers();
                showSuccessModal(data.message);
            } else {
                alert("Error: Could not delete gig.");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Error: Failed to delete gig.");
        });
    };

    // Table filtering
    const searchInput = document.getElementById("searchInput");
    const entriesSelect = document.getElementById("entriesPerPage");

    if (searchInput && entriesSelect && tableBody) {
        function filterTable() {
            const searchText = searchInput.value.toLowerCase();
            const rows = tableBody.getElementsByTagName("tr");
            Array.from(rows).forEach(row => {
                const textContent = row.innerText.toLowerCase();
                row.style.display = textContent.includes(searchText) ? "" : "none";
            });
        }

        function updateEntriesPerPage() {
            const numEntries = parseInt(entriesSelect.value);
            const rows = tableBody.getElementsByTagName("tr");
            Array.from(rows).forEach((row, index) => {
                row.style.display = index < numEntries ? "" : "none";
            });
        }

        searchInput.addEventListener("keyup", filterTable);
        entriesSelect.addEventListener("change", updateEntriesPerPage);
        updateEntriesPerPage();
    }
});
</script>
@endsection