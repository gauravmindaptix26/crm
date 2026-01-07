@extends('layouts.dashboard')

@section('title', 'Guest Posts')

@section('content')
<div class="bg-white shadow-md rounded-lg p-4 mb-4 flex flex-wrap justify-between items-center gap-4">
    <!-- Entries Per Page -->
    <div class="flex items-center space-x-2">
        <label class="text-sm font-medium text-gray-700">Show</label>
        <select id="entriesPerPage"
                class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring focus:border-blue-500"
                onchange="changePerPage(this.value)">
            <option value="10"  {{ $perPage == 10  ? 'selected' : '' }}>10</option>
            <option value="25"  {{ $perPage == 25  ? 'selected' : '' }}>25</option>
            <option value="50"  {{ $perPage == 50  ? 'selected' : '' }}>50</option>
            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
        </select>
        <span class="text-sm font-medium text-gray-700">entries</span>
    </div>

    <!-- Search Box -->
    <input type="text"
           id="searchInput"
           value="{{ $search ?? '' }}"
           placeholder="Search website, DA, publisher..."
           class="border border-gray-300 rounded-lg px-4 py-2 w-64 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm"
           onkeyup="debounceSearch(this.value)">
</div>
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between mb-4">
             <h2 class="text-xl font-bold">Guest Posts</h2>
            <button onclick="openModal()" class="bg-black hover:bg-gray-900 text-white px-4 py-2 rounded-lg shadow-md transition-all flex items-center space-x-2">
                 <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 16.875h3.375m0 0h3.375m-3.375 0V13.5m0 3.375v3.375M6 10.5h2.25a2.25 2.25 0 0 0 2.25-2.25V6a2.25 2.25 0 0 0-2.25-2.25H6A2.25 2.25 0 0 0 3.75 6v2.25A2.25 2.25 0 0 0 6 10.5Zm0 9.75h2.25A2.25 2.25 0 0 0 10.5 18v-2.25a2.25 2.25 0 0 0-2.25-2.25H6a2.25 2.25 0 0 0-2.25 2.25V18A2.25 2.25 0 0 0 6 20.25Zm9.75-9.75H18a2.25 2.25 0 0 0 2.25-2.25V6A2.25 2.25 0 0 0 18 3.75h-2.25A2.25 2.25 0 0 0 13.5 6v2.25a2.25 2.25 0 0 0 2.25 2.25Z"></path>
         </svg>  <span>Add Guest Post</span></button>
        </div>

        <!-- Show validation errors -->
        @if ($errors->any())
            <div class="bg-red-500 text-white p-4 mb-4 rounded">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div id="successModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg w-96 text-center">
        <h2 class="text-lg font-bold text-green-600">Success!</h2>
        <p id="successMessage" class="mt-2"></p>
        <button onclick="closeSuccessModal()" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded">OK</button>
    </div>
