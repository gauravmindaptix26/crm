@extends('layouts.dashboard')

@section('title', 'Bank Accounts')

@section('content')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="bg-white shadow-md rounded-lg p-6 mb-6 flex justify-between items-center">
        <div class="flex items-center space-x-3">
            <label for="entriesPerPage" class="text-sm font-medium text-gray-600">Show</label>
            <select id="entriesPerPage" name="per_page" class="border border-gray-300 rounded-md px-3 py-1.5 focus:ring focus:border-blue-500 text-sm">
                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
            </select>
            <span class="text-sm font-medium text-gray-600">entries</span>
        </div>
        <input type="text" id="searchInput" name="search" placeholder="Search bank accounts..." 
               value="{{ request('search') }}" 
               class="border border-gray-300 rounded-md px-4 py-2 w-72 focus:ring focus:border-blue-500 shadow-md text-gray-700 transition">
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-2xl font-semibold text-gray-800">Bank Accounts</h2>
            <button onclick="openModal()" class="bg-blue-600 text-white px-5 py-2 rounded-md shadow hover:bg-blue-700 transition duration-200">
                + Add Bank Account
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse bg-white shadow-md rounded-lg text-left" id="bankAccountsTable">
                <thead class="bg-gray-200 text-gray-700 font-semibold">
                    <tr>
                        <th class="border px-5 py-3">S/N</th>
                        <th class="border px-5 py-3">Account Name</th>
                        <th class="border px-5 py-3">Description</th>
                        <th class="border px-5 py-3">IFSC Code</th>
                        <th class="border px-5 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bankAccounts as $account)
                        <tr id="account-{{ $account->id }}" class="hover:bg-gray-100 transition">
                            <td class="border px-5 py-3 text-gray-700">{{ $bankAccounts->firstItem() + $loop->index }}</td>
                            <td class="border px-5 py-3 font-medium text-gray-900">{{ $account->account_name ?? 'N/A' }}</td>
                            <td class="border px-5 py-3 font-medium text-gray-900">{{ $account->description ?? 'No description' }}</td>
                            <td class="border px-5 py-3 font-medium text-gray-900">{{ $account->ifsc_code ?? 'N/A' }}</td>
                            <td class="border px-5 py-3 flex justify-center space-x-2">
                                <button onclick="editAccount({{ $account->id }})" 
                                        class="bg-yellow-500 text-white px-3 py-1.5 rounded-md shadow hover:bg-yellow-600 transition">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="border px-5 py-3 text-center text-gray-500">No accounts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex justify-end">
            {{ $bankAccounts->links() }}
        </div>
    </div>

    <!-- Add/Edit Bank Account Modal -->
    <div id="bankAccountModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden overflow-y-auto">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
            <h2 id="modalTitle" class="text-xl font-semibold mb-4">Add Bank Account</h2>
            
            <!-- Success Message -->
            <div id="successMessage" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                <strong>Success!</strong> <span id="successText"></span>
            </div>

            <!-- Error Message -->
            <div id="errorMessage" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <strong>Error!</strong> <span id="errorText"></span>
            </div>

            <form id="bankAccountForm" method="POST">
                @csrf
                <input type="hidden" id="account_id" name="account_id">
                
                <div class="mb-4">
                    <label for="account_name" class="block text-sm font-medium text-gray-700">Account Name</label>
                    <input type="text" id="account_name" name="account_name" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring focus:border-blue-500">
                </div>

                <div class="mb-4">
                    <label for="bank_name" class="block text-sm font-medium text-gray-700">Bank Name</label>
                    <input type="text" id="bank_name" name="bank_name" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring focus:border-blue-500">
                </div>

                <div class="mb-4">
                    <label for="ifsc_code" class="block text-sm font-medium text-gray-700">IFSC Code</label>
                    <input type="text" id="ifsc_code" name="ifsc_code" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring focus:border-blue-500">
                </div>

                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="description" name="description" rows="4" 
                              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring focus:border-blue-500"></textarea>
                </div>

                <div class="mb-4">
                    <label for="account_number" class="block text-sm font-medium text-gray-700">Account Number</label>
                    <input type="text" id="account_number" name="account_number" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring focus:border-blue-500">
                </div>

                <div class="mb-4">
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status" name="status" required 
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring focus:border-blue-500">
                        <option value="">Select Status</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" class="bg-gray-400 text-white px-4 py-2 rounded-md">Cancel</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Set up CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function openModal(account = null) {
            document.getElementById('bankAccountForm').reset();
            document.getElementById('account_id').value = account ? account.id : '';
            document.getElementById('modalTitle').innerText = account ? 'Edit Bank Account' : 'Add Bank Account';
            document.getElementById('successMessage').classList.add('hidden');
            document.getElementById('errorMessage').classList.add('hidden');

            if (account) {
                document.getElementById('account_name').value = account.account_name || '';
                document.getElementById('bank_name').value = account.bank_name || '';
                document.getElementById('account_number').value = account.account_number || '';
                document.getElementById('ifsc_code').value = account.ifsc_code || '';
                document.getElementById('description').value = account.description || '';
                document.getElementById('status').value = account.status || '';
            }

            document.getElementById('bankAccountModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('bankAccountModal').classList.add('hidden');
            document.getElementById('successMessage').classList.add('hidden');
            document.getElementById('errorMessage').classList.add('hidden');
        }

        function editAccount(accountId) {
            fetch(`payment_accounts/${accountId}/edit`, {
                headers: { 'X-CSRF-TOKEN': csrfToken }
            })
                .then(response => {
                    if (!response.ok) throw new Error('Failed to fetch account data');
                    return response.json();
                })
                .then(data => openModal(data))
                .catch(error => {
                    document.getElementById('errorText').innerHTML = error.message;
                    document.getElementById('errorMessage').classList.remove('hidden');
                });
        }

        document.getElementById('bankAccountForm').addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            let accountId = document.getElementById('account_id').value;
            let url = accountId ? `payment_accounts/${accountId}` : 'payment_accounts';
            
            if (accountId) {
                formData.append('_method', 'PUT');
            }

            fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw data; // Throw the error response
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    document.getElementById('successText').innerText = data.message;
                    document.getElementById('successMessage').classList.remove('hidden');
                    document.getElementById('errorMessage').classList.add('hidden');
                    setTimeout(() => {
                        closeModal();
                        location.reload();
                    }, 2000);
                })
                .catch(error => {
                    let errorMsg = 'An error occurred.';
                    if (error.errors) {
                        // Handle validation errors
                        errorMsg = Object.values(error.errors).flat().join('<br>');
                    } else if (error.message) {
                        errorMsg = error.message;
                    }
                    document.getElementById('errorText').innerHTML = errorMsg;
                    document.getElementById('errorMessage').classList.remove('hidden');
                    document.getElementById('successMessage').classList.add('hidden');
                });
        });

        // Handle per-page selection
        document.getElementById('entriesPerPage').addEventListener('change', function () {
            const perPage = this.value;
            const url = new URL(window.location);
            url.searchParams.set('per_page', perPage);
            url.searchParams.set('page', 1);
            window.location.href = url.toString();
        });

        // Handle search input (debounced)
        let searchTimeout;
        document.getElementById('searchInput').addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const search = this.value;
                const url = new URL(window.location);
                url.searchParams.set('search', search);
                url.searchParams.set('page', 1);
                window.location.href = url.toString();
            }, 300);
        });
    </script>
@endsection