@extends('layouts.dashboard')

@section('title', 'Pending Leave Requests')

@section('content')
<div class="bg-white shadow-md rounded-lg p-6">
    <div class="flex justify-between items-center mb-5">
        <h2 class="text-2xl font-semibold text-gray-800">Pending Leave Requests</h2>
        <a href="{{ route('leave-requests.index') }}" class="px-5 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            My Requests
        </a>
    </div>
    
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 rounded text-green-700">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 rounded text-red-700">
            {{ session('error') }}
        </div>
    @endif
    
    <div class="overflow-x-auto">
        <table class="w-full border-collapse bg-white shadow-md rounded-lg">
            <thead class="bg-slate-50">
                <tr>
                    <th class="border px-5 py-3">Employee</th>
                    <th class="border px-5 py-3">Type</th>
                    <th class="border px-5 py-3">Dates</th>
                    <th class="border px-5 py-3">Duration</th>
                    <th class="border px-5 py-3">Reason</th>
                    <th class="border px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingRequests as $request)
                    <tr class="hover:bg-gray-50">
                        <td class="border px-5 py-3 font-medium">{{ $request->user->name }}</td>
                        <td class="border px-5 py-3">{{ $request->leavePolicy->name }}</td>
                        <td class="border px-5 py-3">
                            {{ $request->start_date }} 
                            @if($request->end_date)(to {{ $request->end_date }}) @endif
                        </td>
                        <td class="border px-5 py-3 font-semibold">{{ number_format($request->duration, 2) }} days</td>
                        <td class="border px-5 py-3 max-w-xs">{{ Str::limit($request->reason ?? 'N/A', 50) }}</td>
                        <td class="border px-5 py-3 space-x-2">
                            <form action="{{ route('leave-requests.approve', $request) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                    Approve
                                </button>
                            </form>
                            <form action="{{ route('leave-requests.reject', $request) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm"
                                        onclick="return confirm('Are you sure you want to reject this request?')">
                                    Reject
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="border px-5 py-8 text-center text-gray-500">
                            No pending leave requests.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $pendingRequests->links() }}</div>
</div>
@endsection