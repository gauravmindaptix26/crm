@extends('layouts.dashboard')

@section('title', 'Task Management')

@section('content')

<div class="flex justify-between items-center mb-5">
    <h2 class="text-2xl font-semibold text-gray-800">Tasks</h2>
    <div class="flex space-x-3">
        <a href="{{ route('tasks.index') }}" class="bg-blue-500 text-white px-4 py-2 rounded shadow hover:bg-blue-600">One-Time Task Management</a>
        <a href="{{ route('submitted.tasks') }}"
   class="bg-green-500 text-white px-4 py-2 rounded shadow hover:bg-green-600">
   All Submitted Task Report
</a>




        <button onclick="openModal('addModal')" class="bg-blue-600 text-white px-5 py-2 rounded-md shadow hover:bg-blue-700 transition duration-200">
            + Add Task
        </button>
    </div>
</div>

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
    <input type="text" id="searchInput" placeholder="Search tasks..." 
        class="border border-gray-300 rounded-md px-4 py-2 w-72 focus:ring focus:border-blue-500 shadow-md text-gray-700 transition">
</div>

<div class="bg-white shadow-md rounded-lg p-6">
    <div class="flex justify-between items-center mb-5">
        <h2 class="text-2xl font-semibold text-gray-800">Tasks</h2>
        <button onclick="openModal('addModal')" class="bg-blue-600 text-white px-5 py-2 rounded-md shadow hover:bg-blue-700 transition duration-200">
            + Add Task
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse bg-white shadow-md rounded-lg text-left" id="tasksTable">
            <thead class="bg-gray-200 text-gray-700 font-semibold">
            <tr>
        <th class="border px-5 py-3">S/N</th>
        <th class="border px-5 py-3">Task Title</th>
        <th class="border px-5 py-3">Task Info</th>
        
        <th class="border px-5 py-3">Assigned Users</th> <!-- NEW -->
        <th class="border px-5 py-3">Added By</th>

        <th class="border px-5 py-3 text-center">Actions</th>
    </tr>
</thead>
<tbody>
    @foreach ($tasks as $task)
        <tr id="task-{{ $task->id }}" class="hover:bg-gray-100 transition">
            <td class="border px-5 py-3 text-gray-700">{{ $tasks->firstItem() + $loop->index }}</td>
            <td class="border px-5 py-3 font-medium text-gray-900">{{ $task->name }}</td>
            <td class="border px-5 py-3 text-gray-700">
    <ul class="list-disc pl-5">
        @foreach(explode("\n", $task->description) as $point)
            @if(trim($point) !== '')
                <li>{{ $point }}</li>
            @endif
        @endforeach
    </ul>
</td>
            <td class="border px-5 py-3 text-gray-700">
            @foreach($task->assignedUsers as $user)
        <div class="mb-2">
            <strong>{{ $user->name }}</strong><br>
            @php
                $days = json_decode($user->pivot->days, true);
            @endphp
            <span>Days: {{ is_array($days) ? implode(', ', $days) : '-' }}</span>
        </div>
    @endforeach
            </td>
            <td class="border px-5 py-3 text-gray-700">
    {{ $task->createdBy->name ?? '-' }}
    
