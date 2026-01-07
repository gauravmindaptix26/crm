@extends('layouts.dashboard')

@section('title', 'Task Phases')

@section('content')
<div class="flex space-x-4 mb-4 bg-white shadow-md p-4 rounded-lg">
    <a href="{{ route('project-tasks.index') }}" 
       class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md shadow-md transition">
        📊 Manage Project Task
    </a>
    <a href="{{ route('countries.index') }}" 
       class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md shadow-md transition">
        📊 Manage Countries
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
            <h2 class="text-xl font-bold">Task Phases</h2>
            <button onclick="openModal('addModal')" class="bg-blue-500 text-white px-4 py-2 rounded">Add Task Phase</button>
        </div>
        <table class="w-full border-collapse border border-gray-200" id="taskPhasesTable">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-4 py-2">S/N</th>
                    <th class="border px-4 py-2">Title</th>
                    <th class="border px-4 py-2">Description</th>
                    <th class="border px-4 py-2">Created By</th>
                    <th class="border px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($taskPhases as $taskPhase)
                    <tr id="taskPhase-{{ $taskPhase->id }}">
                        <td class="border px-4 py-2">{{ $taskPhases->firstItem() + $loop->index }}</td>
                        <td class="border px-4 py-2">{{ $taskPhase->title }}</td>
                        <td class="border px-4 py-2">{{ $taskPhase->description }}</td>
                        <td class="border px-4 py-2">{{ $taskPhase->creator->name ?? 'Unknown' }}</td>
                        <td class="border px-4 py-2">
                            <button onclick="editTaskPhase({{ $taskPhase->id }})" class="bg-yellow-500 text-white px-3 py-1 rounded">Edit</button>
                            <button onclick="deleteTaskPhase({{ $taskPhase->id }})" class="bg-red-500 text-white px-3 py-1 rounded">Delete</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $taskPhases->links() }}
        </div>
    </div>

    <!-- Modal -->
    <div id="addModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg w-1/3 relative">
            <button onclick="closeModal('addModal')" class="absolute top-3 right-3 text-xl text-gray-500 hover:text-black">&times;</button>
            <h2 class="text-xl font-bold mb-4" id="modalTitle">Add Task Phase</h2>
            <form id="taskPhaseForm">
                @csrf
                <input type="hidden" id="taskPhaseId">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" id="title" name="title" class="w-full px-3 py-2 border rounded">
                    <span id="titleError" class="text-red-500 text-sm"></span>
                </div>
                <div class="mt-3">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="description" name="description" class="w-full px-3 py-2 border rounded"></textarea>
                    <span id="descriptionError" class="text-red-500 text-sm"></span>
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
            resetForm();
        }

        function resetForm() {
            document.getElementById('modalTitle').innerText = 'Add Task Phase';
            document.getElementById('taskPhaseId').value = '';
            document.getElementById('title').value = '';
            document.getElementById('description').value = '';
            document.getElementById('titleError').innerHTML = '';
            document.getElementById('descriptionError').innerHTML = '';
        }

        function editTaskPhase(id) {
            fetch(`task-phases/${id}/edit`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('modalTitle').innerText = 'Edit Task Phase';
                    document.getElementById('taskPhaseId').value = data.id;
                    document.getElementById('title').value = data.title;
                    document.getElementById('description').value = data.description;
                    openModal('addModal');
                })
                .catch(error => console.error('Error:', error));
        }

        document.getElementById('taskPhaseForm').addEventListener('submit', function (e) {
            e.preventDefault();
            let id = document.getElementById('taskPhaseId').value;
            let url = id ? `task-phases/${id}` : 'task-phases';
            let method = id ? 'PUT' : 'POST';
            
            let formData = {
                title: document.getElementById('title').value,
                description: document.getElementById('description').value
            };

            fetch(url, {
                method: method,
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                },
                body: JSON.stringify(formData),
            })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(({ status, body }) => {
                if (status === 422) {
                    showValidationErrors(body.errors);
                } else if (status === 200 || status === 201) {
                    alert(body.success);
                    closeModal('addModal');
                    location.reload();
                } else {
                    alert('An unexpected error occurred.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Something went wrong. Please try again.');
            });
        });

        function showValidationErrors(errors) {
            document.getElementById('titleError').innerHTML = errors.title ? `<span class="text-red-500 text-sm">${errors.title[0]}</span>` : '';
            document.getElementById('descriptionError').innerHTML = errors.description ? `<span class="text-red-500 text-sm">${errors.description[0]}</span>` : '';
        }

        function deleteTaskPhase(id) {
            if (!confirm('Are you sure?')) return;
            fetch(`task-phases/${id}`, {
                method: 'DELETE',
                headers: { 
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
            })
            .then(response => response.json())
            .then(data => {
                alert(data.success);
                document.getElementById(`taskPhase-${id}`).remove();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete task phase. Please try again.');
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
