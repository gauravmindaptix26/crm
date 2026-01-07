
@extends('layouts.dashboard')

@section('title', 'Submission Sites')

@section('content')
<div class="bg-white shadow-md rounded-lg p-6 mb-6 flex justify-between items-center">
    <div class="flex items-center space-x-3">
        <label for="entriesPerPage" class="text-sm font-medium text-gray-600">Show</label>
        <select id="entriesPerPage" class="border border-gray-300 rounded-md px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
            <option value="10">10</option>
            <option value="25" selected>25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
        <span class="text-sm font-medium text-gray-600">entries</span>
    </div>
    <input type="text" id="searchInput" placeholder="Search sites..." 
        class="border border-gray-300 rounded-md px-4 py-2 w-72 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm text-gray-700 transition">
</div>

<div class="bg-white shadow-md rounded-lg p-6">
    <div class="flex justify-between items-center mb-5">
        <h2 class="text-2xl font-semibold text-gray-800">Submission Sites</h2>
        <button onclick="openAddModal()" 
            class="font-semibold text-white bg-teal-600 hover:bg-teal-700 focus:ring-4 focus:ring-teal-300 rounded-lg text-sm px-5 py-2.5 transition duration-200">
            + Add Submission Site
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse bg-white shadow-sm rounded-lg text-left" id="sitesTable">
            <thead class="bg-gray-100 text-gray-700 font-semibold">
                <tr>
                    <th class="border px-4 py-3">S/N</th>
                    <th class="border px-4 py-3">Website</th>
                    <th class="border px-4 py-3">Category</th>
                    <th class="border px-4 py-3">Sub-Category</th>
                    <th class="border px-4 py-3">Country</th>
                    <th class="border px-4 py-3">Moz DA</th>
                    <th class="border px-4 py-3">Traffic</th>
                    <th class="border px-4 py-3">Spam Score</th>
                    <th class="border px-4 py-3">Submission Type</th>
                    <th class="border px-4 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sites as $site)
                    <tr id="site-{{ $site->id }}" class="hover:bg-gray-50 transition">
                        <td class="border px-4 py-3 text-gray-600">{{ $sites->firstItem() + $loop->index }}</td>
                        <td class="border px-4 py-3 font-medium text-gray-900">{{ $site->website_name }}</td>
                        <td class="border px-4 py-3 text-gray-600">{{ $site->category()->first()->name ?? 'N/A' }}</td>
                        <td class="border px-4 py-3 text-gray-600">{{ $site->category ?? 'N/A' }}</td>
                        <td class="border px-4 py-3 text-gray-600">{{ $site->country ?? 'N/A' }}</td>
                        <td class="border px-4 py-3 text-gray-600">{{ $site->moz_da ?? 'N/A' }}</td>
                        <td class="border px-4 py-3 text-gray-600">{{ $site->traffic ?? 'N/A' }}</td>
                        <td class="border px-4 py-3 text-gray-600">{{ $site->spam_score ?? 'N/A' }}</td>
                        <td class="border px-4 py-3 text-gray-600">{{ $site->submission_type ?? 'N/A' }}</td>
                        <td class="border px-4 py-3 flex justify-center space-x-2">
                            <button onclick="editSite({{ $site->id }})" 
                                class="text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 rounded-lg text-sm px-4 py-2 transition">
                                Edit
                            </button>
                            <button onclick="deleteSite(this)" data-id="{{ $site->id }}"
                                class="text-white bg-teal-600 hover:bg-teal-700 focus:ring-4 focus:ring-teal-300 rounded-lg text-sm px-4 py-2 transition">
                                Delete
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6 flex justify-end">
        {{ $sites->links() }}
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="addModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg w-full max-w-2xl max-h-[80vh] overflow-y-auto shadow-xl">
        <div class="sticky top-0 bg-white p-6 border-b border-gray-200 z-10">
            <h2 class="text-xl font-semibold text-gray-800" id="modalTitle">Add Submission Site</h2>
            <button onclick="closeModal('addModal')" class="absolute top-4 right-4 text-2xl text-gray-500 hover:text-gray-700">&times;</button>
        </div>
        <form id="siteForm" method="POST" class="p-6">
            @csrf
            <input type="hidden" id="siteId" name="id">
            <input type="hidden" id="method" name="_method" value="">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select id="category_id" name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 shadow-sm" required>
                        <option value="">Select Category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <span id="categoryIdError" class="text-red-500 text-xs mt-1"></span>
                </div>
                <div class="mb-4">
                    <label for="website_name" class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                    <input type="text" id="website_name" name="website_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 shadow-sm" required>
                    <span id="websiteNameError" class="text-red-500 text-xs mt-1"></span>
                </div>
                <div class="mb-4">
                    <label for="register_url" class="block text-sm font-medium text-gray-700 mb-1">Register Page</label>
                    <input type="url" id="register_url" name="register_url" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 shadow-sm">
                    <span id="registerUrlError" class="text-red-500 text-xs mt-1"></span>
                </div>
                <div class="mb-4">
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Sub-Category</label>
                    <select id="category" name="category" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 shadow-sm">
                        <option value="">Select Sub-Category</option>
                        <option value="General">General</option>
                        <option value="Business">Business</option>
                        <option value="Technology">Technology</option>
                        <option value="Health">Health</option>
                        <option value="Education">Education</option>
                        <option value="Finance">Finance</option>
                        <option value="Lifestyle">Lifestyle</option>
                        <option value="News">News</option>
                        <option value="E-commerce">E-commerce</option>
                        <option value="Other">Other</option>
                    </select>
                    <span id="categoryError" class="text-red-500 text-xs mt-1"></span>
                </div>
                <div class="mb-4">
                    <label for="country" class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                    <select id="country" name="country" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 shadow-sm">
                        <option value="">Select Country</option>
                        <option value="Afghanistan">Afghanistan</option>
                        <option value="Albania">Albania</option>
                        <option value="Algeria">Algeria</option>
                        <option value="Andorra">Andorra</option>
                        <option value="Angola">Angola</option>
                        <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                        <!-- Add more countries as needed -->
                    </select>
                    <span id="countryError" class="text-red-500 text-xs mt-1"></span>
                </div>
                <div class="mb-4">
                    <label for="moz_da" class="block text-sm font-medium text-gray-700 mb-1">Moz DA</label>
                    <input type="number" id="moz_da" name="moz_da" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 shadow-sm" min="0" max="100">
                    <span id="mozDaError" class="text-red-500 text-xs mt-1"></span>
                </div>
                <div class="mb-4">
                    <label for="traffic" class="block text-sm font-medium text-gray-700 mb-1">Traffic</label>
                    <input type="text" id="traffic" name="traffic" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 shadow-sm">
                    <span id="trafficError" class="text-red-500 text-xs mt-1"></span>
                </div>
                <div class="mb-4">
                    <label for="spam_score" class="block text-sm font-medium text-gray-700 mb-1">Spam Score</label>
                    <input type="number" id="spam_score" name="spam_score" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 shadow-sm" min="0" max="100">
                    <span id="spamScoreError" class="text-red-500 text-xs mt-1"></span>
                </div>
                <div class="mb-4">
                    <label for="submission_type" class="block text-sm font-medium text-gray-700 mb-1">Submission Type</label>
                    <select id="submission_type" name="submission_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 shadow-sm">
                        <option value="Free">Free</option>
                        <option value="Paid">Paid</option>
                    </select>
                    <span id="submissionTypeError" class="text-red-500 text-xs mt-1"></span>
                </div>
                <div class="mb-4">
                    <label for="report_url" class="block text-sm font-medium text-gray-700 mb-1">Report URL</label>
                    <input type="url" id="report_url" name="report_url" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 shadow-sm">
                    <span id="reportUrlError" class="text-red-500 text-xs mt-1"></span>
                </div>
            </div>
            <div class="sticky bottom-0 bg-white p-4 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('addModal')" 
                    class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:ring-2 focus:ring-gray-300 transition">
                    Cancel
                </button>
                <button type="submit" id="submitButton" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-300 transition">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg w-96 text-center shadow-xl">
        <h2 class="text-lg font-bold text-green-600">Success!</h2>
        <p id="successMessage" class="mt-2"></p>
        <button onclick="closeSuccessModal()" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:ring-2 focus:ring-blue-300 transition">OK</button>
    </div>
