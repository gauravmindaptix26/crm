@extends('layouts.dashboard')

@section('content')
<div class="bg-white shadow-md rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Team Leave History</h2>
        <a href="{{ route('leaves.history') }}" 
           class="font-semibold text-white bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-teal-300 font-medium rounded-lg px-5 py-2.5 text-center transition duration-200">
            My History
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full border-collapse bg-white shadow-md rounded-lg text-left" id="teamLeavesTable">
            <thead class="bg-slate-50 text-gray-700 font-semibold">
                <tr>
                    <th class="border px-5 py-3">S/N</th>
                    <th class="border px-5 py-3">Employee</th>
                    <th class="border px-5 py-3">Dates</th>
                    <th class="border px-5 py-3">Type</th>
                    <th class="border px-5 py-3">Duration</th>
                    <th class="border px-5 py-3">Status</th>
                    <th class="border px-5 py-3">Applied</th>
                    <th class="border px-5 py-3">Note</th>
                    <th class="border px-5 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $request)
                    <tr id="leave-{{ $request->id }}" class="hover:bg-gray-50 transition">
                        <td class="border px-5 py-3 text-gray-700">{{ $requests->firstItem() + $loop->index }}</td>
                        <td class="border px-5 py-3 font-medium text-gray-900">{{ $request->user->name }}</td>
                        <td class="border px-5 py-3 text-gray-700">{{ $request->date_range }}</td>
                        <td class="border px-5 py-3 text-gray-700">{{ $request->leavePolicy->name ?? 'N/A' }}</td>
                        <td class="border px-5 py-3 text-gray-700">{{ $request->duration_display }}</td>
                        <td class="border px-5 py-3">
                            @switch($request->status)
                                @case('pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                    @break
                                @case('approved')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Approved
                                    </span>
                                    @break
                                @case('rejected')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Rejected
                                    </span>
                                    @break
                                @default
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ ucfirst($request->status) }}
                                    </span>
                            @endswitch
                        </td>
                        <td class="border px-5 py-3 text-gray-700">{{ $request->created_at->format('d M Y') }}</td>
                        <td class="border px-5 py-3 text-gray-700">{{ $request->note }}</td>
                        <td class="border px-5 py-3 flex justify-center space-x-2">
                            @if($request->status === 'pending')
                                <form action="{{ route('leaves.approve', $request) }}" method="POST" style="display: inline-block;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            class="text-white bg-green-600 hover:bg-green-700 px-3 py-2 rounded-md text-sm font-medium transition shadow-sm"
                                            onclick="return confirm('Approve this leave request?')">
                                        <i class="fas fa-check mr-1"></i> Approve
                                    </button>
                                </form>
                                <button onclick="openRejectModal({{ $request->id }})" 
                                        class="text-white bg-red-600 hover:bg-red-700 px-3 py-2 rounded-md text-sm font-medium transition shadow-sm">
                                    <i class="fas fa-times mr-1"></i> Reject
                                </button>
                            @else
                                <span class="text-gray-500">N/A</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="border px-5 py-3 text-center text-gray-500">
                            No leave requests found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6 flex justify-end">
        {{ $requests->links() }}
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-8 rounded-lg w-1/3 shadow-lg relative">
        <button onclick="closeRejectModal()" class="absolute top-3 right-3 text-2xl text-gray-500 hover:text-black">&times;</button>
        <h2 class="text-2xl font-semibold mb-6 text-gray-800">Reject Leave Request</h2>
        
        <form id="rejectForm" method="POST">
            @csrf
            @method('PATCH')
            <input type="hidden" id="rejectRequestId" name="request_id">
            <div class="mb-4">
                <label for="reason" class="block text-sm font-medium text-gray-700">Reason for Rejection <span class="text-red-500">*</span></label>
                <textarea id="reason" name="reason" class="w-full px-4 py-2 border rounded-md focus:ring focus:border-blue-500 text-gray-900 shadow-sm" rows="4" required></textarea>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeRejectModal()" 
                        class="px-4 py-2 bg-gray-500 text-white rounded-md shadow hover:bg-gray-600 transition">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-red-600 text-white rounded-md shadow hover:bg-red-700 transition">
                    Reject
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openRejectModal(id) {
    document.getElementById('rejectRequestId').value = id;
    document.getElementById('rejectForm').action = '{{ route("leaves.reject", "") }}' + id;
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('reason').value = '';
}
</script>
@endsection