</div>
        <table class="w-full border-collapse border border-gray-200">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-4 py-2">S/N</th>
                    <th class="border px-4 py-2">Website</th>
                    <th class="border px-4 py-2">DA</th>
                    <th class="border px-4 py-2">PA</th>
                    <th class="border px-4 py-2">Country</th>
                    <th class="border px-4 py-2">Industry</th>
                    <th class="border px-4 py-2">Traffic</th>
                    <th class="border px-4 py-2">Publisher</th>
                    <th class="border px-4 py-2">Created BY</th>

                    <th class="border px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($guestPosts as $guestPost)
                    <tr id="guestPost-{{ $guestPost->id }}">
                        <td class="border px-4 py-2">{{ $guestPosts->firstItem() + $loop->index }}</td>
                        <td class="border px-4 py-2">{{ $guestPost->website }}</td>
                        <td class="border px-4 py-2">{{ $guestPost->da }}</td>
                        <td class="border px-4 py-2">{{ $guestPost->pa }}</td>
                        <td class="border px-4 py-2">{{ $guestPost->country?->name ?? 'N/A' }}</td>
                        <td class="border px-4 py-2">{{ $guestPost->industry }}</td>
                        <td class="border px-4 py-2">{{ $guestPost->traffic }}</td>
                        <td class="border px-4 py-2">{{ $guestPost->publisher }}</td>
                        <td class="border px-4 py-2">By: {{ $guestPost->creator?->name  ?? 'N/A' }}<br>
                       At: {{ $guestPost->created_at }}
                    </td>

                        <td class="border px-4 py-2">
                            <button onclick="editGuestPost({{ $guestPost->id }})" class="p-2 rounded bg-[#2dd4bf] text-white hover:bg-[#2dd4bf] hover:scale-110 active:scale-95 transition-transform duration-200 shadow-md"> <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"></path>
                     </svg></button>
                            <button onclick="deleteGuestPost({{ $guestPost->id }})" class="p-2 rounded bg-[#E74C3C] text-white hover:bg-[#E74C3C] hover:scale-110 active:scale-95 transition-transform duration-200 shadow-md">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"></path>
                     </svg>
                  </button>


                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
{{ $guestPosts->appends(request()->query())->links() }}        </div>
    </div>

    <!-- Modal -->
    <div id="guestPostModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg w-11/12 md:w-1/3 max-h-[80vh] overflow-y-auto relative">
              <button onclick="closeModal()" class="absolute top-3 right-3 bg-black text-white text-2xl hover:bg-gray-800 rounded-full w-8 h-8 flex items-center justify-center">&times;</button>
            <h2 class="text-xl font-bold mb-4 text-center bg-[#14b8a6f2] text-white p-[10px] rounded" id="modalTitle">Add Guest Post</h2>
            <form id="guestPostForm">
                @csrf
                <input type="hidden" id="guestPostId">
                            <div class="grid grid-cols-2 gap-2">

                 <div class="mt-3">
                    <label for="website" class="mb-[3px] inline-block">Website</label>
                    <input type="text" id="website" name="website" class="w-full px-3 py-2 border rounded">
                                  

                    @error('website')
                        <div class="text-red-500 text-sm">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mt-3">
                    <label for="da" class="mb-[3px] inline-block">DA</label>
                    <input type="number" id="da" name="da" class="w-full px-3 py-2 border rounded">
                    @error('da')
                        <div class="text-red-500 text-sm">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mt-3">
                    <label for="pa" class="mb-[3px] inline-block">PA</label>
                    <input type="number" id="pa" name="pa" class="w-full px-3 py-2 border rounded">
                    @error('pa')
                        <div class="text-red-500 text-sm">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mt-3">
                    <label for="country" class="mb-[3px] inline-block">Country</label>
                    <select id="country_id" name="country_id" class="w-full px-3 py-2 border rounded">
                        <option value="">Select a Country</option>
                        @foreach ($countries as $country)
                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                        @endforeach
                    </select>
                    @error('country')
                        <div class="text-red-500 text-sm">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-3">
                    <label for="industry" class="mb-[3px] inline-block">Industry</label>
                    <input type="text" id="industry" name="industry" class="w-full px-3 py-2 border rounded">
                    @error('industry')
                        <div class="text-red-500 text-sm">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-3">
                    <label for="traffic" class="mb-[3px] inline-block">Traffic</label>
                    <input type="text" id="traffic" name="traffic" class="w-full px-3 py-2 border rounded">
                    @error('traffic')
                        <div class="text-red-500 text-sm">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-3">
                    <label for="publisher" class="mb-[3px] inline-block">Publisher</label>
                    <input type="text" id="publisher" name="publisher" class="w-full px-3 py-2 border rounded">
                    @error('publisher')
                        <div class="text-red-500 text-sm">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Added missing fields -->
                <div class="mt-3">
                    <label for="publisher_price" class="mb-[3px] inline-block">Publisher Price</label>
                    <input type="number" id="publisher_price" name="publisher_price" class="w-full px-3 py-2 border rounded">
                    @error('publisher_price')
                        <div class="text-red-500 text-sm">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-3">
                    <label for="our_price" class="mb-[3px] inline-block">Our Price</label>
                    <input type="number" id="our_price" name="our_price" class="w-full px-3 py-2 border rounded">
                    @error('our_price')
                        <div class="text-red-500 text-sm">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-3">
                    <label for="publisher_details" class="mb-[3px] inline-block">Publisher Details</label>
                    <textarea id="publisher_details" name="publisher_details" class="w-full px-3 py-2 border rounded"></textarea>
                    @error('publisher_details')
                        <div class="text-red-500 text-sm">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-3">
                    <label for="live_link" class="mb-[3px] inline-block">Live Link</label>
                    <input type="url" id="live_link" name="live_link" class="w-full px-3 py-2 border rounded">
                    @error('live_link')
                        <div class="text-red-500 text-sm">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex justify-end space-x-2 mt-4">
                 <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-all">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600
            hover:from-teal-500 hover:via-teal-600 hover:to-teal-700 text-white rounded hover:bg-blue-700 transition-all">Save</button>
                </div>
                  </div>
            </form>
        </div>
    </div>

@endsection

@section('scripts')
<script>

function changePerPage(value) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', value);
    url.searchParams.set('page', 1);
    window.location = url.toString();
}

