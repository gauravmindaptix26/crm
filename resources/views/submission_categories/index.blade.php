@extends('layouts.dashboard')

@section('title', 'Submission Categories')

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
    <input type="text" id="searchInput" placeholder="Search categories..." 
        class="border border-gray-300 rounded-md px-4 py-2 w-72 focus:ring focus:border-blue-500 shadow-md text-gray-700 transition">
</div>

<div class="bg-white shadow-md rounded-lg p-6">
    <div class="flex justify-between items-center mb-5">
        <h2 class="text-2xl font-semibold text-gray-800">Submission Website Categories</h2>
        <button onclick="openModal('addModal')" 
            class="font-semibold text-white bg-gradient-to-r bg-teal-600 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:bg-teal-600 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2 transition duration-200">
            + Add Category
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse bg-white shadow-md rounded-lg text-left" id="categoriesTable">
            <thead class="bg-slate-50 text-gray-700 font-semibold">
                <tr>
                    <th class="border px-5 py-3">S/N</th>
                    <th class="border px-5 py-3">Name</th>
                    <th class="border px-5 py-3">Slug</th>
                    <th class="border px-5 py-3">Description</th>
                    <th class="border px-5 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categories as $category)
                    <tr id="category-{{ $category->id }}" class="hover:bg-gray-50 transition">
                        <td class="border px-5 py-3 text-gray-700">{{ $categories->firstItem() + $loop->index }}</td>
                        <td class="border px-5 py-3 font-medium text-gray-900">{{ $category->name }}</td>
                        <td class="border px-5 py-3 text-gray-700">{{ $category->slug }}</td>
                        <td class="border px-5 py-3 text-gray-700">{{ Str::limit($category->description, 50) }}</td>
                        <td class="border px-5 py-3 flex justify-center space-x-2">
                            <button onclick="editCategory({{ $category->id }})" 
                                class="text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-700 dark:border-gray-700 px-3 py-1.5 rounded-md shadow hover:bg-yellow-600 transition">
                                Edit
                            </button>
                            <button onclick="deleteCategory(this)" data-id="{{ $category->id }}"
                                class="text-white bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-teal-300 dark:focus:ring-teal-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2">
                                Delete
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination (Aligned Right) -->
    <div class="mt-6 flex justify-end">
        {{ $categories->links() }}
    </div>
</div>