</div>

<script>
document.getElementById('siteForm').addEventListener('submit', function (event) {
    event.preventDefault();
    const form = this;
    const siteId = document.getElementById('siteId').value;
    const url = siteId ? '{{ route('submission_sites.update', ':id') }}'.replace(':id', siteId) : '{{ route('submission_sites.store') }}';
    const method = siteId ? 'PUT' : 'POST';

    document.getElementById('method').value = method;

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: new FormData(form)
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Raw response:', text);
                throw new Error(`HTTP error! Status: ${response.status}, Response: ${text.substring(0, 100)}...`);
            });
        }
        return response.json();
    })
    .then(data => {
        showSuccessModal(data.message || 'Operation successful');
        form.reset();
        document.getElementById('siteId').value = '';
        document.getElementById('method').value = '';
        closeModal('addModal');
        setTimeout(() => location.reload(), 1000);
    })
    .catch(error => {
        console.error('Error:', error);
        if (error.errors) {
            displayErrors(error.errors);
        } else {
            alert('An error occurred: ' + (error.message || 'Unknown error'));
        }
    });
});

function displayErrors(errors) {
    document.getElementById('categoryIdError').innerText = errors.category_id ? errors.category_id[0] : '';
    document.getElementById('websiteNameError').innerText = errors.website_name ? errors.website_name[0] : '';
    document.getElementById('registerUrlError').innerText = errors.register_url ? errors.register_url[0] : '';
    document.getElementById('categoryError').innerText = errors.category ? errors.category[0] : '';
    document.getElementById('countryError').innerText = errors.country ? errors.country[0] : '';
    document.getElementById('mozDaError').innerText = errors.moz_da ? errors.moz_da[0] : '';
    document.getElementById('trafficError').innerText = errors.traffic ? errors.traffic[0] : '';
    document.getElementById('spamScoreError').innerText = errors.spam_score ? errors.spam_score[0] : '';
    document.getElementById('submissionTypeError').innerText = errors.submission_type ? errors.submission_type[0] : '';
    document.getElementById('reportUrlError').innerText = errors.report_url ? errors.report_url[0] : '';
}

