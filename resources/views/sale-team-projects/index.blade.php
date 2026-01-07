@extends('layouts.dashboard')

@section('content')
<div class="bg-white shadow-md rounded-lg p-6 mb-6 flex justify-between items-center">
    <div class="flex items-center space-x-3">
        <label for="entriesPerPage" class="text-sm font-medium text-gray-600">Show</label>
        <select id="entriesPerPage" class="border border-gray-300 rounded-md px-3 py-1.5 focus:ring focus:border-blue-500 text-sm">
            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
            <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
            <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
            <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
        </select>
        <span class="text-sm font-medium text-gray-600">entries</span>
    </div>
    <div class="flex items-center space-x-2">
        <input type="text" id="searchInput" placeholder="Search Sales Projects..." 
               class="border border-gray-300 rounded-md px-4 py-2 w-72 focus:ring focus:border-blue-500 shadow-md text-gray-700 transition"
               value="{{ request('search') }}">
        <button id="clearSearch" class="bg-gray-200 text-gray-700 px-3 py-2 rounded-md hover:bg-gray-300">Clear</button>
    </div>
</div>

<div class="p-4">
    <h1 class="text-2xl font-semibold mb-4">Sales Team Projects</h1>
    <form id="filterForm" method="GET" action="{{ route('sales-projects.index') }}" class="mb-6 flex items-center space-x-4">
        <div>
            <label for="department_id" class="block text-sm font-medium text-gray-700">Filter by Department:</label>
            <select name="department_id" id="department_id" class="border border-gray-300 rounded-lg px-4 py-2">
                <option value="">-- All Departments --</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mt-5">
            <button type="submit" class="px-3 py-1 whitespace-nowrap text-white rounded-lg shadow-md
                bg-gradient-to-r from-blue-400 via-blue-500 to-blue-600
                hover:from-blue-500 hover:via-blue-600 hover:to-blue-700
                focus:outline-none inline-flex items-center gap-2">
                Search
            </button>
        </div>
    </form>

    @if (!auth()->user()->hasRole('Project Manager'))
        <div class="flex justify-end mb-4">
            <button onclick="openProjectModal()" class="bg-black hover:bg-gray-900 text-white px-4 py-2 rounded-lg shadow-md transition-all flex items-center space-x-2">
               <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 16.875h3.375m0 0h3.375m-3.375 0V13.5m0 3.375v3.375M6 10.5h2.25a2.25 2.25 0 0 0 2.25-2.25V6a2.25 2.25 0 0 0-2.25-2.25H6A2.25 2.25 0 0 0 3.75 6v2.25A2.25 2.25 0 0 0 6 10.5Zm0 9.75h2.25A2.25 2.25 0 0 0 10.5 18v-2.25a2.25 2.25 0 0 0-2.25-2.25H6a2.25 2.25 0 0 0-2.25 2.25V18A2.25 2.25 0 0 0 6 20.25Zm9.75-9.75H18a2.25 2.25 0 0 0 2.25-2.25V6A2.25 2.25 0 0 0 18 3.75h-2.25A2.25 2.25 0 0 0 13.5 6v2.25a2.25 2.25 0 0 0 2.25 2.25Z"></path>
         </svg> Add Project
            </button>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-sm">
            <thead class="bg-gradient-to-r from-gray-100 to-gray-200 text-left text-gray-800">
                <tr>
                    <th class="py-3 px-4 border-b text-base font-semibold text-center whitespace-nowrap">Sr. No.</th>
                    <th class="py-3 px-4 border-b text-base font-semibold whitespace-nowrap">Name / URL</th>
                    <th class="py-3 px-4 border-b text-base font-semibold text-center whitespace-nowrap">Action</th>
                    <th class="py-3 px-4 border-b text-base font-semibold whitespace-nowrap">Office Details</th>
                    <th class="py-3 px-4 border-b text-base font-semibold text-center whitespace-nowrap">Price</th>
                    <th class="py-3 px-4 border-b text-base font-semibold text-center whitespace-nowrap">Added On</th>
                    <th class="py-3 px-4 border-b text-base font-semibold text-center whitespace-nowrap">Type</th>
                    <th class="py-3 px-4 border-b text-base font-semibold text-center whitespace-nowrap">Hired From</th>
                    <th class="py-3 px-4 border-b text-base font-semibold whitespace-nowrap">Client Details</th>
                    <th class="py-3 px-4 border-b text-base font-semibold whitespace-nowrap">Attachments</th>
                    <th class="py-3 px-4 border-b text-base font-semibold whitespace-nowrap">Description</th>
                </tr>
            </thead>
            <tbody class="text-gray-900 font-medium">
                @if(isset($projects) && $projects->count())
                    @foreach($projects as $project)
                        <tr class="hover:bg-gray-50 transition-all border-b border-gray-200 align-top">
                            <td class="py-4 px-4 text-center whitespace-nowrap">{{ ($projects->currentPage() - 1) * $projects->perPage() + $loop->iteration }}</td>
                            <td class="py-4 px-4 text-sm leading-relaxed max-w-xs break-words">
                                <div class="font-semibold truncate">{{ $project->name_or_url }}</div>
                                @if ($project->dashboard_url)
                                    <a href="{{ $project->dashboard_url }}" target="_blank" class="text-blue-600 underline text-xs">
                                        Dashboard Link
                                    </a>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center text-sm whitespace-nowrap">
                                <div class="flex flex-col items-center space-y-2">
                                    <button onclick="editProject({{ $project->id }})"
                                            class="p-2 rounded bg-[#2dd4bf] text-white hover:bg-[#2dd4bf] hover:scale-110 active:scale-95 transition-transform duration-200 shadow-md">
                                       <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"></path>
                     </svg>
                                    </button>
                                    @php
                                        $isAssigned = \App\Models\AssignedProject::where('project_id', $project->id)
                                            ->where('source_type', 'sale_team')
                                            ->exists();
                                        $user = auth()->user();
                                        $canAssign = !$isAssigned && (
                                            ($user->hasAnyRole(['Sales Team', 'Sales Team Manager']) && $user->id === $project->sales_person_id) ||
                                            $user->hasRole('Project Manager') ||
                                            $user->hasRole('Admin')
                                        );
                                    @endphp
                                    @if ($canAssign)
                                        <a href="{{ route('assigned-projects.index', ['project_id' => $project->id]) }}"
                                           class="flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md shadow-sm transition duration-200">
                                           ✅ <span>Assignnn</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-4 text-sm leading-relaxed max-w-xs">
                                <div><strong>Sales:</strong> {{ $project->salesPerson->name ?? '-' }}</div>
                                <div><strong>Department:</strong> {{ $project->department->name ?? '-' }}</div>
                                <div><strong>Hired From Portal:</strong> {{ $project->hired_from_portal ?? '-' }}</div>
                                <div><strong>Business Type:</strong> {{ $project->business_type ?? '-' }}</div>
                            </td>
                            <td class="py-4 px-4 text-center text-sm whitespace-nowrap">
                                ${{ $project->price_usd ?? '-' }}
                            </td>
                            <td class="py-4 px-4 text-center text-sm whitespace-nowrap">
                                {{ $project->created_at->format('d M, Y') }}
                            </td>
                            <td class="py-4 px-4 text-center text-sm whitespace-nowrap">
                                {{ $project->project_type ?? '-' }}
                            </td>
                            <td class="py-4 px-4 text-center text-sm whitespace-nowrap">
                            {{ $project->hiredFromProfile->name ?? '-' }}
                            </td>
                            <td class="py-4 px-4 text-sm leading-relaxed max-w-xs space-y-1.5 break-words">
                                <div><strong>Type:</strong> {{ $project->client_type ?? '-' }}</div>
                                <div><strong>Name:</strong> {{ $project->client_name ?? '-' }}</div>
                                <div><strong>Email:</strong> {{ $project->client_email ?? '-' }}</div>
                                <div><strong>Other Info:</strong>
                                    @if($project->client_other_info)
                                        <a href="{{ $project->client_other_info }}" class="text-blue-600 underline text-xs break-all" target="_blank">
                                            {{ Str::limit($project->client_other_info, 40) }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </div>
                                <div><strong>Behaviour:</strong> {{ $project->client_behaviour ?? '-' }}</div>
                                <div><strong>Contact Time:</strong> {{ $project->time_to_contact ?? '-' }}</div>
                                <div><strong>Keywords:</strong> {{ $project->client_keywords ?? 'No' }}</div>
                                <div><strong>Results Commitment:</strong> {{ $project->client_results_commitment ?? '-' }}</div>
                                <div><strong>Content Commitment:</strong> {{ $project->result_commitment ?? 'No' }}</div>
                                <div><strong>Loom Video:</strong> {{ $project->client_loom_video ?? 'No' }}</div>
                                <div><strong>Website Speed:</strong>
                                    @if($project->website_speed_included === 'Yes')
                                        <span class="text-green-600 font-semibold">Yes</span>
                                    @else
                                        <span class="text-red-600 font-semibold">No</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-4 text-sm space-y-1 max-w-xs break-words">
                                @forelse ($project->attachments as $attachment)
                                    <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank" class="text-blue-600 underline block text-xs truncate" title="{{ basename($attachment->file_path) }}">
                                        {{ basename($attachment->file_path) }}
                                    </a>
                                @empty
                                    <span class="text-gray-500 text-xs">No attachments</span>
                                @endforelse
                                <a href="{{ route('sales-projects.attachments.create', $project->id) }}" 
                                   class="inline-block mt-2 px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs whitespace-nowrap">
                                    Add Attachment
                                </a>
                            </td>
                            <td class="py-4 px-4 text-sm text-gray-800 align-top max-w-3xl">
    <div x-data="{ open: false }" class="leading-relaxed">
        <!-- Short version -->
        <div x-show="!open" 
             x-transition.opacity 
             class="whitespace-normal break-words">
            {!! nl2br(e(Str::limit($project->description ?? '-', 150, '...'))) !!}
        </div>

        <!-- Full version -->
        <div x-show="open" 
             x-transition.opacity 
             class="whitespace-normal break-words">
            {!! nl2br(e($project->description ?? '-')) !!}
        </div>

        <!-- View More / View Less button (only if text is long) -->
        @if($project->description && Str::length(strip_tags($project->description)) > 150)
            <button @click="open = !open"
                    class="mt-3 inline-flex items-center px-4 py-2 text-xs font-medium text-white rounded-lg shadow-md
                           bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700
                           hover:from-blue-600 hover:via-blue-700 hover:to-blue-800
                           focus:outline-none transition">
                <span x-text="open ? 'View Less' : 'View More'"></span>
                <svg x-show="!open" class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
                <svg x-show="open" class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                </svg>
            </button>
        @endif
    </div>
</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="11" class="text-center py-6 text-gray-500">
                            No projects found for the selected department.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="mt-6 pagination">
        {{ $projects->appends(request()->query())->links() }}
    </div>
</div>


<!-- Modal -->
<div id="projectModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white w-full max-w-3xl rounded-2xl shadow-lg p-6 relative">
        <button onclick="closeProjectModal()" class="absolute top-3 right-4 text-gray-500 text-2xl font-bold hover:text-gray-700">&times;</button>
        <h2 class="text-2xl font-semibold mb-6" id="projectModalTitle">Add Project</h2>

        <form id="projectForm" method="POST"  action="" class="space-y-4 max-h-[70vh] overflow-y-auto">


            @csrf

            <input type="hidden" name="_method" id="formMethod" value="POST">
          <input type="hidden" name="project_id" id="project_id">
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Hired From Portal</label>
                    <select name="hired_from_portal" id="hired_from_portal" class="form-select mt-1 block w-full">
                <option value="">-- Select Portal --</option>
                <option value="PPH">PPH</option>
                <option value="Fiver">Fiver</option>
                <option value="Upwork">Upwork</option>

            </select>
        </div>	

             

                <div>
    <label class="block text-sm font-medium mb-1">Hired From Profile</label>
    <select name="hired_from_profile_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        <option value="">Select</option>
        @foreach($hiredFroms as $profile)
            <option value="{{ $profile->id }}">{{ $profile->name }}</option>
        @endforeach
    </select>
</div>



<div>
    <label for="project_type" class="block text-sm font-medium mb-1">Project Type</label>
    <select id="project_type" name="project_type" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        <option value="">Select</option>
        <option value="Ongoing">Ongoing</option>
        <option value="One-time">One-time</option>
    </select>
</div>

           
                @php
    $loggedInUser = Auth::user();
@endphp

<div>
    <label class="block text-sm font-medium mb-1">Sales Person</label>
    <select name="sales_person_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        <option value="">Select</option>
        @foreach($salesPersons as $person)
            <option value="{{ $person->id }}"
                @if(
                    old('sales_person_id') == $person->id || 
                    (!old('sales_person_id') && $loggedInUser->hasAnyRole(['Sales Team', 'Sales Team Manager']) && $loggedInUser->id == $person->id)
                    )
                    selected
                @endif
            >
                {{ $person->name }}
            </option>
        @endforeach
    </select>
</div>


                <div>
                    <label class="block text-sm font-medium mb-1">Client Name</label>
                    <input type="text" name="client_name" class="w-full border border-gray-300 rounded-lg px-3 py-2" >
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Client Email</label>
                    <input type="email" name="client_email" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Project URL/Name</label>
                    <input type="text" name="name_or_url" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Description</label>
                    <textarea name="description" class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Price (USD)</label>
                    <input type="number" step="0.01" name="price_usd" class="w-full border border-gray-300 rounded-lg px-3 py-2" >
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Client Type</label>
                    <select name="client_type" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">Please Select</option>

                        <option value="new client">New Client</option>
                        <option value="old client">Old Client</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Business Type</label>
                    <select name="business_type" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">Please Select</option>

                        <option value="Midlevel">Midlevel</option>
                        <option value="Startup">Startup</option>
                        <option value="Small">Small</option>
                        <option value="Enterprise">Enterprise</option>
                    </select>
                </div>


                <div>
                    <label class="block text-sm font-medium mb-1">Project Month</label>
                    <input type="date" name="project_month" class="w-full border border-gray-300 rounded-lg px-3 py-2" >
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Country</label>
                    <select name="country_id" class="w-full border border-gray-300 rounded-lg px-3 py-2" >
                        <option value="">Select Country</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
    <label class="block text-sm font-medium mb-1">Department</label>
    <select name="department_id" id="department_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        <option value="">Select Department</option>
        @foreach($departments as $department)
            <option value="{{ $department->id }}"
                {{ old('department_id') == $department->id ? 'selected' : '' }}
            >
                {{ $department->name }}
            </option>
        @endforeach
    </select>
</div>

                <div>
                    <label class="block text-sm font-medium mb-1">Client Behavior</label>
                    <textarea name="client_behaviour" class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Particular Time to Contact Client, If any</label>
                    <textarea name="time_to_contact" class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
                </div>

              
                <div>
                    <label class="block text-sm font-medium mb-1">Client Communication Details (Whole Conversations): </label>
                    <textarea name="communication_details" class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
                </div>

         
                       
        <!-- Client Requirements Section -->
<div class="md:col-span-2">
    <h3 class="text-lg font-semibold border-b pb-2 mb-4">Client Requirements</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Any Specific Keyword client want to target?</label>
            <textarea name="specific_keywords" class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Any Specific Commitment For Results:</label>
            <textarea name="result_commitment" class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Any Content Commitment:</label>
            <textarea name="content_commitment" class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Any Website Development work commitment:</label>
            <textarea name="website_dev_commitment" class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Internal loom video to explain with all above communication:</label>
            <textarea name="internal_explainer_video" class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
        </div>

        <div>
    <label class="block text-sm font-medium mb-1">Website Speed Included</label>
    <select name="website_speed_included" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        <option value="">Please Select</option>
        <option value="Yes" {{ (old('website_speed_included', isset($project) ? $project->website_speed_included : '') === 'Yes') ? 'selected' : '' }}>Yes</option>
         <option value="No" {{ (old('website_speed_included', isset($project) ? $project->website_speed_included : '') === 'No') ? 'selected' : '' }}>No</option>

    </select>
</div>


</div>



            </div>

            <div class="flex justify-end mt-6 space-x-4">
                <button type="button" class="bg-gray-500 text-white px-6 py-2 rounded-md" onclick="closeProjectModal()">Cancel</button>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md">Save</button>
            </div>
        </form>
    </div>
</div>

   
<script>
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Handle entries per page change
    $('#entriesPerPage').on('change', function() {
        const perPage = $(this).val();
        const search = $('#searchInput').val();
        const departmentId = $('#department_id').val();
        updateProjects(search, perPage, departmentId, 1);
    });

    // Handle search input (debounced)
    let searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const search = $(this).val();
            const perPage = $('#entriesPerPage').val();
            const departmentId = $('#department_id').val();
            updateProjects(search, perPage, departmentId, 1);
        }, 500);
    });

    // Handle department filter form submission
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        const search = $('#searchInput').val();
        const perPage = $('#entriesPerPage').val();
        const departmentId = $('#department_id').val();
        updateProjects(search, perPage, departmentId, 1);
    });

    // Handle clear search button
    $('#clearSearch').on('click', function() {
        $('#searchInput').val('');
        const perPage = $('#entriesPerPage').val();
        const departmentId = $('#department_id').val();
        updateProjects('', perPage, departmentId, 1);
    });

    // Function to update projects with query parameters
    function updateProjects(search, perPage, departmentId, page) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', perPage);
        url.searchParams.set('page', page);
        if (search.trim()) {
            url.searchParams.set('search', search.trim());
        } else {
            url.searchParams.delete('search');
        }
        if (departmentId) {
            url.searchParams.set('department_id', departmentId);
        } else {
            url.searchParams.delete('department_id');
        }
        window.location.href = url.toString();
    }
});








