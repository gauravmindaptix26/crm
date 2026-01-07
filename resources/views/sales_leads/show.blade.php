@extends('layouts.dashboard')

@section('content')
<div class="container grid grid-cols-1 md:grid-cols-2 gap-6">

    <!-- Section 1: Sales Lead Details -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-xl font-bold mb-4">Sales Lead Details</h2>
        <ul class="text-sm space-y-2">
            <li><strong>Bid Date:</strong> {{ optional($lead->bid_date)->format('d-F-Y') }}</li>
            <li><strong>Status:</strong> {{ $lead->status ?? '-' }}</li>
            <li><strong>Status Message:</strong> {{ $lead->status_message ?? '-' }}</li>
            <li><strong>Status Updated:</strong> {{ optional($lead->updated_at)->format('d-F-Y') }}</li>
            <li><strong>Client Name:</strong> {{ $lead->client_name }}</li>
            <li><strong>Email:</strong> {{ $lead->client_email }}</li>
            <li><strong>Phone:</strong> {{ $lead->client_phone }}</li>
            <li><strong>Client Type:</strong> {{ $lead->client_type }}</li>
            <li><strong>Job Title:</strong> {{ $lead->job_title }}</li>
            <li><strong>Job URL:</strong> <a href="{{ $lead->job_url }}" class="text-blue-600 underline" target="_blank">{{ $lead->job_url }}</a></li>
            <li><strong>Country:</strong> {{ $lead->country->name ?? '-' }}</li>
            <li><strong>Lead From:</strong> {{ $lead->lead_from }}</li>
            <li><strong>Sales Person:</strong> {{ $lead->user->name ?? '-' }}</li>
            <li><strong>Department:</strong> {{ $lead->department->name ?? '-' }}</li>
        </ul>
    </div>

    <!-- Section 2: General Notes -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">General Notes</h2>
            <button onclick="document.getElementById('noteModal').classList.remove('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Add Note
            </button>
        </div>

        <div class="space-y-3 max-h-96 overflow-y-auto">
            @forelse ($lead->notes as $note)
                <div class="border p-3 rounded shadow-sm">
                    <div class="flex justify-between items-center text-sm text-gray-600">
                        <span><strong>{{ $note->note_type }}</strong> - {{ $note->title }}</span>
                        <span>{{ $note->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="mt-1 text-sm">{{ $note->description }}</p>
                    @if($note->attachment)
                        <a href="{{ asset('storage/' . $note->attachment) }}" target="_blank" class="text-blue-600 text-sm">View Attachment</a>
                    @endif
                    <div class="text-xs text-gray-400 mt-1">Added by: {{ $note->user->name ?? 'N/A' }}</div>
                </div>
            @empty
                <p class="text-gray-500 text-sm">No notes added yet.</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Modal: Add Note -->
<div id="noteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg w-full max-w-md p-6 relative">
        <button onclick="document.getElementById('noteModal').classList.add('hidden')" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-xl font-bold">&times;</button>
        <h3 class="text-lg font-bold mb-4">Add Note</h3>
        <form method="POST" action="{{ route('sales-lead.addNote', $lead->id) }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="block text-sm font-medium">Note Type</label>
                <select name="note_type" class="w-full border rounded p-2">
                    <option value="Follow up">Follow up</option>
                    <option value="General">General</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium">Title</label>
                <input type="text" name="title" class="w-full border rounded p-2" required>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium">Description</label>
                <textarea name="description" class="w-full border rounded p-2" required></textarea>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium">Attachment (optional)</label>
                <input type="file" name="attachment" class="w-full">
            </div>
            <div class="text-right">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save Note</button>
            </div>
        </form>
    </div>
</div>
@endsection
