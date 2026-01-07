@extends('layouts.dashboard')

@section('title', 'Manage Links')

@section('content')
<div class="flex space-x-4 mb-4 bg-white shadow-md p-4 rounded-lg">
    <a href="{{ route('project-tasks.index') }}" 
       class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md shadow-md transition">
        📊 Manage Project Task
    </a>
</div>
<div class="flex justify-between items-center mb-4 bg-white shadow-md p-4 rounded-lg">
    <div class="flex items-center space-x-2">
        <label for="entriesPerPage" class="text-sm font-medium text-gray-700">Show</label>
        <select id="entriesPerPage" class="border border-gray-300 rounded px-3 py-1 focus:outline-none focus:ring focus:border-blue-500">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
        <label for="entriesPerPage" class="text-sm font-medium text-gray-700">entries</label>
    </div>

    <input type="text" id="searchInput" placeholder="Search..." 
        class="border border-gray-300 rounded-lg px-4 py-2 w-64 focus:ring focus:border-blue-500 shadow-sm">
</div>

    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between mb-4">
            <h2 class="text-xl font-bold">Manage Links</h2>
            <button onclick="openModal()" class="bg-blue-500 text-white px-4 py-2 rounded">Add Link</button>
        </div>

        <table class="w-full border-collapse border border-gray-200">
    <thead>
        <tr class="bg-gray-100">
            <th class="border px-4 py-2">S/N</th>
            <th class="border px-4 py-2">Link</th>
            <th class="border px-4 py-2">PA</th>
            <th class="border px-4 py-2">DA</th>
            <th class="border px-4 py-2">Added On</th>
            <th class="border px-4 py-2">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($manageLinks as $manageLink)
            <tr id="manageLink-{{ $manageLink->id }}">
                <td class="border px-4 py-2">{{ $loop->iteration }}</td>
                <td class="border px-4 py-2">
                    <a href="{{ $manageLink->url }}" target="_blank" class="text-blue-500 underline">
                        {{ $manageLink->link }}
                    </a>
                </td>
                <td class="border px-4 py-2">{{ $manageLink->pa }}</td>
                <td class="border px-4 py-2">{{ $manageLink->da }}</td>
                <td class="border px-4 py-2">
                    On: {{ $manageLink->created_at->format('d-m-Y') }}<br>
                </td>
                <td class="border px-4 py-2">
                    <button onclick="editManageLink({{ $manageLink->id }})" class="bg-yellow-500 text-white px-3 py-1 rounded">Edit</button>
                    <button onclick="deleteManageLink({{ $manageLink->id }})" class="bg-red-500 text-white px-3 py-1 rounded">Delete</button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>


        <div class="mt-4">
            {{ $manageLinks->links() }}
        </div>
    </div>

   <!-- Modal -->
<div id="manageLinkModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg w-1/3 relative">
        <button onclick="closeModal()" class="absolute top-3 right-3 text-xl text-gray-500 hover:text-black">&times;</button>
        <h2 class="text-xl font-bold mb-4" id="modalTitle">Add Link</h2>
        <form id="manageLinkForm">
            @csrf
            <input type="hidden" id="manageLinkId">

            <div>
                <label for="link" class="block text-sm font-medium text-gray-700">Link (URL)</label>
                <input type="url" id="link" name="link" class="w-full px-3 py-2 border rounded" required>
            </div>

            <div class="mt-3">
    <label for="pa" class="block text-sm font-medium text-gray-700">PA</label>
    <input type="number" id="pa" name="pa" class="w-full px-3 py-2 border rounded" min="1" max="100" >
</div>

<div class="mt-3">
    <label for="da" class="block text-sm font-medium text-gray-700">DA</label>
    <input type="number" id="da" name="da" class="w-full px-3 py-2 border rounded" min="1" max="100" >
</div>


            <!-- <div class="mt-3">
                <label for="project_task_id" class="block text-sm font-medium text-gray-700">Project Task</label>
                <select id="project_task_id" name="project_task_id" class="w-full px-3 py-2 border rounded" required>
                    <option value="">Select Project Task</option>
                    @foreach ($projectTasks as $task)
                        <option value="{{ $task->id }}">{{ $task->title }}</option>
                    @endforeach
                </select>
            </div> -->

            <div class="flex justify-end space-x-2 mt-4">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-500 text-white rounded">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">Save</button>
            </div>
        </form>
    </div>
</div>
<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg w-1/3 text-center relative">
        <button onclick="closeSuccessModal()" class="absolute top-3 right-3 text-xl text-gray-500 hover:text-black">&times;</button>
        <h2 class="text-xl font-bold text-green-600 mb-2">Success!</h2>
        <p id="successMessage">The link has been saved successfully.</p>
        <button onclick="closeSuccessModal()" class="mt-4 px-4 py-2 bg-green-500 text-white rounded">OK</button>
    </div>
