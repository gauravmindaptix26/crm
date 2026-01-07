@extends('layouts.dashboard')

@section('title', 'Assigned Projects')

@section('content')

<div class="flex gap-3">
        <a href="{{ route('sales-projects.index') }}"
           class="bg-gray-500 text-white px-4 py-2.5 rounded-lg hover:bg-gray-600 transition duration-200 font-medium text-sm">
            ← Back
        </a>
</div>
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Assigned Projects</h2>
            <button class="bg-green-600 text-white px-5 py-2.5 rounded-lg hover:bg-green-700 transition duration-200 font-medium text-sm"
                    onclick="openAssignmentModal()">
                Assign Project
            </button>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r-lg shadow-sm">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg shadow-sm">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Projects Table -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Project Manager</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Team Leader</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Assigned Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Hours</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Assigned On</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($assignedProjects as $assignedProject)
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $assignedProject->projectManager?->name ?? 'Not Assigned' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $assignedProject->teamLead?->name ?? 'Not Assigned' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $assignedProject->assignedEmployee?->name ?? 'Not Assigned' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $assignedProject->hour ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $assignedProject->created_at->format('d M, Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                No assigned projects found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

      
        <!-- Modal -->
        <div id="assignmentModal" class="fixed inset-0 bg-black bg-opacity-60 z-50 hidden flex items-center justify-center transition-opacity duration-300">
            <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-lg transform transition-all duration-300 scale-95">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800">{{ isset($assignedProject) ? 'Edit' : 'Assign' }} Project</h2>
                    <button onclick="closeAssignmentModal()" class="text-gray-500 hover:text-gray-700 transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="assignmentForm" action="{{ route('assigned-projects.store') }}?project_id={{ $project_id }}" method="POST" class="space-y-4">
                    @csrf
                    @if(isset($assignedProject))
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $assignedProject->id }}">
                    @endif

                    <!-- Project ID (Hidden or Select) -->
                    @if($project_id)
                        <input type="hidden" name="project_id" value="{{ $project_id }}">
                    @else
                        <div>
                            <label for="project_id" class="block text-sm font-medium text-gray-700 mb-1">Project</label>
                            <select name="project_id" id="project_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Select Project --</option>
                                @foreach($allProjects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name_or_url }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Project Manager -->
                    <div>
                        <label for="project_manager_id" class="block text-sm font-medium text-gray-700 mb-1">Project Manager</label>
                        <select name="project_manager_id" id="project_manager_id"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Select Manager --</option>
                            @foreach($projectManagers as $user)
                                <option value="{{ $user->id }}" {{ isset($assignedProject) && $assignedProject->project_manager_id == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Team Leader -->
                    <div>
                        <label for="team_lead_id" class="block text-sm font-medium text-gray-700 mb-1">Team Leader</label>
                        <select name="team_lead_id" id="team_lead_id"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Select Team Leader --</option>
                            @foreach($teamLeaders as $user)
                                <option value="{{ $user->id }}" {{ isset($assignedProject) && $assignedProject->team_lead_id == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Employee -->
                    <div>
                        <label for="assigned_employee_id" class="block text-sm font-medium text-gray-700 mb-1">Assign Employee</label>
                        <select name="assigned_employee_id" id="assigned_employee_id"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Select Employee --</option>
                            @foreach($employees as $user)
                                <option value="{{ $user->id }}" {{ isset($assignedProject) && $assignedProject->assigned_employee_id == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Hour -->
                    <div>
                        <label for="hour" class="block text-sm font-medium text-gray-700 mb-1">Hours</label>
                        <input type="number" name="hour" id="hour"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               value="{{ isset($assignedProject) ? $assignedProject->hour : '' }}"
                               placeholder="Enter hours (optional)" required>
                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeAssignmentModal()"
                                class="bg-gray-500 text-white px-5 py-2 rounded-lg hover:bg-gray-600 transition duration-200 text-sm font-medium">
                            Cancel
                        </button>
                        <button type="submit"
                                class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition duration-200 text-sm font-medium">
                            {{ isset($assignedProject) ? 'Update' : 'Assign' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function openAssignmentModal() {
            const modal = document.getElementById('assignmentModal');
            modal.classList.remove('hidden');
            // Trigger animation
            const modalContent = modal.querySelector('div');
            modalContent.classList.remove('scale-95');
            modalContent.classList.add('scale-100');
        }

        function closeAssignmentModal() {
            const modal = document.getElementById('assignmentModal');
            const modalContent = modal.querySelector('div');
            modalContent.classList.remove('scale-100');
            modalContent.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 200); // Match transition duration
        }

        // Close modal when clicking the background
        window.onclick = function(event) {
            const modal = document.getElementById('assignmentModal');
            if (event.target === modal) {
                closeAssignmentModal();
            }
        }
    </script>
@endsection