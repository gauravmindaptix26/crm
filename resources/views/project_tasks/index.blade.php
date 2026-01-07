@extends('layouts.dashboard')

@section('title', 'Project Tasks')

@section('content')

<!-- Top Navigation Buttons -->
<div class="flex space-x-4 mb-4 bg-white shadow-md p-4 rounded-lg">
    <a href="{{ route('task-phases.index') }}" 
       class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md shadow-md transition">
        📊 Task Phase
    </a>

    <a href="{{ route('countries.index') }}" 
       class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-md shadow-md transition">
        🌍 Manage Countries
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

<div id="successMessage" class="hidden fixed top-5 right-5 bg-green-500 text-white px-6 py-3 rounded shadow-lg">
    Project Task added successfully!
</div>

<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-semibold text-gray-700">Project Tasks</h2>
        <button onclick="openModal('addEditModal')" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg shadow-md">
            + Add Project Task
        </button>
    </div>

    <table class="w-full border-collapse border border-gray-300 shadow-md" id="projectTasksTable">
        <thead>
            <tr class="bg-gray-200 text-gray-700">
                <th class="border px-4 py-3">S/N</th>
                <!-- <th class="border px-4 py-3">Country</th>
                <th class="border px-4 py-3">Project Phase</th> -->
                <th class="border px-4 py-3">Title</th>
                <th class="border px-4 py-3">Order Number</th>
                <th class="border px-4 py-3">Added On</th>
                <th class="border px-4 py-3">Added By</th>

                <th class="border px-4 py-3">Actions</th>
                <th class="border px-4 py-3">Manage Links</th>

            </tr>
        </thead>
        <tbody class="bg-white">
            @foreach ($projectTasks as $projectTask)
                <tr id="projectTask-{{ $projectTask->id }}" class="hover:bg-gray-100 transition">
                    <td class="border px-4 py-3">{{ $projectTasks->firstItem() + $loop->index }}</td>
                    <!-- <td class="border px-4 py-3">{{ $projectTask->country->name ?? 'N/A' }}</td>
                    <td class="border px-4 py-3">{{ $projectTask->phase->title ?? 'N/A' }}</td> -->
                    <td class="border px-4 py-3">{{ $projectTask->title }}</td>
                    <td class="border px-4 py-3">{{ $projectTask->order_number }}</td>
                    <td class="border px-4 py-3">{{ $projectTask->created_at->format('d-m-Y') }}</td>
                    <td class="border px-4 py-3">{{ $projectTask->createdBy->name ?? 'N/A' }}</td>
                    <td class="border px-4 py-3 flex space-x-2">
                        <button onclick="editProjectTask({{ $projectTask->id }})" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-lg shadow-md">Edit</button>
                        <button onclick="deleteProjectTask({{ $projectTask->id }})" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg shadow-md">Delete</button>
                        <td class="border px-4 py-3 text-center whitespace-nowrap">
    <a href="{{ route('manage-links.index', ['project_task_id' => $projectTask->id]) }}" class="bg-green-500 text-white px-4 py-2 rounded">
        Manage Links
    </a>
</td>



                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $projectTasks->links() }}
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="addEditModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg w-full max-w-2xl shadow-lg relative">
        <button onclick="closeModal('addEditModal')" class="absolute top-3 right-3 text-2xl text-gray-500 hover:text-black">&times;</button>
        <h2 class="text-xl font-semibold mb-4" id="modalTitle">Add Project Task</h2>
        <form id="projectTaskForm">
            @csrf
            <input type="hidden" id="projectTaskId">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="country_id" class="block text-sm font-medium text-gray-700">Country</label>
                    <select id="country_id" name="country_id" class="w-full px-3 py-2 border border-gray-300 rounded focus:ring focus:border-blue-500">
                    <option value="">Select Country</option> <!-- Optional: Default placeholder -->
                    @foreach ($countries as $country)
                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="task_phase_id" class="block text-sm font-medium text-gray-700">Project Phase</label>
                    <select id="task_phase_id" name="task_phase_id" class="w-full px-3 py-2 border border-gray-300 rounded focus:ring focus:border-blue-500">
                        @foreach ($projectPhases as $phase)
                            <option value="{{ $phase->id }}">{{ $phase->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" id="title" name="title" class="w-full px-3 py-2 border border-gray-300 rounded focus:ring focus:border-blue-500">
            </div>
             
            <div class="mt-4">
    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
    <textarea id="description" name="description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded focus:ring focus:border-blue-500"></textarea>
</div>







            <div class="mt-4 flex space-x-2">
                <div class="w-1/2">
                    <label class="block text-sm font-medium text-gray-700">Order Number</label>
                    <input type="number" id="order_number" name="order_number" class="w-full px-3 py-2 border border-gray-300 rounded">
                </div>
                <div class="w-1/2">
                    <label class="block text-sm font-medium text-gray-700">Video Link</label>
                    <input type="url" id="video_link" name="video_link" class="w-full px-3 py-2 border border-gray-300 rounded">
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700">Tool Link</label>
                <input type="url" id="tool_link" name="tool_link" class="w-full px-3 py-2 border border-gray-300 rounded">
            </div>

            <div class="mt-4">
    <label class="block text-sm font-medium text-gray-700">Attachments</label>
    
    <!-- Show existing images -->
    <div id="existingAttachments" class="flex flex-wrap gap-2">
        <!-- Images will be appended here dynamically -->
    </div>

    <input type="file" id="attachments" name="attachments[]" multiple class="w-full px-3 py-2 border border-gray-300 rounded">
</div>



            <div class="flex justify-end space-x-2 mt-4">
                <button type="button" onclick="closeModal('addEditModal')" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Save</button>
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
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }

    function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
    resetForm();
}

    function resetForm() {
        document.getElementById('modalTitle').innerText = 'Add Project Task';
        document.getElementById('projectTaskId').value = '';
        document.getElementById('title').value = '';
        document.getElementById('order_number').value = '';
        document.querySelectorAll(".text-red-500").forEach(el => el.innerText = "");
    }
