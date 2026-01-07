@extends('layouts.dashboard')

@section('content')
<div class="bg-white shadow-md rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Leave History</h2>
        <a href="{{ route('leaves.create') }}" 
           class="font-semibold text-white bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-teal-300 font-medium rounded-lg px-5 py-2.5 text-center transition duration-200">
            New Request
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            <ul class="list-disc list-inside mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(config('app.debug'))
    <div class="mb-6 p-4 bg-yellow-100 border border-yellow-400 rounded-lg">
        <strong>Debug Info:</strong><br>
        Total Requests: {{ $requests->count() }}<br>
        Total Pages: {{ $requests->lastPage() }}<br>
        Current Page: {{ $requests->currentPage() }}
    </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full border-collapse bg-white shadow-md rounded-lg text-left" id="leavesTable">
            <thead class="bg-slate-50 text-gray-700 font-semibold">
                <tr>
                    <th class="border px-5 py-3">S/N</th>
                    <th class="border px-5 py-3">Dates</th>
                    <th class="border px-5 py-3">Type</th>
                    <th class="border px-5 py-3">Duration</th>
                    <th class="border px-5 py-3">Status</th>
                    <th class="border px-5 py-3">Applied</th>
                    <th class="border px-5 py-3">Note</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $request)
                    <tr id="leave-{{ $request->id }}" class="hover:bg-gray-50 transition">
                        <td class="border px-5 py-3 text-gray-700">
                            {{ $requests->firstItem() + $loop->index }}
                        </td>
                        <td class="border px-5 py-3 text-gray-700">
                            @if($request->start_date && $request->end_date && $request->start_date->eq($request->end_date))
                                <span class="font-medium">{{ $request->start_date->format('d M Y') }}</span>
                            @else
                                <div>
                                    <div class="font-medium">{{ $request->start_date->format('d M Y') }}</div>
                                    @if($request->end_date && !$request->start_date->eq($request->end_date))
                                        <div class="text-sm text-gray-500">- {{ $request->end_date->format('d M Y') }}</div>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="border px-5 py-3 font-medium text-gray-900">
                            {{ $request->leavePolicy->name ?? 'N/A' }}
                        </td>
                        <td class="border px-5 py-3 text-gray-700">
                            @if($request->duration)
                                <div class="font-medium">
                                    {{ number_format($request->duration, 1) }} 
                                    day{{ $request->duration > 1 ? 's' : '' }}
                                </div>
                                @if($request->partial_type)
                                    <div class="text-sm text-gray-500">
                                        ({{ ucfirst($request->partial_type) }} - 
                                        {{ $request->partial_minutes ?? 0 }} mins)
                                    </div>
                                @endif
                            @else
                                <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
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
                        <td class="border px-5 py-3 text-gray-700 text-sm">
                            {{ $request->created_at->format('d M Y H:i') }}
                        </td>
                        <td class="border px-5 py-3">
                            @if($request->note)
                                <button onclick="showNote('{{ addslashes($request->note) }}')" 
                                        class="text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-3 py-1.5 transition-colors shadow-sm">
                                    <i class="fas fa-eye mr-1"></i>View
                                </button>
                            @else
                                <span class="text-gray-400 text-sm">No note</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="border px-5 py-8 text-center">
                            <div class="text-gray-500 py-8">
                                <i class="fas fa-calendar-times fa-3x mb-4 text-gray-300 block mx-auto"></i>
                                <p class="text-lg font-medium">No leave requests found</p>
                                <p class="text-sm">Your leave history will appear here once you submit requests.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($requests->hasPages())
    <div class="mt-6 flex justify-end">
        {{ $requests->links() }}
    </div>
    @endif
</div>

<!-- Note Modal -->
<div id="noteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Leave Note</h3>
                <button onclick="closeNoteModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times fa-lg"></i>
                </button>
            </div>
            <div id="noteContent" class="text-gray-700 whitespace-pre-wrap"></div>
        </div>
        <div class="items-center px-4 py-3">
            <button onclick="closeNoteModal()" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                Close
            </button>
        </div>
    </div>
</div>

<script>
function showNote(note) {
    document.getElementById('noteContent').textContent = note;
    document.getElementById('noteModal').classList.remove('hidden');
}

function closeNoteModal() {
    document.getElementById('noteModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('noteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeNoteModal();
    }
});
</script>
@endsection