function openAddModal() {
    document.getElementById('addModal').classList.remove('hidden');
    document.getElementById('modalTitle').innerText = 'Add Submission Site';
    document.getElementById('submitButton').innerText = 'Save';
    document.getElementById('siteId').value = '';
    document.getElementById('method').value = '';
    document.getElementById('siteForm').reset();
    displayErrors({}); // Clear errors
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

function showSuccessModal(message) {
    console.log('Showing success modal with message:', message);
    document.getElementById('successMessage').innerText = message;
    document.getElementById('successModal').classList.remove('hidden');
}

function closeSuccessModal() {
    document.getElementById('successModal').classList.add('hidden');
    location.reload();
}

function editSite(id) {
    fetch('{{ route('submission_sites.edit', ':id') }}'.replace(':id', id), {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(res => {
        if (!res.ok) {
            return res.text().then(text => {
                console.error('Raw response:', text);
                throw new Error(`Failed to fetch site data: HTTP ${res.status}, Response: ${text.substring(0, 100)}...`);
            });
        }
        return res.json();
    })
    .then(data => {
        document.getElementById('modalTitle').innerText = 'Edit Submission Site';
        document.getElementById('submitButton').innerText = 'Update';
        document.getElementById('siteId').value = data.site.id;
        document.getElementById('method').value = 'PUT';
        document.getElementById('category_id').value = data.site.category_id || '';
        document.getElementById('website_name').value = data.site.website_name || '';
        document.getElementById('register_url').value = data.site.register_url || '';
        document.getElementById('category').value = data.site.category || '';
        document.getElementById('country').value = data.site.country || '';
        document.getElementById('moz_da').value = data.site.moz_da || '';
        document.getElementById('traffic').value = data.site.traffic || '';
        document.getElementById('spam_score').value = data.site.spam_score || '';
        document.getElementById('submission_type').value = data.site.submission_type || 'Free';
        document.getElementById('report_url').value = data.site.report_url || '';
        document.getElementById('addModal').classList.remove('hidden');
        displayErrors({}); // Clear errors
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to load site data: ' + error.message);
    });
}

function deleteSite(button) {
    const siteId = button.getAttribute('data-id');
    if (!confirm('Are you sure you want to delete this site?')) {
        return;
    }
    fetch('{{ route('submission_sites.destroy', ':id') }}'.replace(':id', siteId), {
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
                throw new Error(`Failed to delete site: HTTP ${response.status}, Response: ${text.substring(0, 100)}...`);
            });
        }
        return response.json();
    })
    .then(data => {
        document.getElementById(`site-${siteId}`).remove();
        showSuccessModal(data.message);
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete site: ' + error.message);
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

    // Show success modal if session has success message
    @if (session('success'))
        showSuccessModal('{{ session('success') }}');
    @endif
});
</script>
@endsection
