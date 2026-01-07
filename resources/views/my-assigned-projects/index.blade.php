@extends('layouts.dashboard')

@section('content')
<style>
    .table-container {
        max-height: 600px;
        overflow-y: auto;
    }
    thead tr th {
        position: sticky;
        top: 0;
        
        z-index: 10;
    }
   
 
    .tooltip {
        position: relative;
        display: inline-block;
        cursor: pointer;
    }
    .tooltip .tooltiptext {
        visibility: hidden;
        width: 250px;
        background-color: #333;
        color: #fff;
        text-align: left;
        border-radius: 6px;
        padding: 10px;
        position: absolute;
        z-index: 1;
        bottom: 125%;
        left: 0;
        opacity: 0;
        transition: opacity 0.3s;
    }
    .tooltip:hover .tooltiptext {
        visibility: visible;
        opacity: 1;
    }
</style>

<div class="container mx-auto p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">Assigned Projects</h2>
        <!-- <button onclick="openModal('projectFormModal')" class="bg-blue-600 text-white px-4 py-2 rounded">+ Add Project</button> -->
    </div>
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
    <div class="p-4 text-white rounded-lg shadow-md
         bg-gradient-to-r from-emerald-400 via-emerald-500 to-emerald-600
         hover:from-emerald-500 hover:via-emerald-600 hover:to-emerald-700
         focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2
         transition-all duration-200 ease-in-out">
        <div class="text-sm">Total Projects</div>
        <div class="text-2xl font-bold">{{ $totalProjects }}</div>
    </div>

    <div class="p-4 text-white rounded-lg shadow-md
         bg-gradient-to-r from-indigo-400 via-indigo-500 to-indigo-600
         hover:from-indigo-500 hover:via-indigo-600 hover:to-indigo-700
         focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2
         transition-all duration-200 ease-in-out">
        <div class="text-sm">Active Projects</div>
        <div class="text-2xl font-bold">{{ $activeProjects }}</div>
    </div>

    <div class="p-4 text-white rounded-lg shadow-md
         bg-gradient-to-r from-blue-400 via-blue-500 to-blue-600
         hover:from-blue-500 hover:via-blue-600 hover:to-blue-700
         focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2
         transition-all duration-200 ease-in-out">
        <div class="text-sm">Prediction Amount</div>
        <div class="text-2xl font-bold">${{ number_format($predictionAmount, 2) }}</div>
    </div>

    <div class="w-5/6 py-2 px-4 rounded-lg shadow text-white font-medium rounded-lg shadow-md 
            bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600
            hover:from-teal-500 hover:via-teal-600 hover:to-teal-700
            focus:outline-none">
        <div class="text-sm font-semibold">(ALL) Amount Received</div>
        <div class="text-2xl font-bold">${{ number_format($amountReceived, 2) }}</div>
    </div>


</div>


    <form method="GET" action="{{ route('projects.index') }}" class="mb-6 grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
    </form>

    <div class="p-2 bg-white shadow-lg rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
    <table class="min-w-full border border-gray-300 text-gray-900 text-[15px]">
    <thead class="bg-gray-100 text-gray-900 font-semibold text-sm uppercase">
       <tr style="background-color: #0fd7c636 !important;">
            <th class="border px-5 py-3">#</th>
            <th class="border px-5 py-3">Name / URL</th>
            <th class="border px-5 py-3 text-center">Action</th>

            <th class="border px-5 py-3">Office Details</th>
            <th class="border px-5 py-3">Price</th>
            <th class="border px-5 py-3">Added On</th>
            <th class="border px-5 py-3">Type</th>
            <th class="border px-5 py-3">Project Type</th>
            <th class="border px-5 py-3">Status</th>
            <th class="border px-5 py-3">Client Details</th>
            <th class="border px-5 py-3">Description</th>
            <th class="border px-5 py-3">Attachments</th>
        </tr>
    </thead>
    <tbody class="bg-white text-gray-900">
        @forelse($projects as $index => $project)
            <tr class="">
            <td class="border px-5 py-4 font-medium">
    {{ ($projects->currentPage() - 1) * $projects->perPage() + $loop->iteration }}
