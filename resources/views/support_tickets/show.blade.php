@extends('layouts.dashboard')

@section('content')
<div class="max-w-5xl mx-auto bg-white shadow-lg rounded-2xl p-8 space-y-8">
    <h2 class="text-2xl font-semibold text-gray-800">Update Ticket</h2>

    <form action="{{ route('support-tickets.reply', $ticket->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Ticket Details --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ticket Title:</label>
                <input type="text" readonly class="w-full rounded-md border-gray-300 shadow-sm bg-gray-100" value="{{ $ticket->title }}">
                <input type="hidden" name="ID" value="{{ $ticket->id }}">
                <input type="hidden" name="parent_ticket" value="{{ $ticket->id }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Priority:</label>
                <select disabled class="w-full rounded-md border-gray-300 shadow-sm bg-gray-100">
                    <option value="normal" {{ $ticket->priority == 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="high" {{ $ticket->priority == 'high' ? 'selected' : '' }}>High</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Assigned To:</label>
                <select disabled class="w-full rounded-md border-gray-300 shadow-sm bg-gray-100">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ $ticket->assigned_to == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description:</label>
                <textarea readonly rows="4" class="w-full rounded-md border-gray-300 shadow-sm bg-gray-100">{{ $ticket->description }}</textarea>
            </div>
        </div>

        {{-- Replies --}}
        <div>
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Previous Replies</h3>
            <div class="space-y-4">
                @forelse($ticket->replies as $reply)
                    <div class="border rounded-lg p-4 bg-gray-50 shadow-sm">
                        <div class="flex justify-between items-center mb-1">
                            <strong class="text-gray-800">{{ $reply->user->name }}</strong>
                            <small class="text-gray-500">{{ $reply->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="text-gray-700">{{ $reply->message }}</p>
                    </div>
                @empty
                    <p class="text-gray-500 italic">No replies yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Reply Form --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status:</label>
                <select name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500">
                    <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>Open</option>
                    <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Your Reply:</label>
                <textarea name="reply_ticket_description" rows="4" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500"></textarea>
                @if ($errors->has('reply_ticket_description'))
    <p class="text-sm text-red-600 mt-1">{{ $errors->first('reply_ticket_description') }}</p>
@endif
            </div>
        </div>

        <div class="text-right pt-4">
            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                Save
            </button>
        </div>
    </form>
</div>
@endsection
