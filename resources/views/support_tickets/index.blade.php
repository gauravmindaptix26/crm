@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto p-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Support Tickets</h1>
        <button onclick="openModal('addTicketModal')" class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow-md hover:bg-blue-700 transition">+ Add Ticket</button>
    </div>

    @if(session('success'))
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                openModal('successModal');
            });
        </script>
    @endif

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg w-1/3 shadow-lg">
            <h2 class="text-xl font-bold text-green-600 mb-4">Success</h2>
            <p>{{ session('success') }}</p>
            <div class="flex justify-end mt-4">
                <button type="button" onclick="closeModal('successModal')" class="px-4 py-2 bg-blue-600 text-white rounded-lg">OK</button>
            </div>
        </div>
    </div>

    <!-- Add Ticket Modal -->
    <div id="addTicketModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg w-1/3 shadow-lg">
            <h2 class="text-xl font-bold mb-4">Add New Ticket</h2>
            <form method="POST" action="{{ route('support-tickets.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium">Title</label>
                    <input type="text" name="title" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Priority</label>
                    <select name="priority" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300">
                        <option value="Low">Low</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="High">High</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Assign To</label>
                    <select name="assigned_to" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300">
                        <option value="">Select User</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Description</label>
                    <textarea name="description" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300" required></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="closeModal('addTicketModal')" class="mr-2 px-4 py-2 bg-gray-300 rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="w-full border-collapse">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-4 py-2 text-left">#</th>
                    <th class="px-4 py-2 text-left">Title</th>
                    <th class="px-4 py-2 text-left">Description</th>
                    <th class="px-4 py-2 text-left">Users</th>
                    <th class="px-4 py-2 text-left">Priority</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $ticket)
                <tr class="border-t hover:bg-gray-100 transition">
                    <td class="px-4 py-2">{{ $tickets->firstItem() + $loop->index }}</td>
                    <td class="px-4 py-2">{{ $ticket->title }}</td>
                    <td class="px-4 py-2">{{ \Illuminate\Support\Str::limit($ticket->description, 60) }}</td>
                    <td class="px-4 py-2">
                        <div>
                            <strong>Added By:</strong> {{ $ticket->user?->name ?? 'N/A' }}<br>
                            <strong>Assigned To:</strong> {{ $ticket->assignedTo?->name ?? 'Unassigned' }}
                        </div>
                    </td>
                    <td class="px-4 py-2">
                        @php
                            $priorityColor = match($ticket->priority) {
                                'High' => 'bg-red-500 text-white',
                                'Medium' => 'bg-yellow-400 text-black',
                                default => 'bg-green-500 text-white',
                            };
                        @endphp
                        <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $priorityColor }}">
                            {{ $ticket->priority }}
                        </span>
                    </td>
                    <td class="px-4 py-2">
                        <span class="px-3 py-1 rounded-full text-sm font-semibold
                            @if($ticket->status == 'Open') bg-blue-500 text-white
                            @else bg-gray-400 text-white
                            @endif">
                            {{ $ticket->status }}
                        </span>
                    </td>
                    <td class="px-4 py-2">
                        <a href="{{ route('support-tickets.show', $ticket->id) }}" class="text-blue-500 hover:underline" onclick="event.preventDefault(); openTicketModal({{ $ticket->id }})">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination Links -->
    <div class="mt-4">
        {{ $tickets->links() }}
    </div>

    <!-- View Ticket Modal -->
    <div id="viewTicketModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg w-1/3 shadow-lg">
            <h2 class="text-xl font-bold mb-4">View Ticket</h2>
            <div class="mb-4">
                <label class="block text-sm font-medium">Ticket Title:</label>
                <input type="text" id="ticketTitle" class="w-full px-3 py-2 border rounded-lg bg-gray-200" readonly>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Description:</label>
                <textarea id="ticketDescription" class="w-full px-3 py-2 border rounded-lg bg-gray-200" readonly></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Ticket Priority:</label>
                <input type="text" id="ticketPriority" class="w-full px-3 py-2 border rounded-lg bg-gray-200" readonly>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Assign Ticket To:</label>
                <input type="text" id="assignedTo" class="w-full px-3 py-2 border rounded-lg bg-gray-200" readonly>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Ticket Status:</label>
                <input type="text" id="ticketStatus" class="w-full px-3 py-2 border rounded-lg bg-gray-200" readonly>
            </div>
            <div class="flex justify-end">
                <button type="button" onclick="closeModal('viewTicketModal')" class="px-4 py-2 bg-gray-300 rounded-lg">Close</button>
            </div>
        </div>
    </div>

    <!-- JavaScript for Fetching Ticket Data -->
    <script>
        function openTicketModal(ticketId) {
            fetch(`/support-tickets/${ticketId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById("ticketTitle").value = data.title;
                    document.getElementById("ticketDescription").value = data.description;
                    document.getElementById("ticketPriority").value = data.priority;
                    document.getElementById("assignedTo").value = data.assigned_to?.name ?? 'Not Assigned';
                    document.getElementById("ticketStatus").value = data.status;

                    openModal("viewTicketModal");
                })
                .catch(error => console.error("Error fetching ticket:", error));
        }

        function openModal(id) {
            let modal = document.getElementById(id);
            if (modal) {
                modal.classList.remove('hidden');
            } else {
                console.error(`Modal with ID "${id}" not found`);
            }
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }
    </script>
</div>
@endsection