$('#projectForm').on('submit', function(e) {
    e.preventDefault();

    let form = $(this);
    let method = $('#formMethod').val(); // POST or PUT
    let projectId = $('#project_id').val();

    let url = '';
    let formData = form.serialize();

    if (method === 'POST') {
        url = '{{ route("sales-projects.store") }}';
    } else {
        url = `sales-projects/${projectId}`;
        formData += '&_method=PUT'; // Spoof method manually
    }

    $.ajax({
        url: url,
        type: 'POST', // Always POST, Laravel handles _method spoofing
        data: formData,
        success: function(response) {
            alert(response.message);
            closeProjectModal();
            location.reload();
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                $('.text-red-500').remove();

                for (const key in errors) {
                    const input = $('[name="' + key + '"]');
                    if (input.length) {
                        input.after('<p class="text-red-500 text-sm mt-1">' + errors[key][0] + '</p>');
                    }
                }
            } else {
                alert("Something went wrong. Status: " + xhr.status);
            }
        }
    });
});



function openAssignedProjectsModal(projectId) {
    const modal = document.getElementById('assignedProjectsModal');
    const content = document.getElementById('assignedProjectsContent');

    // Show modal
    modal.classList.remove('hidden');

    // Load content via AJAX
    fetch(`assigned-projects?project_id=${projectId}`)
        .then(response => response.text())
        .then(html => {
            content.innerHTML = html;
        });
}