let searchTimeout;
function debounceSearch(value) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const url = new URL(window.location);
        if (value.trim() !== '') {
            url.searchParams.set('search', value.trim());
        } else {
            url.searchParams.delete('search');
        }
        url.searchParams.set('page', 1);
        window.location = url.toString();
    }, 600);
}
    // Clear previous error messages
    function clearErrors() {
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        document.querySelectorAll('input, select, textarea').forEach(el => {
            el.classList.remove('border-red-500');
        });
    }

    // Display validation errors under fields
    function showErrors(errors) {
        clearErrors();
        Object.keys(errors).forEach(field => {
            const input = document.getElementById(field);
            if (input) {
                input.classList.add('border-red-500');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message text-red-500 text-sm mt-1';
                errorDiv.textContent = errors[field][0]; // First error message
                input.parentNode.appendChild(errorDiv);
            }
        });
    }

    // Open modal for Add
    function openModal() {
        clearErrors();
        document.getElementById("guestPostModal").classList.remove("hidden");
        document.getElementById("modalTitle").innerText = "Add Guest Post";
        document.getElementById("guestPostForm").reset();
        document.getElementById("guestPostId").value = "";
    }

    // Close modal
    function closeModal() {
        document.getElementById("guestPostModal").classList.add("hidden");
        clearErrors();
    }

    // Edit Guest Post - Load data
    function editGuestPost(id) {
        fetch(`guest-posts/${id}/edit`, {
            method: "GET",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json",
            }
        })
        .then(response => response.json())
        .then(result => {
            if (!result.success) {
                alert("Failed to load data");
                return;
            }

            const data = result.data; // Important: data is nested under .data

            document.getElementById("guestPostId").value = data.id;
            document.getElementById("website").value = data.website || '';
            document.getElementById("da").value = data.da || '';
            document.getElementById("pa").value = data.pa || '';
            document.getElementById("country_id").value = data.country_id || '';
            document.getElementById("industry").value = data.industry || '';
            document.getElementById("traffic").value = data.traffic || '';
            document.getElementById("publisher").value = data.publisher || '';
            document.getElementById("publisher_price").value = data.publisher_price || '';
            document.getElementById("our_price").value = data.our_price || '';
            document.getElementById("publisher_details").value = data.publisher_details || '';
            document.getElementById("live_link").value = data.live_link || '';

            document.getElementById("modalTitle").innerText = "Edit Guest Post";
            document.getElementById("guestPostModal").classList.remove("hidden");
            clearErrors();
        })
        .catch(err => {
            console.error("Error loading guest post:", err);
            alert("Could not load guest post data.");
        });
    }

    // Delete Guest Post
    function deleteGuestPost(id) {
        if (!confirm("Are you sure you want to delete this guest post?")) return;

        fetch(`guest-posts/${id}`, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                "X-Requested-With": "XMLHttpRequest",
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`guestPost-${id}`).remove();
                showSuccessModal(data.message || "Deleted successfully!");
            } else {
                alert("Failed to delete.");
            }
        })
        .catch(err => console.error(err));
    }

    // Success Modal
    function showSuccessModal(message) {
        document.getElementById("successMessage").innerText = message;
        document.getElementById("successModal").classList.remove("hidden");
    }

    function closeSuccessModal() {
        document.getElementById("successModal").classList.add("hidden");
        location.reload(); // Refresh to see updated list
    }

    // Main Form Submit (Add & Edit)
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.getElementById("guestPostForm");

        form.addEventListener("submit", function (e) {
            e.preventDefault();
            clearErrors();

            const guestPostId = document.getElementById("guestPostId").value;
            const url = guestPostId ? `guest-posts/${guestPostId}` : "guest-posts";
            const formData = new FormData(form);

            if (guestPostId) {
                formData.append("_method", "PUT");
            }

            fetch(url, {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                    "X-Requested-With": "XMLHttpRequest",
                    "Accept": "application/json",
                }
            })
            .then(response => {
                if (response.status === 422) {
                    return response.json().then(err => { throw err; });
                }
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    closeModal();
                    showSuccessModal(data.message || "Guest post saved successfully!");
                }
            })
            .catch(error => {
                if (error.errors) {
                    showErrors(error.errors);
                } else {
                    console.error("Submission error:", error);
                    alert("An error occurred. Check console.");
                }
            });
        });

        // Search & Entries Per Page (client-side only)
        const searchInput = document.getElementById("searchInput");
        const entriesSelect = document.getElementById("entriesPerPage");
        const rows = document.querySelectorAll("table tbody tr");

        function filterTable() {
            const term = searchInput.value.toLowerCase();
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(term) ? "" : "none";
            });
        }

        function updateEntriesPerPage() {
            const limit = parseInt(entriesSelect.value);
            rows.forEach((row, index) => {
                row.style.display = index < limit ? "" : "none";
            });
        }

        searchInput?.addEventListener("keyup", filterTable);
        entriesSelect?.addEventListener("change", updateEntriesPerPage);
        updateEntriesPerPage(); // Initial apply
    });
</script>
@endsection
