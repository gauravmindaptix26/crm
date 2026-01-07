@extends('layouts.dashboard')

@section('title', 'Project Payments')

@section('content')
<div class="mb-4">
        @if (auth()->user()->hasRole('Employee'))
            <div class="flex gap-3 mb-4">
                <a href="{{ route('my.assigned.projects') }}"
                   class="bg-gray-500 text-white px-4 py-2.5 rounded-lg hover:bg-gray-600 transition duration-200 font-medium text-sm">
                    ← Back to  Assigned Projects
                </a>
            </div>
        @else
            <div class="flex gap-3 mb-4">
                <a href="{{ route('projects.index') }}"
                   class="bg-gray-500 text-white px-4 py-2.5 rounded-lg hover:bg-gray-600 transition duration-200 font-medium text-sm">
                    ← Back to Projects
                </a>
            </div>
        @endif
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
    <input type="text" id="searchInput" placeholder="Search payments..." 
           class="border border-gray-300 rounded-md px-4 py-2 w-72 focus:ring focus:border-blue-500 shadow-md text-gray-700 transition">
</div>

<div class="bg-white shadow-md rounded-lg p-6">
    <div class="flex justify-between items-center mb-5">
        <h2 class="text-2xl font-semibold text-gray-800">Project Payments</h2>
        <button onclick="openModal('addModal')" class="bg-blue-600 text-white px-5 py-2 rounded-md shadow hover:bg-blue-700 transition duration-200">
            + Add Payment
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse bg-white shadow-md rounded-lg text-left" id="paymentsTable">
            <thead>
                <tr>
                    <th class="border px-5 py-3 text-gray-600">Amount</th>
                    <th class="border px-5 py-3 text-gray-600">Commission</th>
                    <th class="border px-5 py-3 text-gray-600">Month-Year</th>
                    <th class="border px-5 py-3 text-gray-600">Account</th>
                    <th class="border px-5 py-3 text-gray-600">Payment Details</th>
                    <th class="border px-5 py-3 text-gray-600">Screenshot</th>
                    <th class="border px-5 py-3 text-gray-600">Added By</th>
                    <th class="border px-5 py-3 text-gray-600">Added On</th>
                    <th class="border px-5 py-3 text-gray-600">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payments as $payment)
                    <tr id="payment-{{ $payment->id }}" class="hover:bg-gray-100 transition">
                        <td class="border px-5 py-3 text-gray-700">${{ number_format($payment->payment_amount, 2) }}</td>
                        <td class="border px-5 py-3 text-gray-700">{{ $payment->commission_amount ? number_format($payment->commission_amount, 2) . '%' : 'N/A' }}</td>
                        <td class="border px-5 py-3 text-gray-700">{{ \Carbon\Carbon::parse($payment->payment_month)->format('F Y') }}</td>
                        <td class="border px-5 py-3 text-gray-700">{{ $payment->account->account_name ?? 'N/A' }}</td>
                        <td class="border px-5 py-3 text-gray-700">{{ $payment->payment_details ?? 'N/A' }}</td>
                        <td class="border px-5 py-3 text-gray-700 text-center">
                            @if($payment->screenshot)
                                <a href="{{ asset('storage/' . $payment->screenshot) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $payment->screenshot) }}" alt="screenshot" class="w-16 h-16 object-cover rounded shadow mx-auto mb-1" />
                                    <div class="text-blue-600 hover:underline text-sm">View</div>
                                </a>
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="border px-5 py-3 text-gray-700">{{ $payment->creator->name ?? 'N/A' }}</td>
                        <td class="border px-5 py-3 text-gray-700">{{ $payment->created_at->format('d M Y') }}</td>
                        <td class="border px-5 py-3 flex justify-center space-x-2">
                            <button onclick="deletePayment(this)" data-id="{{ $payment->id }}"
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
        {{ $payments->links() }}
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 overflow-auto">
    <div class="bg-white p-8 rounded-lg w-full max-w-xl mx-auto shadow-lg relative border-4 max-h-[90vh] overflow-y-auto"
         style="border-image-source: linear-gradient(to right, #0178bc, #00bdda); border-image-slice: 1;">
        <!-- Close Button -->
        <button onclick="closeModal('addModal')" class="absolute top-3 right-3 text-2xl text-gray-500 hover:text-black">&times;</button>

        <h2 class="text-2xl font-semibold mb-6 text-gray-800" id="modalTitle">Add Payment</h2>

        <form id="paymentForm" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="paymentId">
            <input type="hidden" name="project_id" value="{{ $project->id }}">

            <!-- Amount -->
            <div class="mb-4">
                <label for="payment_amount" class="block text-sm font-medium text-gray-700">Payment Amount (USD)</label>
                <input type="number" step="0.01" id="payment_amount" name="payment_amount"
                       class="w-full px-4 py-2 border rounded-md focus:ring focus:border-blue-500 text-gray-900 shadow-sm">
                <span id="amountError" class="text-red-500 text-sm"></span>
            </div>

            <!-- Paid On -->
            <div class="mb-4">
                <label for="payment_month" class="block text-sm font-medium text-gray-700">Payment for Month</label>
                <input type="date" id="payment_month" name="payment_month"
                       class="w-full px-4 py-2 border rounded-md focus:ring focus:border-blue-500 text-gray-900 shadow-sm">
                <span id="paidOnError" class="text-red-500 text-sm"></span>
            </div>

            <!-- Commission Amount -->
            <div class="mb-4">
                <label for="commission_amount" class="block text-sm font-medium text-gray-700">Commission Amount</label>
                <input type="number" step="0.01" id="commission_amount" name="commission_amount"
                       class="w-full px-4 py-2 border rounded-md text-gray-900 shadow-sm focus:ring focus:border-blue-500">
                <span id="commissionError" class="text-red-500 text-sm"></span>
            </div>

            <!-- Payment Details -->
            <div class="mb-4">
                <label for="payment_details" class="block text-sm font-medium text-gray-700">Payment Details</label>
                <textarea id="payment_details" name="payment_details" rows="3"
                          class="w-full px-4 py-2 border rounded-md text-gray-900 shadow-sm focus:ring focus:border-blue-500"></textarea>
                <span id="detailsError" class="text-red-500 text-sm"></span>
            </div>

            <!-- Account where payment received -->
            <div class="mb-4">
                <label for="account_id" class="block text-sm font-medium text-gray-700">Account where payment received</label>
                <select id="account_id" name="account_id"
                        class="w-full px-4 py-2 border rounded-md text-gray-900 shadow-sm focus:ring focus:border-blue-500">
                    <option value="">-- Select Account --</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->account_name }}</option>
                    @endforeach
                </select>
                <span id="accountError" class="text-red-500 text-sm"></span>
            </div>

            <!-- Payment Screenshot -->
            <div class="mb-4">
                <label for="screenshot" class="block text-sm font-medium text-gray-700">Payment Screenshot</label>
                <input type="file" id="screenshot" name="screenshot"
                       accept=".jpg,.jpeg,.png,.gif,.pdf"
                       class="w-full px-4 py-2 border rounded-md focus:ring focus:border-blue-500 text-gray-900 shadow-sm">
                <span id="screenshotError" class="text-red-500 text-sm"></span>
            </div>

            <!-- Buttons -->
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

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg w-96 text-center">
        <h2 class="text-lg font-bold text-green-600">Success!</h2>
        <p id="successMessage" class="mt-2"></p>
        <button onclick="closeSuccessModal()" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded">OK</button>
    </div>