// Function to close success modal and reload the page
function closeSuccessModal() {
    document.getElementById("successModal").classList.add("hidden");
    location.reload(); // Refresh page to update listing
}
function deleteAttachment(attachmentId) {
    if (!confirm('Are you sure you want to delete this attachment?')) return;

    fetch(`project-tasks/${attachmentId}?type=deleteAttachment`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`attachment-${attachmentId}`).remove();
            alert(data.message);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

$(document).on("click", ".manage-links-btn", function () {
    let projectTaskId = $(this).data("task-id");

    $.ajax({
        url: `/manage-links?project_task_id=${projectTaskId}`,
        type: "GET",
        success: function (data) {
            let linksHtml = "";
            data.forEach(link => {
                linksHtml += `<tr>
                    <td>${link.link}</td>
                    <td>${link.pa}</td>
                    <td>${link.da}</td>
                    <td>
                        <button class="btn btn-sm btn-warning edit-link" data-id="${link.id}">Edit</button>
                        <button class="btn btn-sm btn-danger delete-link" data-id="${link.id}">Delete</button>
                    </td>
                </tr>`;
            });

            $("#manageLinksTable tbody").html(linksHtml);
            $("#manageLinksModal").modal("show");
            $("#projectTaskId").val(projectTaskId);
        }
    });
});


function editProjectTask(id) {
    fetch(`project-tasks/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            console.log("Fetched Data:", data);

            document.getElementById("modalTitle").innerText = "Edit Project Task";
            document.getElementById("projectTaskId").value = data.id;
            document.getElementById("title").value = data.title;
            document.getElementById("order_number").value = data.order_number;
            document.getElementById("video_link").value = data.video_link;
            document.getElementById("tool_link").value = data.tool_link;

            // Set the pre-selected country
            let countrySelect = document.getElementById("country_id");
            if (countrySelect) {
                for (let i = 0; i < countrySelect.options.length; i++) {
                    if (countrySelect.options[i].value == data.country_id) {
                        countrySelect.options[i].selected = true;
                        break;
                    }
                }
            }

            // Handle attachments display
            let attachmentsDiv = document.getElementById("existingAttachments");
            attachmentsDiv.innerHTML = ""; // Clear previous attachments

            if (data.attachments && data.attachments.length > 0) {
                data.attachments.forEach(attachment => {
                    let wrapper = document.createElement("div");
                    wrapper.classList.add("flex", "items-center", "space-x-2");

                    let img = document.createElement("img");
                    img.src = `/storage/${attachment.file_path}`;
                    img.alt = "Attachment";
                    img.classList.add("w-20", "h-20", "object-cover", "border", "rounded");

                    let removeBtn = document.createElement("button");
                    removeBtn.innerText = "❌";
                    removeBtn.classList.add("text-red-500", "text-xs", "ml-1");
                    removeBtn.onclick = function () {
                        removeAttachment(attachment.id, wrapper);
                    };

                    wrapper.appendChild(img);
                    wrapper.appendChild(removeBtn);
                    attachmentsDiv.appendChild(wrapper);
                });
            }

            openModal("addEditModal");
        })
        .catch(error => console.error("Error fetching project task:", error));
}



function removeAttachment(attachmentId, wrapperElement) {
    if (!confirm("Are you sure you want to delete this attachment?")) return;

    fetch(`/project-task-attachments/${attachmentId}`, {
        method: "DELETE",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
            "Content-Type": "application/json",
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the attachment from the UI
            wrapperElement.remove();
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => console.error("Error deleting attachment:", error));
}



document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("projectTaskForm").addEventListener("submit", function (e) {
        e.preventDefault();

        let taskId = document.getElementById("projectTaskId").value;
        let formData = new FormData(this);
        let url = taskId ? `project-tasks/${taskId}` : "project-tasks";
        let method = taskId ? "POST" : "POST";

        if (taskId) {
            formData.append("_method", "PUT");
        }

        fetch(url, {
            method: method,
            body: formData,
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal('addEditModal'); // Close the form modal
                showSuccessModal(data.message); // Show success message modal
            }
        })
        .catch(error => console.error("Error:", error));
    });
});
// Function to show success modal
function showSuccessModal(message) {
    document.getElementById("successMessage").innerText = message;
    document.getElementById("successModal").classList.remove("hidden");
}

function deleteProjectTask(id) {
    if (!confirm("Are you sure you want to delete this task?")) {
        return;
    }

    fetch(`project-tasks/${id}`, {
        method: "DELETE",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
            "Content-Type": "application/json",
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`projectTask-${id}`).remove();
            alert("Project Task deleted successfully!");
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => console.error("Error deleting project task:", error));
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
function removeAttachment(attachmentId, wrapperElement) {
    if (!confirm("Are you sure you want to delete this attachment?")) return;

    fetch(`/attachments/${attachmentId}`, {
        method: "DELETE",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
            "Content-Type": "application/json",
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            wrapperElement.remove(); // Remove the entire div (image + delete button)
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => console.error("Error deleting attachment:", error));
}

</script>
@endsection