</td>                <td class="border px-5 py-4">
                    <div class="font-semibold text-indigo-700">{{ $project->name_or_url }}</div>
                    <!-- <a href="{{ $project->dashboard_url }}" target="_blank" class="text-blue-700 underline font-medium">Dashboard</a> -->
                </td>
                <td class="border px-5 py-4 text-center">
                    <div class="flex gap-2 justify-center flex-wrap">
                        <a href="{{ route('project_payments.index', ['project_id' => $project->id]) }}"
                         class="px-3 py-1 whitespace-nowrap text-white rounded-lg shadow-md
          bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600
            hover:from-teal-500 hover:via-teal-600 hover:to-teal-700
            focus:outline-none">
                  Payment Details


                        </a>
                        <a href="{{ route('projects.show', $project->id) }}"
                              class="px-3 py-1 whitespace-nowrap text-white rounded-lg shadow-md bg-black hover:bg-gray-900
            focus:outline-none">
      View        
                        </a>
                    </div>


                </td>
                <td class="border px-5 py-4 leading-relaxed whitespace-nowrap">
                    <div><strong>Business:</strong> {{ $project->business_type }}</div>
                    <div><strong>PM:</strong> {{ optional($project->projectManager)->name }}</div>
                    <div><strong>Dept:</strong> {{ optional($project->department)->name }}</div>
                    <div><strong>Employee:</strong> {{ optional($project->assignMainEmployee)->name ?? 'N/A' }}</div>
                    <div><strong>Sales:</strong> {{ optional($project->salesPerson)->name ?? 'N/A' }}</div>
                </td>
                <td class="border px-5 py-4">
                    <strong>{{ $project->price }}</strong>
                </td>
                <td class="border px-5 py-4">{{ $project->created_at->format('d M, Y') }}</td>
                <td class="border px-5 py-4">{{ $project->project_grade }}</td>
                <td class="border px-5 py-4">{{ $project->project_type }}</td>
                <td class="border px-5 py-4 text-sm">
                        @php
                            $statusColors = [
                                'complete'   => 'text-green-700 bg-green-200 font-bold',
                                'working'    => 'text-teal-600 bg-teal-100 font-bold',
                                'hold'       => 'text-yellow-600 bg-yellow-100 font-bold',
                                'paused'     => 'text-purple-600 bg-purple-100 font-bold',
                                'issues'     => 'text-red-600 bg-red-100 font-bold',
                                'temp hold'  => 'text-orange-600 bg-orange-100 font-bold',
                                'closed'     => 'text-gray-600 bg-gray-100 font-bold',
                            ];
                            $statusRaw = $project->project_status ?? 'working';
                            $status = strtolower(trim($statusRaw));
                            $statusClass = $statusColors[$status] ?? 'text-gray-500 bg-gray-100 font-bold';
                        @endphp
                        <span class="inline-block px-2 py-1 text-sm rounded {{ $statusClass }}">
                            {{ $project->project_status ?? 'Working' }}
                        </span>
                    </td>
                <td class="border px-5 py-4">
                    <div class="font-semibold">{{ $project->client_name }}</div>
                    <a href="mailto:{{ $project->client_email }}" class="text-blue-700 underline font-medium">{{ $project->client_email }}</a>
                </td>
             
                <td class="border px-5 py-4 text-sm whitespace-pre-line break-words">
    <div class="description-container flex items-center flex-nowrap gap-0.25">
        <span class="description-text">{!! e(Str::words($project->description, 10, '...')) !!}</span>
        @if (str_word_count($project->description) > 10)
            <button class="view-description-btn bg-green-600 text-white text-xs font-medium px-2 py-0.5 ml-1 rounded-sm inline-flex items-center hover:bg-green-700 transition-colors duration-150" 
                    data-full-description="{!! e(nl2br($project->description)) !!}">
                <svg class="h-2 w-2 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                View More
            </button>
        @endif
    </div>
</td>
<!-- Modal (place outside table, e.g., before pagination div) -->
<div id="description-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 max-w-2xl w-full max-h-[80vh] overflow-y-auto shadow-xl">
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h2 class="text-lg font-semibold text-gray-800">Full Description</h2>
            <button id="close-modal" class="text-gray-500 hover:text-gray-700 p-1 rounded">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div id="modal-description-content" class="text-sm text-gray-700 whitespace-pre-line"></div>
    </div>
</div>


                <td class="border px-5 py-4 space-y-1 max-w-sm break-words">
                    @forelse ($project->attachments as $attachment)
                        <a href="{{ asset('storage/' . $attachment->file_path) }}"
                           target="_blank"
                           class="text-blue-700 underline block truncate"
                           title="{{ basename($attachment->file_path) }}">
                           {{ basename($attachment->file_path) }}
                        </a>
                    @empty
                        <span class="text-gray-800">No attachments</span>
                    @endforelse
                    <a href="{{ route('projects.attachments.create', $project->id) }}"
                       class="px-3 py-1 whitespace-nowrap text-white rounded-lg shadow-md
         bg-gradient-to-r from-indigo-400 via-purple-500 to-pink-500
         hover:from-indigo-500 hover:via-purple-600 hover:to-pink-600
         focus:outline-none inline-flex items-center gap-2">
                        Add Attachment
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="12" class="text-center py-6 text-gray-700">No projects found.</td>
            </tr>
        @endforelse
    </tbody>
</table>

    </div>

    <!-- Pagination -->
    <div class="mt-6 px-4">
        {{ $projects->links() }}
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('description-modal');
    const modalContent = document.getElementById('modal-description-content');
    const closeModalBtn = document.getElementById('close-modal');
    const descriptionButtons = document.querySelectorAll('.view-description-btn');

    descriptionButtons.forEach(button => {
        button.addEventListener('click', function () {
            const fullDescription = this.getAttribute('data-full-description');
            modalContent.innerHTML = fullDescription || 'No description available';
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
    });

    function closeModal() {
        modal.classList.add('hidden');
        modalContent.innerHTML = '';
        document.body.style.overflow = 'auto';
    }

    closeModalBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });
});
</script>
@endsection
