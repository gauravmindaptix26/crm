@extends('layouts.dashboard')

@section('title', 'Project Categories')

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
            <h2 class="text-xl font-bold">Project Categories</h2>
            <button onclick="openModal('addModal')" class="bg-blue-500 text-white px-4 py-2 rounded">Add Category</button>
        </div>
        <table class="w-full border-collapse border border-gray-200" id="categoriesTable">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-4 py-2">S/N</th>
                    <th class="border px-4 py-2">Category Name</th>
                    <th class="border px-4 py-2">Parent Category</th>
                    <th class="border px-4 py-2">Added By</th>

                    <th class="border px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categories as $category)
                    <tr id="category-{{ $category->id }}">
                        <td class="border px-4 py-2">{{ $categories->firstItem() + $loop->index }}</td>
                        <td class="border px-4 py-2">{{ $category->name }}</td>
                        <td class="border px-4 py-2">
    {{ $category->parent ? $category->parent->name : $category->name }}
</td>                        <td class="border px-4 py-2">On: {{ $category->created_at->format('d-m-Y') }}<br>
                        By: {{ $category->creator->name ?? 'Unknown' }}</td>
                        <td class="border px-4 py-2">
                            <button onclick="editCategory({{ $category->id }})" class="bg-yellow-500 text-white px-3 py-1 rounded">Edit</button>
                            <button onclick="deleteCategory({{ $category->id }})" class="bg-red-500 text-white px-3 py-1 rounded">Delete</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $categories->links() }}
        </div>
    </div>

    <!-- Modal -->
    <div id="addModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg w-1/3 relative">
            <button onclick="closeModal('addModal')" class="absolute top-3 right-3 text-xl text-gray-500 hover:text-black">&times;</button>
            <h2 class="text-xl font-bold mb-4" id="modalTitle">Add Category</h2>
            <form id="categoryForm">
                @csrf
                <input type="hidden" id="categoryId">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Category Name</label>
                    <input type="text" id="name" name="name" class="w-full px-3 py-2 border rounded">
                    <span id="nameError" class="text-red-500 text-sm"></span>
                </div>
                <div class="mt-3">
                    <label for="parent_id" class="block text-sm font-medium text-gray-700">Parent Category</label>
                    <select id="parent_id" name="parent_id" class="w-full px-3 py-2 border rounded">
                        <option value="">None</option>
                        @foreach($allCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
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
            document.getElementById('modalTitle').innerText = 'Add Category';
            document.getElementById('categoryId').value = '';
            document.getElementById('name').value = '';
            document.getElementById('parent_id').value = '';
            document.getElementById('nameError').innerHTML = '';
        }

        function editCategory(id) {
            fetch(`project-directories/${id}/edit`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('modalTitle').innerText = 'Edit Category';
                    document.getElementById('categoryId').value = data.id;
                    document.getElementById('name').value = data.name;
                    document.getElementById('parent_id').value = data.parent_id || '';
                    openModal('addModal');
                })
                .catch(error => console.error('Error:', error));
        }

        document.getElementById('categoryForm').addEventListener('submit', function (e) {
            e.preventDefault();
            let id = document.getElementById('categoryId').value;
            let url = id ? `project-directories/${id}` : 'project-directories';
            let method = id ? 'PUT' : 'POST';
            
            let formData = {
                name: document.getElementById('name').value,
                parent_id: document.getElementById('parent_id').value
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
            let nameError = document.getElementById('nameError');
            nameError.innerHTML = errors.name ? `<span class="text-red-500 text-sm">${errors.name[0]}</span>` : '';
        }

        function deleteCategory(id) {
            if (!confirm('Are you sure?')) return;
            fetch(`project-directories/${id}`, {
                method: 'DELETE',
                headers: { 
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
            })
            .then(response => response.json())
            .then(data => {
                alert(data.success);
                document.getElementById(`category-${id}`).remove();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete category. Please try again.');
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