</div>

<style>
    .input-error {
        border-color: red;
    }
</style>

<script>
document.getElementById('paymentForm').addEventListener('submit', function (event) {
    event.preventDefault();

    const form = this;
    const formData = new FormData(form);
    const paymentId = document.getElementById('paymentId').value;
    const url = paymentId ? `project_payments/${paymentId}` : 'project_payments';

    if (paymentId) {
        formData.append('_method', 'PUT');
    }

    // Clear previous error messages and error styles
    const errorSpans = ['amountError', 'paidOnError', 'commissionError', 'detailsError', 'accountError', 'screenshotError'];
    const inputs = ['payment_amount', 'payment_month', 'commission_amount', 'payment_details', 'account_id', 'screenshot'];
    errorSpans.forEach(spanId => document.getElementById(spanId).innerText = '');
    inputs.forEach(inputId => document.getElementById(inputId).classList.remove('input-error'));

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(errorData => {
                throw errorData;
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.status) {
            closeModal('addModal');
            showSuccessModal(data.message);
            form.reset();
            reloadPaymentList();
        }
    })
    .catch(error => {
        if (error.errors) {
            displayErrors(error.errors);
        } else {
            console.error('Error:', error);
            alert("An error occurred: " + (error.message || "Please try again."));
        }
    });
});

function displayErrors(errors) {
    const errorFields = {
        'payment_amount': 'amountError',
        'payment_month': 'paidOnError',
        'commission_amount': 'commissionError',
        'payment_details': 'detailsError',
        'account_id': 'accountError',
        'screenshot': 'screenshotError'
    };

    Object.keys(errorFields).forEach(field => {
        const errorSpan = document.getElementById(errorFields[field]);
        const input = document.getElementById(field === 'screenshot' ? 'screenshot' : field);
        if (errors[field]) {
            errorSpan.innerText = errors[field][0];
            input.classList.add('input-error');
        } else {
            errorSpan.innerText = '';
            input.classList.remove('input-error');
        }
    });
}

function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function showSuccessModal(message) {
    document.getElementById('successMessage').textContent = message;
    document.getElementById('successModal').classList.remove('hidden');
}

function closeSuccessModal() {
    document.getElementById('successModal').classList.add('hidden');
    location.reload();
}

function reloadPaymentList() {
    fetch(location.href, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.text())
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newTableBody = doc.querySelector('#paymentsTable tbody');
        const newPagination = doc.querySelector('.mt-6.flex.justify-end');

        document.querySelector('#paymentsTable tbody').innerHTML = newTableBody.innerHTML;
        document.querySelector('.mt-6.flex.justify-end').innerHTML = newPagination.innerHTML;
    });
}

function editPayment(id) {
    fetch(`project_payments/${id}/edit`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('modalTitle').innerText = 'Edit Payment';
            document.getElementById('paymentId').value = data.id;
            document.getElementById('payment_amount').value = data.payment_amount;
            document.getElementById('payment_month').value = data.payment_month;
            document.getElementById('commission_amount').value = data.commission_amount || '';
            document.getElementById('payment_details').value = data.payment_details || '';
            document.getElementById('account_id').value = data.account_id || '';
            openModal('addModal');
        });
}

function deletePayment(button) {
    let paymentId = button.getAttribute('data-id');

    if (!confirm('Are you sure you want to delete this payment?')) return;

    fetch(`project_payments/${paymentId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(() => {
        document.getElementById(`payment-${paymentId}`).remove();
        alert('Payment deleted successfully!');
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
        let totalRows = rows.length;
        Array.from(rows).forEach((row, index) => {
            row.style.display = index < numEntries ? "" : "none";
        });
    }

    if (searchInput) {
        searchInput.addEventListener("keyup", filterTable);
    }
    if (entriesSelect) {
        entriesSelect.addEventListener("change", updateEntriesPerPage);
        updateEntriesPerPage();
    }
});
</script>
@endsection