</td>
</td>
            <td class="border px-5 py-3 flex justify-center space-x-2">
                <button onclick="editTask({{ $task->id }})" 
                    class="bg-yellow-500 text-white px-3 py-1.5 rounded-md shadow hover:bg-yellow-600 transition">
                    Edit
                </button>
                <button onclick="deleteTask(this)" data-id="{{ $task->id }}"
                    class="bg-red-500 text-white px-3 py-1.5 rounded-md shadow hover:bg-red-600 transition">
                    Delete
                </button>
            </td>
        </tr>
    @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6 flex justify-end">
        {{ $tasks->links() }}
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
  <div class="bg-white p-8 rounded-lg w-full max-w-3xl shadow-lg max-h-[90vh] overflow-auto relative">

    <button onclick="closeModal('addModal')" class="absolute top-3 right-3 text-3xl text-gray-500 hover:text-black transition">&times;</button>

    <h2 class="text-2xl font-semibold mb-6 text-gray-800" id="modalTitle">Add Task</h2>

    <form id="taskForm" method="POST" class="flex flex-col space-y-6">
      @csrf
      <input type="hidden" id="taskId" name="taskId">

      <div>
        <label for="title" class="block mb-1 text-sm font-medium text-gray-700">Task Name</label>
        <input type="text" id="name" name="name"
          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring focus:border-blue-500 text-gray-900 shadow-sm"
          autocomplete="off">
        <span id="titleError" class="text-red-500 text-sm"></span>
      </div>

      <div>
        <label for="description" class="block mb-1 text-sm font-medium text-gray-700">Description</label>
        <textarea id="description" name="description"
          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring focus:border-blue-500 text-gray-900 shadow-sm"
          rows="4"></textarea>
        <span id="descriptionError" class="text-red-500 text-sm"></span>
      </div>

      <div class="flex flex-col space-y-2 max-h-[250px] overflow-auto border border-gray-300 rounded-md p-4">
        <div class="flex justify-between items-center mb-2">
          <label for="usersInput" class="font-medium text-gray-700">Add Employees</label>
          <input type="text" id="usersInput" name="userFilter" placeholder="Filter employees..."
            class="px-2 py-1 border border-gray-300 rounded-md focus:ring focus:border-blue-500 text-gray-700 w-48"
            onkeyup="filterUsers()">
        </div>

        <table id="users" class="w-full text-sm text-left text-gray-700">
          <thead class="bg-gray-100 sticky top-0">
            <tr>
              <th class="px-3 py-2">Name</th>
              <th class="px-3 py-2">Days</th>
            </tr>
          </thead>
          <tbody>
            @if($users)
              @foreach($users as $user)
              <tr>
                <td class="px-3 py-2 align-middle">
                  <label class="inline-flex items-center cursor-pointer" for="user_{{ $user->id }}">
                    <input class="mr-2 rounded" id="user_{{ $user->id }}" type="checkbox" value="{{ $user->id }}" name="user[{{ $user->id }}]">
                    {{ $user->name }}
                  </label>
                </td>
                <td class="px-3 py-2">
                  <div class="flex flex-wrap gap-1">
                    @foreach(['Mon', 'Tues', 'Wed', 'Thurs', 'Fri', 'Sat'] as $day)
                      <label class="inline-flex items-center cursor-pointer">
                        <input class="rounded" type="checkbox" name="user[{{ $user->id }}][{{ $day }}]" value="1">
                        <span class="ml-1 text-xs">{{ $day }}</span>
                      </label>
                    @endforeach
                  </div>
                </td>
              </tr>
              @endforeach
            @endif
          </tbody>
        </table>
      </div>


      <div class="flex justify-end space-x-3">
        <button type="button" onclick="closeModal('addModal')" 
          class="px-4 py-2 bg-gray-500 text-white rounded-md shadow hover:bg-gray-600 transition">
          Cancel
        </button>
        <button type="submit" onclick="submitTaskForm()"
          class="px-4 py-2 bg-blue-600 text-white rounded-md shadow hover:bg-blue-700 transition">
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
document.getElementById('taskForm').addEventListener('submit', function (event) {
    event.preventDefault();

    let formData = new FormData(this);
    let taskId = document.getElementById('taskId').value;
    let url = taskId ? `tasks/${taskId}` : 'tasks';

    if (taskId) {
        formData.append('_method', 'PUT');
    }

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
        if (data.success) {
            // Optionally: update or add row in table
            // if (taskId) updateTaskRow(data.task);
            // else addTaskRow(data.task);

            // Clear form
            document.getElementById('taskForm').reset();

            // Close modal
            closeModal('addModal');

            // Show success message
            document.getElementById('successMessage').innerText = 'Task saved successfully!';
            document.getElementById('successModal').classList.remove('hidden');
        } else {
            // Handle unexpected response
            alert('Something went wrong. Please try again.');
        }
    })
    .catch(error => {
        console.error(error);
        alert('An error occurred while saving the task.');
    });
});

function closeSuccessModal() {
    document.getElementById('successModal').classList.add('hidden');
}
function submitTaskForm() {
    const taskId = document.getElementById('taskId').value;
    const form = document.getElementById('taskForm');
    const formData = new FormData(form);
    const users = {};
    
    // Extract user-day checkboxes
    form.querySelectorAll('input[type="checkbox"]').forEach(input => {
        const match = input.name.match(/^user\[(\d+)\](?:\[(\w+)\])?$/); // Matches both user[1] and user[1][Mon]
        if (match && input.checked) {
            const userId = match[1];
            const day = match[2]; // might be undefined for just user checkbox

            if (!users[userId]) users[userId] = [];

            // Only push days, not the main user checkbox
            if (day) users[userId].push(day);
        }
    });
}

function clearForm() {
    document.getElementById('taskForm').reset();
    document.getElementById('taskId').value = '';
    document.getElementById('titleError').innerText = '';
    document.getElementById('descriptionError').innerText = '';
    document.getElementById('modalTitle').innerText = 'Add Task';
}