function openProjectModal() {
    document.getElementById("projectModalTitle").innerText = "Add Project";
    document.getElementById("projectForm").reset();
    document.getElementById("formMethod").value = "POST";
    document.getElementById("projectForm").action = "{{ route('sales-projects.store') }}";
    document.getElementById("project_id").value = "";
    document.getElementById("projectModal").classList.remove("hidden");
}
    function closeProjectModal() {
        document.getElementById('projectModal').classList.add('hidden');
    }

    function editProject(projectId) {
    // Fetch the project data using an AJAX request or set it via a Blade variable
    fetch(`sales-projects/${projectId}/edit`)
        .then(response => response.json())
        .then(project => {
            // Populate form fields with project data
            document.getElementById('projectModalTitle').textContent = 'Edit Project';
            
            document.getElementById('formMethod').value = 'PUT';  // Ensure it's a PUT request for update
            document.getElementById('project_id').value = project.id;

            // Set project field values
            document.querySelector('select[name="hired_from_portal"]').value = project.hired_from_portal;
            document.querySelector('select[name="hired_from_profile_id"]').value = project.hired_from_profile_id;
            document.querySelector('select[name="project_type"]').value = project.project_type;
            document.querySelector('select[name="sales_person_id"]').value = project.sales_person_id;
            document.querySelector('input[name="client_name"]').value = project.client_name;
            document.querySelector('input[name="client_email"]').value = project.client_email;
            document.querySelector('input[name="name_or_url"]').value = project.name_or_url;
            document.querySelector('textarea[name="description"]').value = project.description;
            document.querySelector('input[name="price_usd"]').value = project.price_usd;
            document.querySelector('select[name="client_type"]').value = project.client_type;
            document.querySelector('select[name="business_type"]').value = project.business_type;
            document.querySelector('input[name="project_month"]').value = project.project_month;
            document.querySelector('select[name="country_id"]').value = project.country_id;
            $('[name="department_id"]').val(project.department_id); // 👈 set selected department
            document.querySelector('textarea[name="client_behaviour"]').value = project.client_behaviour;
            document.querySelector('textarea[name="time_to_contact"]').value = project.time_to_contact;
            document.querySelector('textarea[name="specific_keywords"]').value = project.specific_keywords;
            document.querySelector('textarea[name="internal_explainer_video"]').value = project.internal_explainer_video;
            document.querySelector('textarea[name="content_commitment"]').value = project.content_commitment;
            document.querySelector('textarea[name="website_dev_commitment"]').value = project.website_dev_commitment;
            document.querySelector('textarea[name="result_commitment"]').value = project.result_commitment;
            document.querySelector('textarea[name="communication_details"]').value = project.communication_details;


            
            // Show the modal
            document.getElementById('projectModal').classList.remove('hidden');
        })
        .catch(error => console.error('Error:', error));
}



function deleteSalesProject(projectId) {
    if (!confirm('Are you sure you want to delete this project?')) {
        return;
    }

    fetch(`sales-projects/${projectId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to delete project.');
        }
        return response.json();
    })
    .then(data => {
        alert(data.message || 'Project deleted successfully!');
        const row = document.getElementById(`sales-project-${projectId}`);
        if (row) {
            row.remove();
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        alert('Something went wrong while deleting the project.');
    });
}

</script>
@endsection