<!-- Add Category Modal -->
<div id="addModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg w-1/3 shadow-lg relative">
        <button onclick="closeModal('addModal')" class="absolute top-3 right-3 text-2xl text-gray-500 hover:text-black">&times;</button>
        <h2 class="text-2xl font-semibold mb-6 text-gray-800">Add Category</h2>
        
        <form id="addCategoryForm" method="POST" action="{{ route('submission_categories.store') }}">
            @csrf
            <div class="mb-4">
                <label for="addName" class="block text-sm font-medium text-gray-700">Category Name</label>
                <input type="text" id="addName" name="name" 
                    class="w-full px-4 py-2 border rounded-md focus:ring focus:border-blue-500 text-gray-900 shadow-sm" required>
                <span id="addNameError" class="text-red-500 text-sm"></span>
            </div>
            <div class="mb-4">
                <label for="addSlug" class="block text-sm font-medium text-gray-700">Slug</label>
                <input type="text" id="addSlug" name="slug" 
                    class="w-full px-4 py-2 border rounded-md focus:ring focus:border-blue-500 text-gray-900 shadow-sm" required>
                <span id="addSlugError" class="text-red-500 text-sm"></span>
            </div>
            <div class="mb-4">
                <label for="addDescription" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea id="addDescription" name="description" 
                    class="w-full px-4 py-2 border rounded-md focus:ring focus:border-blue-500 text-gray-900 shadow-sm"></textarea>
                <span id="addDescriptionError" class="text-red-500 text-sm"></span>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeModal('addModal')" 
                    class="px-4 py-2 bg-gray-500 text-white rounded-md shadow hover:bg-gray-600 transition">
                    Cancel
                </button>
                <button type="submit" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md shadow hover:bg-blue-700 transition">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg w-1/3 shadow-lg relative">
        <button onclick="closeModal('editModal')" class="absolute top-3 right-3 text-2xl text-gray-500 hover:text-black">&times;</button>
        <h2 class="text-2xl font-semibold mb-6 text-gray-800">Edit Category</h2>
        
        <form id="editCategoryForm" method="POST">
            @csrf
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" id="editCategoryId" name="id">
            <div class="mb-4">
                <label for="editName" class="block text-sm font-medium text-gray-700">Category Name</label>
                <input type="text" id="editName" name="name" 
                    class="w-full px-4 py-2 border rounded-md focus:ring focus:border-blue-500 text-gray-900 shadow-sm" required>
                <span id="editNameError" class="text-red-500 text-sm"></span>
            </div>
            <div class="mb-4">
                <label for="editSlug" class="block text-sm font-medium text-gray-700">Slug</label>
                <input type="text" id="editSlug" name="slug" 
                    class="w-full px-4 py-2 border rounded-md focus:ring focus:border-blue-500 text-gray-900 shadow-sm" required>
                <span id="editSlugError" class="text-red-500 text-sm"></span>
            </div>
            <div class="mb-4">
                <label for="editDescription" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea id="editDescription" name="description" 
                    class="w-full px-4 py-2 border rounded-md focus:ring focus:border-blue-500 text-gray-900 shadow-sm"></textarea>
                <span id="editDescriptionError" class="text-red-500 text-sm"></span>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeModal('editModal')" 
                    class="px-4 py-2 bg-gray-500 text-white rounded-md shadow hover:bg-gray-600 transition">
                    Cancel
                </button>
                <button type="submit" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md shadow hover:bg-blue-700 transition">
                    Update
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
function submitForm(formId, url, method) {
    const form = document.getElementById(formId);
    const formData = new FormData(form);
    if (method === 'PUT') {
        formData.append('_method', 'PUT'); // Laravel requires _method for PUT requests
    }

    fetch(url, {
        method: 'POST', // Use POST as Laravel expects it with _method for PUT
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            // Log the raw response text for debugging
            response.text().then(text => {
                console.error('Raw response:', text);
                throw new Error(`HTTP error! Status: ${response.status}, Response: ${text.substring(0, 100)}...`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showSuccessModal(data.success);
            form.reset();
            if (formId === 'editCategoryForm') {
                document.getElementById('editCategoryId').value = '';
            }
            closeModal(formId === 'addCategoryForm' ? 'addModal' : 'editModal');
        } else {
            displayErrors(formId, data.errors || {});
        }
    })
    .catch(error => {
        console.error('Error:', error);
        displayErrors(formId, error.errors || {});
        alert('An error occurred while processing your request: ' + (error.message || 'Unknown error'));
    });
}

function displayErrors(formId, errors) {
    const prefix = formId === 'addCategoryForm' ? 'add' : 'edit';
    document.getElementById(`${prefix}NameError`).innerText = errors.name ? errors.name[0] : '';
    document.getElementById(`${prefix}SlugError`).innerText = errors.slug ? errors.slug[0] : '';
    document.getElementById(`${prefix}DescriptionError`).innerText = errors.description ? errors.description[0] : '';
}

function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
    if (modalId === 'addModal') {
        const form = document.getElementById('addCategoryForm');
        form.reset();
        displayErrors('addCategoryForm', {}); // Clear errors
    }
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function showSuccessModal(message) {
    document.getElementById('successMessage').innerText = message;
    document.getElementById('successModal').classList.remove('hidden');
}

function closeSuccessModal() {
    document.getElementById('successModal').classList.add('hidden');
    location.reload();
}

document.getElementById('addCategoryForm').addEventListener('submit', function (event) {
    event.preventDefault();
    submitForm('addCategoryForm', '{{ route("submission_categories.store") }}', 'POST');
});

document.getElementById('editCategoryForm').addEventListener('submit', function (event) {
    event.preventDefault();
    const categoryId = document.getElementById('editCategoryId').value;
    submitForm('editCategoryForm', '{{ route("submission_categories.update", ":id") }}'.replace(':id', categoryId), 'PUT');
});

function editCategory(id) {
    fetch('{{ route("submission_categories.edit", ":id") }}'.replace(':id', id), {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(res => {
        if (!res.ok) {
            return res.text().then(text => {
                throw new Error(`Failed to fetch category data: HTTP ${res.status}, Response: ${text.substring(0, 100)}...`);
            });
        }
        return res.json();
    })
    .then(data => {
        document.getElementById('editCategoryId').value = data.category.id;
        document.getElementById('editName').value = data.category.name;
        document.getElementById('editSlug').value = data.category.slug;
        document.getElementById('editDescription').value = data.category.description || '';
        displayErrors('editCategoryForm', {}); // Clear errors
        openModal('editModal');
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to load category data: ' + error.message);
    });
}

function deleteCategory(button) {
    const categoryId = button.getAttribute('data-id');
    if (!confirm('Are you sure you want to delete this category?')) {
        return;
    }
    fetch('{{ route("submission_categories.destroy", ":id") }}'.replace(':id', categoryId), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Raw response:', text);
                throw new Error(`Failed to delete category: HTTP ${response.status}, Response: ${text.substring(0, 100)}...`);
            });
        }
        return response.json();
    })
    .then(data => {
        document.getElementById(`category-${categoryId}`).remove();
        showSuccessModal(data.success);
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete category: ' + error.message);
    });
}

document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");
    const entriesSelect = document.getElementById("entriesPerPage");
    const table = document.querySelector("table tbody");
    const rows = table.getElementsByTagName("tr");

    function filterTable() {
        const searchText = searchInput.value.toLowerCase();
        Array.from(rows).forEach(row => {
            const textContent = row.innerText.toLowerCase();
            row.style.display = textContent.includes(searchText) ? "" : "none";
        });
    }

    function updateEntriesPerPage() {
        const numEntries = parseInt(entriesSelect.value);
        Array.from(rows).forEach((row, index) => {
            row.style.display = index < numEntries ? "" : "none";
        });
    }

    searchInput.addEventListener("keyup", filterTable);
    entriesSelect.addEventListener("change", updateEntriesPerPage);
    updateEntriesPerPage();
});
</script>
@endsection