function addTaskRow(task) {
    let tbody = document.querySelector('#tasksTable tbody');
    let rowCount = tbody.rows.length;
    let sn = rowCount + 1; // Simple serial number increment

    // Create new row HTML
    let tr = document.createElement('tr');
    tr.id = `task-${task.id}`;
    tr.className = 'hover:bg-gray-100 transition';
    tr.innerHTML = `
        <td class="border px-5 py-3 text-gray-700">${sn}</td>
        <td class="border px-5 py-3 font-medium text-gray-900">${escapeHtml(task.name)}</td>
        <td class="border px-5 py-3 text-gray-700">${escapeHtml(truncate(task.description, 50))}</td>
        <td class="border px-5 py-3 text-gray-700">${escapeHtml(task.status)}</td>
        <td class="border px-5 py-3 flex justify-center space-x-2">
            <button onclick="editTask(${task.id})" class="bg-yellow-500 text-white px-3 py-1.5 rounded-md shadow hover:bg-yellow-600 transition">
                Edit
            </button>
            <button onclick="deleteTask(this)" data-id="${task.id}" class="bg-red-500 text-white px-3 py-1.5 rounded-md shadow hover:bg-red-600 transition">
                Delete
            </button>
        </td>
    `;
    tbody.prepend(tr); // add on top - or use appendChild to add at bottom

    // Optionally update the serial numbers of existing rows if needed
}

function editTask(id) {
    fetch(`tasks/${id}/edit-json`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const task = data.task;
                document.getElementById('taskId').value = task.id;
                document.getElementById('name').value = task.name;
                document.getElementById('description').value = task.description;

                // Reset all checkboxes
                document.querySelectorAll('#users input[type="checkbox"]').forEach(checkbox => {
                    checkbox.checked = false;
                });

                // Loop through assigned users and check relevant boxes
                if (task.users) {
                    task.users.forEach(user => {
                        const userCheckbox = document.getElementById(`user_${user.id}`);
                        if (userCheckbox) userCheckbox.checked = true;

                        if (user.days) {
                            user.days.forEach(day => {
                                const dayCheckbox = document.querySelector(`input[name="user[${user.id}][${day}]"]`);
                                if (dayCheckbox) dayCheckbox.checked = true;
                            });
                        }
                    });
                }

                document.getElementById('modalTitle').innerText = 'Edit Task';
                openModal('addModal');
            } else {
                alert('Failed to load task.');
            }
        })
        .catch(error => {
            console.error(error);
            alert('An error occurred while fetching task details.');
        });
}

function updateTaskRow(task) {
    let tr = document.getElementById(`task-${task.id}`);
    if (!tr) return;
    tr.querySelector('td:nth-child(2)').innerText = task.name;
    tr.querySelector('td:nth-child(3)').innerText = truncate(task.description, 50);
    tr.querySelector('td:nth-child(4)').innerText = task.status;
}

function truncate(str, n) {
    return (str.length > n) ? str.substr(0, n - 1) + '…' : str;
}

function escapeHtml(text) {
    let map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}


function displayErrors(errors) {
    document.getElementById('titleError').innerText = errors.title ? errors.title[0] : '';
    document.getElementById('descriptionError').innerText = errors.description ? errors.description[0] : '';
    document.getElementById('statusError').innerText = errors.status ? errors.status[0] : '';
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




function deleteTask(button) {
    let taskId = button.getAttribute('data-id');

    if (!confirm('Are you sure you want to delete this task?')) {
        return;
    }

    fetch(`tasks/${taskId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(() => {
        document.getElementById(`task-${taskId}`).remove();
        alert('Task deleted successfully!');
    });
}

document.addEventListener("DOMContentLoaded", function () {
    let searchInput = document.getElementById("searchInput");
    let entriesSelect = document.getElementById("entriesPerPage");
    let table = document.querySelector("table tbody");
    let rows = table.getElementsByTagName("tr");

    function filterTable() {
        let searchText = searchInput.value.toLowerCase();
        Array.from(rows).forEach(row => {
            let textContent = row.innerText.toLowerCase();
            row.style.display = textContent.includes(searchText) ? "" : "none";
        });
    }

    function updateEntriesPerPage() {
        let numEntries = parseInt(entriesSelect.value);
        Array.from(rows).forEach((row, index) => {
            row.style.display = index < numEntries ? "" : "none";
        });
    }

    searchInput.addEventListener("keyup", filterTable);
    entriesSelect.addEventListener("change", updateEntriesPerPage);

    updateEntriesPerPage();
});
function filterUsers() {
    const input = document.getElementById('usersInput');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('users');
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) { // skip header row
      const td = rows[i].getElementsByTagName('td')[0];
      if (td) {
        const text = td.textContent || td.innerText;
        rows[i].style.display = text.toLowerCase().includes(filter) ? '' : 'none';
      }
    }
  }
</script>
@endsection
