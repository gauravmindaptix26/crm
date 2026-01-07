@extends('layouts.dashboard')

@section('title', 'My Leave Requests')

@section('content')
<div class="bg-white shadow-md rounded-lg p-6">
    <h2 class="text-2xl font-semibold text-gray-800 mb-5">My Leave Requests</h2>
    <a href="{{ route('leave-requests.create') }}" class="mb-4 inline-block px-5 py-2.5 bg-teal-600 text-white rounded-lg">+ Request Leave</a>
    <table class="w-full border-collapse bg-white shadow-md rounded-lg">
        <thead class="bg-slate-50">
            <tr>
                <th class="border px-5 py-3">Type</th>
                <th class="border px-5 py-3">Start</th>
                <th class="border px-5 py-3">End</th>
                <th class="border px-5 py-3">Duration</th>
                <th class="border px-5 py-3">Status</th>
                <th class="border px-5 py-3">Reason</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($requests as $request)
                <tr>
                    <td class="border px-5 py-3">{{ $request->leavePolicy->name }}</td>
                    <td class="border px-5 py-3">{{ $request->start_date }}</td>
                    <td class="border px-5 py-3">{{ $request->end_date ?? 'N/A' }}</td>
                    <td class="border px-5 py-3">{{ $request->duration }}</td>
                    <td class="border px-5 py-3">{{ ucfirst($request->status) }}</td>
                    <td class="border px-5 py-3">{{ $request->reason ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $requests->links() }}
</div>
@endsection