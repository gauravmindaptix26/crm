@extends('layouts.dashboard')

@section('title', 'Manager Dashboard')

@section('content')
<style>
    .scroll-container {
        height: 90vh;
        overflow-y: auto;
        padding-right: 12px;
        scrollbar-width: thin;
        scrollbar-color: #888 #f1f1f1;
    }
    .scroll-container::-webkit-scrollbar {
        width: 8px;
    }
    .scroll-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 6px;
    }
    .scroll-container::-webkit-scrollbar-thumb {
        background-color: #888;
        border-radius: 6px;
        border: 2px solid #f1f1f1;
    }
    .scroll-container::-webkit-scrollbar-thumb:hover {
        background-color: #555;
    }

    .dashboard-card {
        background: #fff;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgb(0 0 0 / 0.05);
        transition: box-shadow 0.3s ease;
    }
    .dashboard-card:hover {
        box-shadow: 0 12px 24px rgb(0 0 0 / 0.08);
    }

    .btn-team-report {
        background-color: #2563eb;
        color: white;
        padding: 10px 22px;
        border-radius: 8px;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 4px 8px rgb(37 99 235 / 0.3);
        transition: background-color 0.3s ease, box-shadow 0.3s ease;
    }
    .btn-team-report:hover {
        background-color: #1d4ed8;
        box-shadow: 0 6px 12px rgb(29 78 216 / 0.4);
    }

    .btn-success {
        background-color: #10b981;
        border: none;
        padding: 8px 14px;
        font-weight: 600;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    .btn-success:hover {
        background-color: #059669;
    }

    .btn-mark-read {
        background-color: #6b7280;
        color: white;
        padding: 8px 14px;
        font-weight: 600;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    .btn-mark-read:hover {
        background-color: #4b5563;
    }

    thead tr th {
        background-color: #111827;
        color: #f9fafb;
        padding: 14px 16px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 3px solid #4b5563;
    }

    tbody tr {
        transition: background-color 0.2s ease;
    }
    tbody tr:hover {
        background-color: #f3f4f6;
    }

    tbody td {
        padding: 14px 16px;
        vertical-align: top;
        border-bottom: 1px solid #e5e7eb;
    }

    .breaktext {
        white-space: pre-wrap;
        line-height: 1.5;
        color: #374151;
        font-size: 0.95rem;
    }

    h2, h3 {
        color: #111827;
        letter-spacing: 0.02em;
    }
</style>

<div class="mb-6">
    <h2 class="text-3xl font-bold mb-6 tracking-tight">Dashboard Data</h2>

    <a href="{{ route('projectManager.teamReport') }}" class="btn-team-report mb-8 inline-block">
        Team Report
    </a>
    <a href="{{ route('pm.reviews.index') }}" class="btn-team-report mb-8 inline-block">
    Employee Review
    </a>
    @if(!$myTasks->isEmpty())
        <div class="grid grid-cols-12 gap-6">
            <!-- My Tasks Section -->
            <div class="col-span-12 md:col-span-6">
                <div class="scroll-container dashboard-card">
                    <h3 class="text-2xl font-semibold mb-5 flex items-center gap-2">📝 My Tasks for the Day</h3>
                    <div class="overflow-auto rounded-lg border border-gray-300 shadow-sm" style="max-height: 300px;">
                        <table class="table-fixed w-full">
                            <thead>
                                <tr>
                                    <th class="w-2/5">Task Info</th>
                                    <th class="w-3/5">Task Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($myTasks as $task)
                                    <tr>
                                        <td class="border-r border-gray-300">
                                            <p class="mb-3 font-semibold text-gray-800">{{ $task->name }}</p>
                                            <p class="mb-4 text-sm text-gray-600">
                                                Assigned By: 
                                                <span class="font-medium text-gray-700">{{ $task->createdBy->name ?? 'N/A' }}</span>
                                            </p>
                                            <a href="{{ route('task.addMessageForm', $task->id) }}" class="btn-success inline-block">
                                                ✅ Mark as Done
                                            </a>
                                        </td>
                                        <td>
                                            <div class="breaktext">
                                                {!! nl2br(e($task->description)) !!}
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
    <!-- Overdue Follow-Ups Notification Section -->
    @if(!$notifications->isEmpty())
        <div class="dashboard-card mb-8">
            <h3 class="text-2xl font-semibold mb-5 flex items-center gap-2 text-red-600">⚠️ Overdue Follow-Ups</h3>
            <!-- 🔽 Scrollable container added here -->
            <div class="overflow-auto rounded-lg border border-gray-300 shadow-sm" style="max-height: 300px;">
                <table class="table-fixed w-full">
                    <thead>
                        <tr>
                            <th class="w-1/2">Project Name</th>
                            <th class="w-1/4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notifications as $notification)
                            <tr>
                                <td class="border-r border-gray-300">
                                    <p class="font-semibold text-gray-800">{{ $notification->data['project_name'] }}</p>
                                </td>
                                <td>
                                    <a href="{{ route('notifications.read', $notification->id) }}" class="btn-mark-read inline-block">
                                        Mark as Read
                                    </a>
                                    <a href="{{ route('projects.paused') }}#project-{{ $notification->data['project_id'] }}" class="bg-red-600 hover:bg-red-700 text-white text-xs font-medium px-3 py-1.5 rounded mr-2">
                                        Send Follow-Up
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

  

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Handle "Mark as Read" clicks with AJAX
    document.querySelectorAll('.btn-mark-read').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.closest('tr').remove(); // Remove the notification row
                    if (document.querySelectorAll('.btn-mark-read').length === 0) {
                        document.querySelector('.dashboard-card').remove(); // Remove section if no notifications
                    }
                }
            })
            .catch(error => console.error('Error marking notification as read:', error));
        });
    });
});
</script>
@endsection