</div>

<script>
 function openModal() {
    document.getElementById("manageLinkModal").classList.remove("hidden");
    document.getElementById("modalTitle").innerText = "Add Link";
    document.getElementById("manageLinkId").value = "";
    document.getElementById("link").value = "";
    document.getElementById("pa").value = "";
    document.getElementById("da").value = "";
}

function closeModal() {
    let modal = document.getElementById("manageLinkModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
}
function showSuccessModal(message) {
    document.getElementById("successMessage").innerText = message;
    let modal = document.getElementById("successModal");
    modal.classList.remove("hidden");
    modal.classList.add("flex");

    // Auto-close after 3 seconds
    setTimeout(() => {
        closeSuccessModal();
    }, 3000);
}

function closeSuccessModal() {
    let modal = document.getElementById("successModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
}

document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("manageLinkForm").addEventListener("submit", function (e) {
        e.preventDefault();

        let manageLinkId = document.getElementById("manageLinkId").value;
        let link = document.getElementById("link").value;
        let pa = document.getElementById("pa").value;
        let da = document.getElementById("da").value;
        let projectTaskId = new URLSearchParams(window.location.search).get("project_task_id"); // Get project_task_id from URL

        if (!projectTaskId) {
            alert("Project Task ID is missing in the URL.");
            return;
        }

        let url = manageLinkId ? `manage-links/${manageLinkId}` : `manage-links?project_task_id=${projectTaskId}`;
        let method = manageLinkId ? "PUT" : "POST";

        fetch(url, {
            method: method,
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            },
            body: JSON.stringify({ link, pa, da })
        })
        .then(response => response.json())
        .then(data => {
    if (data.success) {
        closeModal();
        showSuccessModal("The link has been saved successfully."); // Call success modal
        setTimeout(() => location.reload(), 3000); // Refresh after showing success message
    } else {
        alert("Failed to save link.");
    }
})
        .catch(error => console.error("Error:", error));
    });
});

function editManageLink(id) {
    fetch(`manage-links/${id}/edit`, {
        method: "GET",
        headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log("Fetched Data:", data); // Debugging Log

        document.getElementById("manageLinkId").value = data.id;
        document.getElementById("link").value = data.link;
        document.getElementById("pa").value = data.pa;
        document.getElementById("da").value = data.da;
        document.getElementById("modalTitle").innerText = "Edit Link";

        let modal = document.getElementById("manageLinkModal");
        modal.classList.remove("hidden");
        modal.classList.add("flex");
    })
    .catch(error => {
        console.error("Error fetching data:", error);
        alert("Failed to fetch data. Check console for details.");
    });
}

function deleteManageLink(id) {
    if (!confirm("Are you sure you want to delete this link?")) return;

    fetch(`manage-links/${id}`, {
        method: "DELETE",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`manageLink-${id}`).remove();
        } else {
            alert("Failed to delete link.");
        }
    })
    .catch(error => console.error("Error deleting data:", error));
}

function loadManageLinks(projectTaskId) {
    fetch(`manage-links?project_task_id=${projectTaskId}`, {
        method: "GET",
        headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
        }
    })
    .then(response => response.json())
    .then(data => {
        let tbody = document.querySelector("tbody");
        tbody.innerHTML = ""; // Clear existing rows

        data.manageLinks.data.forEach((manageLink, index) => {
            let newRow = `
                <tr id="manageLink-${manageLink.id}">
                    <td class="border px-4 py-2">${index + 1}</td>
                    <td class="border px-4 py-2">${manageLink.link}</td>
                    <td class="border px-4 py-2">${manageLink.pa}</td>
                    <td class="border px-4 py-2">${manageLink.da}</td>
                    <td class="border px-4 py-2">
                        On: ${new Date(manageLink.created_at).toLocaleDateString()}<br>
                        By: ${manageLink.creator ? manageLink.creator.name : 'Unknown'}
                    </td>
                    <td class="border px-4 py-2">
                        <button onclick="editManageLink(${manageLink.id})" class="bg-yellow-500 text-white px-3 py-1 rounded">Edit</button>
                        <button onclick="deleteManageLink(${manageLink.id})" class="bg-red-500 text-white px-3 py-1 rounded">Delete</button>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML("beforeend", newRow);
        });

        document.querySelector(".pagination-links").innerHTML = data.manageLinks.links;
    })
    .catch(error => console.error("Error:", error));
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
