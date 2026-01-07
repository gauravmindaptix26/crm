@extends('layouts.dashboard')

@section('title', 'All Submitted Task Report')

@section('content')
<div class="bg-white shadow p-6 rounded mb-6">
    <form method="GET" action="{{ route('tasks.submitted') }}" class="flex items-center gap-4">
        <div>
            <label class="text-sm font-medium text-gray-700">Filter by Employee</label>
            <select name="filter_user" class="border rounded px-3 py-2 text-sm">
                <option value="">-- All Users --</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" {{ $filterUser == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="pt-6">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700">Filter</button>
        </div>
    </form>
</div>

<div class="bg-white shadow-md rounded-lg p-6">
    <h2 class="text-2xl font-semibold mb-4">Submitted Task Messages</h2>
    <div class="overflow-x-auto">
        <table class="w-full border-collapse bg-white shadow rounded text-left">
            <thead class="bg-gray-200 text-gray-700 font-semibold">
                <tr>
                    <th class="border px-4 py-2">S/N</th>
                    <th class="border px-4 py-2">Task Name</th>
                    <th class="border px-4 py-2">Message</th>
                    <th class="border px-4 py-2">Assigned Users</th>
                    <th class="border px-4 py-2">Assigned By</th>
                    <th class="border px-4 py-2">Submitted At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($submittedTasks as $task)
                    <tr class="hover:bg-gray-100">
                        <td class="border px-4 py-2">{{ $submittedTasks->firstItem() + $loop->index }}</td>
                        <td class="border px-4 py-2">{{ $task->name }}</td>
                        <td class="border px-4 py-2">{{ $task->done_message }}</td>
                        <td class="border px-4 py-2">
                            @foreach($task->assignedUsers as $user)
                                <div>{{ $user->name }}</div>
                            @endforeach
                        </td>
                        <td class="border px-4 py-2">{{ $task->createdBy->name ?? '-' }}</td>
                        <td class="border px-4 py-2">{{ $task->updated_at->format('d M, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">No submitted messages found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $submittedTasks->links() }}
    </div>
</div>
@endsection
