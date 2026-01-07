@extends('layouts.dashboard')

@section('content')
<style>
    /* Sticky header */
    .table-container {
        max-height: 600px; /* Adjust height based on preference */
        overflow-y: auto;
    }
    thead tr th {
        position: sticky;
        top: 0;
        background: #f8f8f8;
        z-index: 10;
    }
    .tooltip {
    position: relative;
    cursor: pointer;
}

 tbody tr {
        font-size: 14px;
    
    }




.tooltip .tooltiptext {
    visibility: hidden;
    width: 250px;
    background-color: #555;
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
    /* Alternating row colors */
    tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    /* Tooltip */
    .tooltip {
        position: relative;
        display: inline-block;
        cursor: pointer;
    }
    .tooltip .tooltiptext {
        visibility: hidden;
        width: 200px;
        background-color: black;
        color: #fff;
        text-align: center;
        border-radius: 6px;
        padding: 5px;
        position: absolute;
        z-index: 1;
        bottom: 100%;
        left: 50%;
        margin-left: -100px;
        opacity: 0;
        transition: opacity 0.3s;
    }
    .tooltip:hover .tooltiptext {
        visibility: visible;
        opacity: 1;
    }
</style>

<!-- <div class="bg-white shadow-md rounded-lg p-4 mb-4">
    <form id="userFilterForm" action="{{ route('projects.index') }}" method="GET" class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 sm:space-x-4">
        <div class="flex items-center space-x-2">
            <label for="entriesPerPage" class="text-sm font-medium text-gray-700">Show</label>
            <select id="entriesPerPage" name="entries_per_page" class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:border-blue-500 text-sm" onchange="this.form.submit()">
                <option value="10" {{ request('entries_per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ request('entries_per_page') == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('entries_per_page') == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('entries_per_page') == 100 ? 'selected' : '' }}>100</option>
            </select>
            <span class="text-sm font-medium text-gray-700">entries</span>
        </div>
        <input type="text" id="searchInput" name="search" placeholder="Search..." value="{{ request('search') }}"
               class="border border-gray-300 rounded-lg px-4 py-2 w-full sm:w-64 focus:ring focus:border-blue-500 shadow-sm">
    </form>
</div> -->


 <!-- Filters Section -->
    <div id="filtersSection" class="hidden mt-4">
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-semibold">Project Grade</label>
                <select id="filter_grade" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
                    <option value="">Select</option>
                    <option value="A">A</option>
                    <option value="AA">AA</option>
                    <option value="AAA">AAA</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold">Project Manager</label>
                <select id="filter_manager" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
                    <option value="">-- Select --</option>
                    @foreach ($projectManagers as $manager)
                        <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                    @endforeach
                </select>
            </div>

          

            <div class="pt-6">
                <button class="bg-blue-600 text-white px-4 py-2 rounded">Apply</button>
            </div>
        </div>
    </div>




  
    <form method="GET" action="{{ route('projects.index') }}" class="mb-6 grid grid-cols-1 md:grid-cols-7 gap-4 items-end">
    {{-- Project Manager --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Project Manager</label>
        <select name="project_manager_id" class="w-5/6  border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All</option>
            @foreach($projectManagers as $manager)
                <option value="{{ $manager->id }}" {{ request('project_manager_id') == $manager->id ? 'selected' : '' }}>
                    {{ $manager->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Sales Person --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Sales Person</label>
        <select name="sales_person_id" class="w-5/6 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All</option>
            @foreach($salesPersons as $sales)
                <option value="{{ $sales->id }}" {{ request('sales_person_id') == $sales->id ? 'selected' : '' }}>
                    {{ $sales->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Department --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
        <select name="department_id" class="w-5/6 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                    {{ $dept->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Assign Main Employee --}}
    <div>
        <label for="assign_main_employee_id" class="block text-sm font-medium text-gray-700 mb-1">Assign Main Employee</label>
        <select name="assign_main_employee_id" id="assign_main_employee_id" class="w-5/6 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All</option>
            @foreach($employees as $employee)
                <option value="{{ $employee->id }}" {{ request('assign_main_employee_id') == $employee->id ? 'selected' : '' }}>
                    {{ $employee->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Project Status --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Project Status</label>
        <select name="project_status" class="w-5/6 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:border-blue-500">
            <option value="">All</option>
            <option value="New" {{ request('project_status') == 'New' ? 'selected' : '' }}>New</option>

            <option value="Complete" {{ request('project_status') == 'Complete' ? 'selected' : '' }}>Complete</option>
            <option value="Hold" {{ request('project_status') == 'Hold' ? 'selected' : '' }}>Hold</option>
            <option value="Paused" {{ request('project_status') == 'Paused' ? 'selected' : '' }}>Paused</option>
            <option value="Working" {{ request('project_status') == 'Working' ? 'selected' : '' }}>Working</option>
            <option value="Issues" {{ request('project_status') == 'Issues' ? 'selected' : '' }}>Issues</option>
            <option value="Temp Hold" {{ request('project_status') == 'Temp Hold' ? 'selected' : '' }}>Temp Hold</option>
            <option value="Rehire" {{ request('project_status') == 'Rehire' ? 'selected' : '' }}>Rehire</option>

        </select>
    </div>
    {{-- Client Type --}}
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Client Type</label>
    <select name="client_type" class="w-5/6 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:border-blue-500">
        <option value="">All</option>
        <option value="New Client" {{ request('client_type') == 'New Client' ? 'selected' : '' }}>New Client</option>
        <option value="Old Client" {{ request('client_type') == 'Old Client' ? 'selected' : '' }}>Old Client</option>
    </select>
</div>

{{-- Project Grade --}}
<div>
    <label class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">Project Grade</label>
    <select name="project_grade" class="w-5/6 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">All</option>
        <option value="A" {{ request('project_grade') == 'A' ? 'selected' : '' }}>A</option>
        <option value="AA" {{ request('project_grade') == 'AA' ? 'selected' : '' }}>AA</option>
        <option value="AAA" {{ request('project_grade') == 'AAA' ? 'selected' : '' }}>AAA</option>
    </select>
</div>

{{-- Select Status --}}
<!-- <div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Select Status</label>
    <select name="select_status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">All</option>
        <option value="Complete" {{ request('select_status') == 'Complete' ? 'selected' : '' }}>Complete</option>
        <option value="Temp Hold" {{ request('select_status') == 'Temp Hold' ? 'selected' : '' }}>Temp Hold</option>
        <option value="Paused" {{ request('select_status') == 'Paused' ? 'selected' : '' }}>Paused</option>
        <option value="Working" {{ request('select_status') == 'Working' ? 'selected' : '' }}>Working</option>
        <option value="Issues" {{ request('select_status') == 'Issues' ? 'selected' : '' }}>Issues</option>
        <option value="Closed" {{ request('select_status') == 'Closed' ? 'selected' : '' }}>Closed</option>
    </select>
</div> -->
{{-- Select Month --}}
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Select Month</label>
    <select name="project_month" class="w-5/6 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">All</option>
        @for ($m = 1; $m <= 12; $m++)
            <option value="{{ $m }}" {{ request('project_month') == $m ? 'selected' : '' }}>
                {{ DateTime::createFromFormat('!m', $m)->format('F') }}
            </option>
        @endfor
    </select>
</div>

{{-- Select Year --}}
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Select Year</label>
    <select name="project_year" class="w-5/6 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">All</option>
        @for ($year = now()->year; $year >= 2020; $year--)
            <option value="{{ $year }}" {{ request('project_year', now()->year) == $year ? 'selected' : '' }}>
                {{ $year }}
            </option>
        @endfor
    </select>
</div>

<div>
    <label for="pending_payment" class="block text-sm font-medium text-gray-700 mb-1">Filter by Payment Status</label>
    <select name="pending_payment" id="pending_payment" class="w-5/6 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">All</option>
        <option value="1" {{ request('pending_payment') == '1' ? 'selected' : '' }}>Pending Payment</option>
    </select>
</div>



{{-- Business Type --}}
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Business Type</label>
    <select name="business_type" class="w-5/6 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">All</option>
        <option value="Startup" {{ request('business_type') == 'Startup' ? 'selected' : '' }}>Startup</option>
        <option value="Small" {{ request('business_type') == 'Small' ? 'selected' : '' }}>Small</option>
        <option value="Mid-level" {{ request('business_type') == 'Mid-level' ? 'selected' : '' }}>Mid-level</option>
        <option value="Enterprise" {{ request('business_type') == 'Enterprise' ? 'selected' : '' }}>Enterprise</option>
    </select>
</div>

{{-- Country --}}
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
    <select name="country_id" class="w-5/6 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">All</option>
        @foreach($countries as $country)
            <option value="{{ $country->id }}" {{ request('country_id') == $country->id ? 'selected' : '' }}>
                {{ $country->name }}
            </option>
        @endforeach
    </select>
</div>

{{-- Project Main Category --}}
 <!-- <div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Project Main Category</label>
    <select name="project_category_id" id="main_category1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">All</option>
        @foreach($mainCategories as $mainCategory)
            <option value="{{ $mainCategory->id }}" {{ request('project_category_id') == $mainCategory->id ? 'selected' : '' }}>
                {{ $mainCategory->name }}
            </option>
        @endforeach
    </select>
</div>

{{-- Project Sub Category --}}
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Project Sub Category</label>
    <select name="project_subcategory_id" id="sub_category" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" {{ request('project_category_id') ? '' : 'disabled' }}>
        <option value="">All</option>
        @if(request('project_category_id') && isset($subCategories))
            @foreach($subCategories as $subCategory)
                <option value="{{ $subCategory->id }}" {{ request('project_subcategory_id') == $subCategory->id ? 'selected' : '' }}>
                    {{ $subCategory->name }}
                </option>
            @endforeach
        @endif
    </select>
</div>  -->


    {{-- Filter Button --}}
    <div class="flex">
        <button type="submit" class="w-5/6 py-2 px-4 rounded-lg shadow text-white font-medium rounded-lg shadow-md 
            bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600
            hover:from-teal-500 hover:via-teal-600 hover:to-teal-700
            focus:outline-none">
            Filter
        </button>


    </div>
</form>




    <div class="container mx-auto">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">Project Management</h2>
        @if (
            auth()->user()->hasRole('Admin') || 
            (!auth()->user()->hasRole('Sales Team') && !auth()->user()->hasRole('Team Lead'))
        )
        <button onclick="openModal('projectFormModal')" class="bg-black hover:bg-gray-900 text-white px-4 py-2 rounded-lg shadow-md transition-all flex items-center space-x-2">
             <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 16.875h3.375m0 0h3.375m-3.375 0V13.5m0 3.375v3.375M6 10.5h2.25a2.25 2.25 0 0 0 2.25-2.25V6a2.25 2.25 0 0 0-2.25-2.25H6A2.25 2.25 0 0 0 3.75 6v2.25A2.25 2.25 0 0 0 6 10.5Zm0 9.75h2.25A2.25 2.25 0 0 0 10.5 18v-2.25a2.25 2.25 0 0 0-2.25-2.25H6a2.25 2.25 0 0 0-2.25 2.25V18A2.25 2.25 0 0 0 6 20.25Zm9.75-9.75H18a2.25 2.25 0 0 0 2.25-2.25V6A2.25 2.25 0 0 0 18 3.75h-2.25A2.25 2.25 0 0 0 13.5 6v2.25a2.25 2.25 0 0 0 2.25 2.25Z"></path>
         </svg>

            Create New Project
        </button>
        

        @endif
    </div>

    <!-- Highlighted Top Stats -->
    @php
        $user = Auth::user();
    @endphp
    @if($user->hasRole('Admin'))
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 border-l-4 border-blue-500 shadow rounded-lg p-2">
                <h2 class="text-sm font-semibold text-blue-700 pt-2">Total Prediction Amount (All Projects)</h2>
                <p class="text-1xl font-extrabold text-blue-800">${{ number_format($totalPredictionAmount, 2) }}</p>
            </div>
            <div class="bg-green-50 border-l-4 border-green-500 shadow rounded-lg p-2">
                <h2 class="text-sm font-semibold text-green-700 pt-2">Total Amount Received (All Projects)</h2>
                <p class="text-1xl font-extrabold text-blue-800">${{ number_format($totalAmountReceived, 2) }}</p>
            </div>
        </div>
    @endif

    <!-- Other Stats -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <!-- <div class="bg-white shadow rounded-lg p-4"> -->
              <div class="p-4 text-white rounded-lg shadow-md
         bg-gradient-to-r from-emerald-400 via-emerald-500 to-emerald-600
         hover:from-emerald-500 hover:via-emerald-600 hover:to-emerald-700
         focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2
         transition-all duration-200 ease-in-out">
            <h2 class="text-sm font-semibold">Total Projects</h2>
            <p class="text-xl font-bold">{{ $totalProjects }}</p>
        </div>
        <div class="p-4 text-white rounded-lg shadow-md
         bg-gradient-to-r from-indigo-400 via-indigo-500 to-indigo-600
         hover:from-indigo-500 hover:via-indigo-600 hover:to-indigo-700
         focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2
         transition-all duration-200 ease-in-out">

            <h2 class="text-sm font-semibold">Prediction Amount ({{ $selectedYear }})</h2>
            <p class="text-xl font-bold">${{ number_format($predictionAmount, 2) }}</p>
        </div>
        {{-- Projects Amount Received Box — HIDDEN FOR TEAM LEAD --}}
@if(!auth()->user()->hasRole('Team Lead'))
    <div class="p-4 text-white rounded-lg shadow-md
         bg-gradient-to-r from-blue-400 via-blue-500 to-blue-600
         hover:from-blue-500 hover:via-blue-600 hover:to-blue-700
         focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2
         transition-all duration-200 ease-in-out">

        <h2 class="text-sm font-semibold">Projects Amount Received ({{ $selectedYear }})</h2>
        <p class="text-xl font-bold">${{ number_format($amountReceived, 2) }}</p>
    </div>
@endif
        </div>



  <!-- Project Listing Table -->
<div class="p-2 bg-white shadow-md rounded-lg overflow-hidden">
    <div class="overflow-x-auto">


<div class="bg-white shadow-md rounded-lg p-4 mb-4">
    <form id="userFilterForm" action="{{ route('projects.index') }}" method="GET" class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 sm:space-x-4">
        <div class="flex items-center space-x-2">
            <label for="entriesPerPage" class="text-sm font-medium text-gray-700">Show</label>
            <select id="entriesPerPage" name="entries_per_page" class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:border-blue-500 text-sm" onchange="this.form.submit()">
                <option value="10" {{ request('entries_per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ request('entries_per_page') == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('entries_per_page') == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('entries_per_page') == 100 ? 'selected' : '' }}>100</option>
            </select>
            <span class="text-sm font-medium text-gray-700">entries</span>
        </div>
        <input type="text" id="searchInput" name="search" placeholder="Search..." value="{{ request('search') }}"
               class="border border-gray-300 rounded-lg px-4 py-2 w-full sm:w-64 focus:ring focus:border-blue-500 shadow-sm">
    </form>
</div>



        <table class="w-full border-collapse border border-gray-300">
               <tr style="background-color: #0fd7c636;">
                    <th class="border border-gray-300 px-4 py-2">#</th>
                    <th class="border border-gray-300 px-4 py-2">Name/URL</th>
                    <th class="border border-gray-300 px-4 py-2">Office Details</th>
                    <th class="border border-gray-300 px-4 py-2">Price/Hours</th>
                    <th class="border border-gray-300 px-4 py-2">Action</th>
                    <th class="border border-gray-300 px-4 py-2">Added On</th>


                    <th class="border border-gray-300 px-4 py-2">Type</th>
                    <th class="border border-gray-300 px-4 py-2">Project Type</th>
                    <th class="border border-gray-300 px-4 py-2">Project Status</th>
                    <th class="border border-gray-300 px-4 py-2">Client Details</th>
                    <th class="border border-gray-300 px-4 py-2">Attachment</th>

                    <th class="border border-gray-300 px-4 py-2">Description</th>
                </tr>
            </thead>
            <tbody>
            @foreach($paginatedProjects as $key => $project)
            @php
                $currentMonth = now()->format('Y-m');
                $hasCurrentMonthPayment = $project->projectPayments->contains(function ($payment) use ($currentMonth) {
                    return \Carbon\Carbon::parse($payment->payment_date)->format('Y-m') === $currentMonth;
                });
                $rowClass = $hasCurrentMonthPayment ? 'bg-[#abeae2]' : '';
            @endphp
            <tr class="{{ $rowClass }}"
            >
                <td class="border border-gray-300 px-4 py-2">SEODIS-{{ $paginatedProjects->firstItem() + $key }}</td>
                
                <td class="border border-gray-300 px-4 py-2">
                        <strong>{{ $project->name_or_url }}</strong><br>
                        <a href="{{ $project->dashboard_url }}" target="_blank" class="text-blue-500"></a>
                    </td>
                    <td class="border border-gray-300 px-4 py-2">
    <div class="mb-2 whitespace-nowrap"><strong>Business:</strong> {{ $project->business_type }}</div>
    <div class="mb-2 whitespace-nowrap"><strong>Grade:</strong> {{ $project->project_grade }}</div>
    <div class="mb-2 whitespace-nowrap"><strong>PM:</strong> {{ optional($project->projectManager)->name ?? '-' }}</div>
    <div class="mb-2 whitespace-nowrap"><strong>TL:</strong> {{ optional($project->teamLead)->name ?? '-' }}</div> {{-- ✅ Team Lead --}}
    <div class="mb-2 whitespace-nowrap"><strong>Sales:</strong> {{ optional($project->salesPerson)->name ?? '-' }}</div>
    <div class="mb-2 whitespace-nowrap"><strong>Department:</strong> {{ optional($project->department)->name ?? '-' }}</div>
    <div class="mb-2 whitespace-nowrap"><strong>Assigned Employee:</strong> {{ optional($project->assignMainEmployee)->name ?? '-' }}</div>
    @if($project->upsell_employee_id && optional($project->upsellEmployee)->name)
        <div class="mb-2 whitespace-nowrap"><strong>Upsell Employee:</strong> {{ $project->upsellEmployee->name }}</div>
        
    @endif
    <div class="mb-2 whitespace-nowrap">
        <strong>Can Client Rehire?</strong> {{ $project->can_client_rehire ?? 'No' }} <br>
        <strong>Rehire Date</strong> {{ $project->rehire_date ? \Carbon\Carbon::parse($project->rehire_date)->format('Y-m-d') : 'N/A' }}
        
    </div>
</td>

@php
    $receivedAmount = $project->projectPayments->sum('payment_amount');

    // 🔹 Duration: from created_at to NOW()
    $duration = $project->created_at ? $project->created_at->diff(now()) : null;
@endphp

<td class="border border-gray-300 px-4 py-2">
    <div class="mb-3 whitespace-nowrap">
        <b>Price:</b> ${{ number_format($project->display_price ?? 0, 2) }}
    </div>

    <div class="mb-3 whitespace-nowrap">
        <b>Content Price:</b> {{ $project->content_price ?? '0' }}
    </div>

    <div class="mb-3">
        <a target="_blank" href="{{ route('project_payments.index', ['project_id' => $project->id]) }}">
            <span class="px-3 py-1 whitespace-nowrap text-white rounded-lg shadow-md
                bg-gradient-to-r from-blue-400 via-blue-500 to-blue-600
                hover:from-blue-500 hover:via-blue-600 hover:to-blue-700
                focus:outline-none inline-flex items-center gap-2">
                Received Price: {{ $receivedAmount }}
            </span>
        </a>
    </div>

    <div class="mb-3 whitespace-nowrap">
        <b>Hours:</b> {{ $project->display_hours ?? 'N/A' }}
    </div>

    <div>
        @if($duration)
            <span class="px-3 py-1 whitespace-nowrap text-white rounded-lg shadow-md
                bg-gradient-to-r from-blue-400 via-blue-500 to-blue-600
                hover:from-blue-500 hover:via-blue-600 hover:to-blue-700
                focus:outline-none inline-flex items-center gap-2">
                Project Duration:
                @if($duration->y > 0)
                    {{ $duration->y }} year(s)
                @endif
                @if($duration->m > 0)
                    {{ $duration->m }} month(s)
                @endif
                {{ $duration->d }} day(s)
            </span>
        @else
            <span class="text-gray-400 whitespace-nowrap">Project Duration: N/A</span>
        @endif
    </div>
</td>









   
<td class="border border-gray-300 px-4 py-2">




    <div class="flex flex-wrap gap-2">
        <a href="{{ route('projects.status', $project->id) }}"
           class="px-3 py-1 whitespace-nowrap text-white rounded-lg shadow-md
         bg-gradient-to-r from-emerald-400 via-emerald-500 to-emerald-600
         hover:from-emerald-500 hover:via-emerald-600 hover:to-emerald-700
         focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2
         inline-flex items-center gap-2 transition-all duration-200 ease-in-out' }}">

            


            <!-- Update Status Icon (refresh) -->
            <!-- <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M4 4v5h.582M20 20v-5h-.581M5.582 9A7 7 0 0119 15.418M18.418 15A7 7 0 015.582 9"/>
            </svg> -->
            Update Status
        </a>

        <a href="{{ route('project_monthly_reports.index', ['project_id' => $project->id]) }}"
           class="px-3 py-1 whitespace-nowrap text-white rounded-lg shadow-md
         bg-gradient-to-r from-indigo-400 via-indigo-500 to-indigo-600
         hover:from-indigo-500 hover:via-indigo-600 hover:to-indigo-700
         focus:outline-none inline-flex items-center gap-2">
            <!-- Report Icon (document) -->
            <!-- <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12h6m-6 4h6m-3-8h.01M5 7h14a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2z"/>
            </svg> -->
            Monthly Report
        </a>

        <a href="{{ route('project_payments.index', ['project_id' => $project->id]) }}"
           class="px-3 py-1 whitespace-nowrap text-white rounded-lg shadow-md
         bg-gradient-to-r from-blue-400 via-blue-500 to-blue-600
         hover:from-blue-500 hover:via-blue-600 hover:to-blue-700
         focus:outline-none inline-flex items-center gap-2">
            <!-- Payment Icon (credit card) -->
            <!-- <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <rect width="20" height="14" x="2" y="5" rx="2" ry="2"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M2 10h20"/>
            </svg> -->
            Payment Details
        </a>

        <a href="{{ route('projects.show', $project->id) }}"
           class="p-2 rounded bg-[#313a3ed6] text-white hover:bg-[#313a3ed6] hover:scale-110 active:scale-95 transition-transform duration-200 shadow-md inline-block"
           title="View Project">
            <!-- Eye Icon -->
         <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
                     </svg>

        </a>

        <a href="{{ route('projects.edit.page', $project->id) }}"
           class="p-2 rounded bg-[#2dd4bf] text-white hover:bg-[#2dd4bf] hover:scale-110 active:scale-95 transition-transform duration-200 shadow-md">
            <!-- Edit Icon (pencil) -->
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"></path>
                     </svg>

        </a>
        <form action="{{ route('projects.duplicate', $project->id) }}" method="POST" style="display:inline-block;">
    @csrf
   <button type="submit" 
    onclick="return confirm('Are you sure you want to duplicate this project?')" 
    class="p-2 rounded bg-[#518c44] text-white 
           hover:bg-[#518c44] hover:scale-110 active:scale-95 
           transition-transform duration-200 shadow-md"
>
    <!-- Smaller Duplicate Icon -->
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" 
         viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" 
         class="w-4 h-4">
      <path stroke-linecap="round" stroke-linejoin="round" 
            d="M15 12.75H8.25A2.25 2.25 0 0 1 6 10.5V3.75A2.25 2.25 0 0 1 8.25 1.5H15a2.25 2.25 0 0 1 2.25 2.25v6.75A2.25 2.25 0 0 1 15 12.75z" />
      <path stroke-linecap="round" stroke-linejoin="round" 
            d="M18 6.75h2.25A2.25 2.25 0 0 1 22.5 9v10.5A2.25 2.25 0 0 1 20.25 21.75H9.75A2.25 2.25 0 0 1 7.5 19.5V17.25" />
    </svg>
</button>

       
    </button>
</form>

@if(auth()->check() && auth()->user()->hasRole('Admin'))
    <form action="{{ route('projects.destroy', $project->id) }}" 
          method="POST" 
          style="display:inline-block;" 
          onsubmit="return confirm('WARNING: This will PERMANENTLY delete the project and ALL related data. Continue?');">
        @csrf
        @method('DELETE')
        <button type="submit" 
                class="p-2 rounded bg-red-600 text-white 
                       hover:bg-red-800 hover:scale-110 active:scale-95 
                       transition-all duration-200 shadow-lg" 
                title="Permanently Delete Project">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" 
                 stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" 
                      d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
            </svg>
        </button>
    </form>
@endif
    </div>
</td>
<td class="border border-gray-300 px-4 py-2 whitespace-nowrap text-gray-600">
    {{ $project->created_at->format('d-M-Y') }}
</td>
                    <td class="border border-gray-300 px-4 py-2">
    <div class="mb-2 whitespace-nowrap"><strong>Project Type:</strong> {{ $project->project_type }}</div>
    <div class="mb-2 whitespace-nowrap"><strong>Report:</strong> {{ $project->report_type }}</div>
    <div class="whitespace-nowrap"><strong>Client Type:</strong> {{ $project->client_type }}</div>
</td>

                    <td class="border border-gray-300 px-4 py-2">
                        <strong>Category:</strong> {{ optional($project->projectCategory)->name ?? '-' }}<br>
                        <strong>Sub Category:</strong> {{ optional($project->projectSubCategory)->name ?? '-' }}<br>
                        <strong>Country:</strong> {{ optional($project->country)->name ?? '-' }}
                    </td>
                    @php
                        $statusColors = [
                            'complete'   => 'text-green-600 font-bold',
                            'working'    => 'text-green-600 font-bold',
                            'hold'       => 'text-yellow-600 font-bold',
                            'paused'     => 'text-purple-600 font-bold',
                            'issues'     => 'text-red-600 font-bold',
                            'temp hold'  => 'text-orange-600 font-bold',
                            'closed'     => 'text-gray-600 font-bold',
                        ];
                        $statusRaw = $project->project_status ?? 'working';
                        $status = strtolower(trim($statusRaw));
                        $statusClass = $statusColors[$status] ?? 'text-gray-500 font-bold';
                    @endphp
                    <td class="border border-gray-300 px-4 py-2">
                        <span class="{{ $statusClass }}">
                            {{ ucfirst($statusRaw) }}
                        </span>
                        @if($project->reason_description)
        <div class="mt-2 p-2 bg-gray-100 rounded-lg text-sm text-gray-600 mb-2 whitespace-nowrap">
            <strong>Message:</strong> {{ $project->reason_description }}
        </div>
        <div><strong>Status Updated At:</strong> {{ $project->status_date ? \Carbon\Carbon::parse($project->status_date)->format('d-F-Y') : 'N/A' }}</div>
    @endif
                    </td>
                    <td class="border border-gray-300 px-4 py-2">
                        <strong>Name:</strong> {{ $project->client_name }}<br>
                        <strong>Email:</strong> {{ $project->client_email }}<br>
                        <strong>Other Info:</strong>
                        <span class="tooltip">
                            {{ Str::limit($project->client_other_info, 20) }}
                            <span class="tooltiptext">{{ $project->client_other_info }}</span>
                        </span>
                    </td>


                   
                 
                    <td class="py-4 px-4 text-sm space-y-1 max-w-xs break-words border border-gray-300">
    @php
        $pmAttachments = $project->attachments ?? [];
        $salesAttachments = $project->saleTeamAttachments ?? [];
    @endphp

    {{-- PM Attachments --}}
    @foreach ($pmAttachments as $attachment)
        <a href="{{ asset('storage/' . $attachment->file_path) }}"
           target="_blank"
           class="text-blue-600 underline block text-xs truncate"
           title="{{ basename($attachment->file_path) }}">
           📎 {{ basename($attachment->file_path) }}
           <span class="text-gray-400 text-xxs">(PM)</span>
        </a>
    @endforeach

    {{-- Sales Team Attachments --}}
    @foreach ($salesAttachments as $attachment)
        <a href="{{ asset('storage/' . $attachment->file_path) }}"
           target="_blank"
           class="text-green-600 underline block text-xs truncate"
           title="{{ basename($attachment->file_path) }}">
           📎 {{ basename($attachment->file_path) }}
           <span class="text-gray-400 text-xxs">(Sales)</span>
        </a>
    @endforeach

    @if (count($pmAttachments) + count($salesAttachments) === 0)
        <span class="text-gray-500 text-xs">No attachments</span>
    @endif

    {{-- Upload Button (only for PMs) --}}
    <a href="{{ route('projects.attachments.create', $project->id) }}"
       class="px-3 py-1 whitespace-nowrap text-white rounded-lg shadow-md
         bg-gradient-to-r from-indigo-400 via-purple-500 to-pink-500
         hover:from-indigo-500 hover:via-purple-600 hover:to-pink-600
         focus:outline-none inline-flex items-center gap-2">
        Add Attachment
    </a>
</td>


<td class="border border-gray-300 px-4 py-2 whitespace-pre-line align-top text-sm" style="max-height: 100px; overflow-y: auto; min-width: 300px; line-height: 1.4;">
    <div class="description-container flex items-center flex-wrap">
        <span class="description-text">{!! nl2br(e(Str::words($project->display_description, 10, '...'))) !!}</span>
        @if (str_word_count($project->display_description) > 10)
            <button class="view-description-btn  px-3 py-1 whitespace-nowrap text-white rounded-lg shadow-md
                bg-gradient-to-r from-blue-400 via-blue-500 to-blue-600
                hover:from-blue-500 hover:via-blue-600 hover:to-blue-700
                focus:outline-none inline-flex items-center gap-2 mt-2" 
                    data-full-description="{!! e(nl2br($project->display_description)) !!}">
          
                View More
            </button>
        @endif
    </div>
</td>



                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
 <!-- SUCCESS MODAL -->
<div id="successModal"
     class="hidden fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center z-50">

    <div id="successModalBox"
         class="bg-white rounded-lg shadow-xl w-[420px] p-6 border border-gray-300">

        <!-- Header -->
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 flex justify-center items-center bg-green-600 text-white rounded-full text-xl">
                ✓
            </div>
            <h2 class="text-xl font-semibold text-gray-800">Success</h2>
        </div>

        <!-- Message -->
        <p id="successMessage" class="text-gray-700 text-base text-left mb-5"></p>

        <!-- Footer -->
        <div class="flex justify-end">
            <button onclick="closeSuccessModal()"
                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                OK
            </button>
        </div>

    </div>
</div>



<!-- Modal (Add this once, outside the table, e.g., before the pagination div) -->
<div id="description-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 max-w-2xl w-full max-h-[80vh] overflow-y-auto shadow-xl">
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h2 class="text-xl font-bold text-gray-800">Full Project Description</h2>
            <button id="close-modal" class="text-gray-500 hover:text-gray-700 p-1 rounded">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div id="modal-description-content" class="text-sm text-gray-700 whitespace-pre-line"></div>
    </div>
</div>
    <!-- Pagination -->
    <div class="mt-4">
        {{ $paginatedProjects->links() }}
    </div>
</div>

<!-- Add/Edit Project Modal -->
<div id="projectFormModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg w-full max-w-5xl max-h-[90vh] overflow-y-auto relative [scrollbar-width:thin] [scrollbar-color:#9ca3af_#e5e7eb]">
        <button onclick="closeModal('projectFormModal')" class="absolute top-3 right-3 bg-black text-white text-2xl hover:bg-gray-800 rounded-full w-8 h-8 flex items-center justify-center">&times;</button>
        <h2 class="text-xl font-bold mb-4 text-center bg-[#14b8a6f2] text-white p-[10px] rounded">Add Project</h2>

        <form id="projectForm" method="POST" action="{{ route('projects.store') }}">
        <div class="grid grid-cols-2 gap-4">
                <!-- Project Name/URL -->
                <div>
                    <label class="mb-[3px] inline-block">Project Name/URL</label>
                    <input type="text" name="name_or_url" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none">
                </div>

                <!-- Dashboard URL -->
                <div>
                    <label class="mb-[3px] inline-block">Dashboard URL(google sheet url):</label>
                    <input type="url" name="dashboard_url" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none">
                </div>

                <!-- Description -->
                <div class="col-span-2">
                    <label class="mb-[3px] inline-block">Description</label>
                    <textarea name="description" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none"></textarea>
                </div>

                <!-- Project Grade -->
                <div>
                    <label class="mb-[3px] inline-block">Project Grade</label>
                    <select name="project_grade" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
                    <option value="">Select Project Grade</option>

                        <option value="A">A</option>
                        <option value="AA">AA</option>
                        <option value="AAA">AAA</option>
                    </select>
                </div>

                <!-- Business Type -->
                <div>
                    <label class="mb-[3px] inline-block">Business Type</label>
                    <select name="business_type" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
                    <option value="">Select Business Type</option>

                        <option value="Startup">Startup</option>
                        <option value="Small">Small</option>
                        <option value="Mid-level">Mid-level</option>
                        <option value="Enterprise">Enterprise</option>
                    </select>
                </div>

                <div>
    <label class="mb-[3px] inline-block">Project Main Category</label>
    <select id="main_category" name="project_category_id" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
        <option value="">Select Main Category</option>
        @foreach($mainCategories as $mainCategory)
            <option value="{{ $mainCategory->id }}">{{ $mainCategory->name }}</option>
        @endforeach
    </select>
</div>

<!-- Sub Category -->
<div>
    <label class="mb-[3px] inline-block"> Project Sub Category</label>
    <select id="sub_category" name="project_subcategory_id" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0" disabled>
        <option value="">Select Subcategory</option>
    </select>
</div>

                <!-- Country -->
                <div>
                    <label class="mb-[3px] inline-block">Country</label>
                    <select name="country_id" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
                    <option value="">Select Country</option>

                        @foreach($countries as $country)
                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                        @endforeach
                    </select>
                </div>

         <!-- Task Phases -->
<div>
    <label class="mb-2 inline-block font-semibold text-gray-700">Task Phases</label>
    <div class="border p-3 rounded max-h-40 overflow-y-auto flex flex-wrap gap-4 text-sm overflow-y-auto relative [scrollbar-width:thin] [scrollbar-color:#9ca3af_#e5e7eb">
        @foreach($taskPhases as $taskPhase)
            <label class="flex items-center space-x-2 px-3 py-1 bg-gray-100 rounded-lg shadow-sm hover:bg-gray-200 cursor-pointer">
                <input type="checkbox" name="task_phases[]" value="{{ $taskPhase->id }}" class="form-checkbox text-indigo-600 rounded">
                <span>{{ $taskPhase->title }}</span>
            </label>
        @endforeach
    </div>
</div>



                <!-- Project Manager -->
                <div>
    <label class="mb-[3px] inline-block">Project Manager</label>
    <select name="project_manager_id" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
        <option value="">Select</option>
        @foreach($projectManagers as $manager)
            <option value="{{ $manager->id }}"
                @if($loggedInUser->hasRole('Project Manager') && $loggedInUser->id == $manager->id) selected @endif>
                {{ $manager->name }}
            </option>
        @endforeach
    </select>
</div>

                  <!-- Team Lead -->
<div>
    <label class="mb-[3px] inline-block">Team Lead</label>
    <select name="team_lead_id" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
        <option value="">Select Team Lead</option>
        @foreach($teamLeads as $teamLead)
            <option value="{{ $teamLead->id }}">{{ $teamLead->name }}</option>
        @endforeach
    </select>
</div>
<!-- Assign Main Employee (Searchable like Add More Employees) -->
<div class="col-span-2 mb-4">
    <label class="mb-[3px] inline-block">Assign Main Employee</label>

    <!-- Search Box -->
    <input type="text" id="assign_main_employee_search" placeholder="Search employee..."
           class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none" autocomplete="off">

    <!-- Employee Options (clickable labels) -->
    <div id="assign_main_employee_list" class="border p-2 rounded max-h-64 overflow-y-auto flex flex-wrap gap-2 relative [scrollbar-width:thin] [scrollbar-color:#9ca3af_#e5e7eb">
        <!-- "None" option to unselect -->
        <label class="inline-flex items-center px-4 py-2 bg-gray-200 rounded cursor-pointer hover:bg-gray-300 text-sm">
            <input type="radio" class="employee-radio" name="assign_main_employee_id" value="" style="margin-right: 10px;">
            None
        </label>
        @foreach($employees as $employee)
            <label class="text-sm bg-gray-200 text-black px-4 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 hover:bg-teal-400 hover:text-white bg-blue-500 text-white" 
                   data-id="{{ $employee->id }}">
                <input type="radio" class="employee-radio" name="assign_main_employee_id" 
                       value="{{ $employee->id }}" 
                       {{ old('assign_main_employee_id') == $employee->id ? 'checked' : '' }}
                       style="margin-right: 10px;">
                {{ $employee->name }}
            </label>
        @endforeach
    </div>
</div>

<!-- Upsell Employee -->

                <div>
    <label class="mb-[3px] inline-block">Assign Upsell Employee</label>
    <select name="upsell_employee_id" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
        <option value="">Select</option>
        @foreach($users as $user)
            <option value="{{ $user->id }}" {{ old('upsell_employee_id', $project->upsell_employee_id ?? '') == $user->id ? 'selected' : '' }}>
                {{ $user->name }}
            </option>
        @endforeach
    </select>
</div>
<!-- Content Pricing -->
<div class="col-md-6">
    <label class="mb-[3px] inline-block mb-2">📝 Content Pricing</label>
    
    <div class="grid grid-cols-3 gap-2 bg-gray-100 p-2 rounded text-sm font-semibold text-gray-700">
        <div></div>
        <div>Type</div>
        <div>Quantity</div>
    </div>

    <div class="grid grid-cols-3 gap-2 border border-t-0 rounded-b p-3 bg-white">
        @php $types = ['website blogs', 'guest posts', 'social media posts']; @endphp
        @foreach ($types as $type)
        <div class="flex items-center">
            <input type="checkbox" id="type_{{ $type }}" name="content_type[]" value="{{ $type }}" class="h-4 w-4 text-blue-600">
        </div>
        <div class="flex items-center capitalize text-sm">
            <label for="type_{{ $type }}">{{ $type }}</label>
        </div>
        <div>
            <input type="number" name="content_quantity[]" value="0" min="0"
                class="w-full text-sm border-gray-300 rounded px-2 py-1" placeholder="0">
        </div>
        @endforeach
    </div>
</div>
 <!-- Assign Content Manager -->
 <div>
 <label class="mb-[3px] inline-block">Content Manager</label>

 <select name="content_manager_id" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
    <option value="">Select Content Manager</option>
    @foreach($contentManagers as $manager)
        <option value="{{ $manager->id }}">{{ $manager->name }}</option>
    @endforeach
</select>
                    </div>


                <!-- Price (USD) -->
                <div>
                    <label class="mb-[3px] inline-block">Price (USD)</label>
                    <input type="number" name="price" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none">
                </div>

                <!-- Estimated Hours -->
                <div>
                    <label class="mb-[3px] inline-block">Estimated Hours</label>
                    <input type="number" name="estimated_hours" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none">
                </div>

                <!-- Project Type -->
                <div>
    <label class="mb-[3px] inline-block">Project Type</label>
    <select name="project_type" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
        <option value="">Select Project Type</option>
        <option value="Ongoing" {{ request('project_type') === 'Ongoing' ? 'selected' : '' }}>Ongoing</option>
        <option value="One-time" {{ request('project_type') === 'One-time' ? 'selected' : '' }}>One-time</option>
    </select>
</div>

                <!-- Upwork Project Type -->
                <div>
                    <label class="mb-[3px] inline-block">Upwork Project Type</label>
                    <select name="upwork_project_type" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
                    <option value="">Select Project Type</option>

                        <option value="Hourly">Hourly</option>
                        <option value="Fixed">Fixed</option>
                    </select>
                </div>

                <!-- Client Type -->
                <div>
    <label class="mb-[3px] inline-block">Client Type</label>
    <select name="client_type" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
        <option value="">Select Client Type</option>
        <option value="New Client" {{ request('client_type') === 'New Client' ? 'selected' : '' }}>New Client</option>
        <option value="Old Client" {{ request('client_type') === 'Old Client' ? 'selected' : '' }}>Old Client</option>
    </select>
</div>
                <!-- Report Type -->
                <div>
                    <label class="mb-[3px] inline-block">Report Type</label>
                    <select name="report_type" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
                    <option value="">Select Report Type</option>

                        <option value="Weekly">Weekly</option>
                        <option value="Bi-Weekly">Bi-Weekly</option>
                        <option value="Monthly">Monthly</option>
                    </select>
                </div>

            <!-- Project Month -->
                <div>
                    <label class="mb-[3px] inline-block">Project Month</label>
                    <input type="date" name="project_month" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0"
                        value="{{ \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}">
                </div>


                <!-- Sales Person -->
                <div>
                    <label class="mb-[3px] inline-block">Sales Person</label>
                    <select name="sales_person_id" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
                    <option value="">Select Sales Person</option>
                        @foreach($salesPersons as $sales)
                            <option value="{{ $sales->id }}">{{ $sales->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Department -->

                <div>
                    <label class="mb-[3px] inline-block">Department</label>
                    <select name="department_id" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
                    <option value="">Select Department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Client Name -->
                <div>
                    <label class="mb-[3px] inline-block">Client Name</label>
                    <input type="text" name="client_name" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none">
                </div>

                <!-- Client Email -->
                <div>
                    <label class="mb-[3px] inline-block">Client Email</label>
                    <input type="email" name="client_email" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none">
                </div>

                <!-- Client Other Info -->
                <div class="col-span-2">
                    <label class="mb-[3px] inline-block">Client Other Info</label>
                    <textarea name="client_other_info" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none"></textarea>
                </div>

               
   <!-- Add More Employees -->
   <div class="col-span-2">
                    <label class="mb-[3px] inline-block">Add More Employees (these will only be able to submit dsr on this project)</label>
                    <div class="border p-2 rounded max-h-64 overflow-y-auto flex flex-wrap gap-3 relative [scrollbar-width:thin] [scrollbar-color:#9ca3af_#e5e7eb">
                        @foreach($employees as $employee)
                            <label class="inline-flex items-center px-4 py-2 bg-gray-200 rounded cursor-pointer hover:bg-teal-400 text-sm bg-blue-500 text-white" 
                                   data-id="{{ $employee->id }}">
                                <input type="checkbox" class="employee-checkbox" name="additional_employees[]" 
                                       value="{{ $employee->id }}" 
                                       {{ old('additional_employees', isset($project) && $project->additional_employees ? (is_array($project->additional_employees) ? $project->additional_employees : json_decode($project->additional_employees, true) ?? []) : []) ? 'checked' : '' }}
                                       style="margin-right: 10px;">
                                {{ $employee->name }}
                            </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('additional_employees')" class="mt-2" />
                </div>
<!-- Save Button -->
<div class="col-span-2 mt-4 text-left">

    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600
            hover:from-teal-500 hover:via-teal-600 hover:to-teal-700 text-white rounded hover:bg-blue-700 transition-all">
        Save
    </button>
         <button type="button" onclick="closeModal('userModal')" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-all">Cancel</button>

</div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('assign_main_employee_search');
    const employeeList = document.getElementById('assign_main_employee_list');
    const labels = Array.from(employeeList.querySelectorAll('label'));

    // Filter function
    function filterEmployees() {
        const q = searchInput.value.trim().toLowerCase();
        labels.forEach(label => {
            const name = label.textContent.trim().toLowerCase();
            label.style.display = name.includes(q) ? 'inline-flex' : 'none';
        });
    }

    searchInput.addEventListener('input', filterEmployees);

    // Handle radio button selection and deselection
    labels.forEach(label => {
        const input = label.querySelector('input');
        input.addEventListener('change', () => {
            // Remove highlighting from all labels
            labels.forEach(l => l.classList.remove('bg-blue-500', 'text-white'));

            // Highlight the selected label (skip "None")
            if (input.checked && input.value !== '') {
                label.classList.add('bg-blue-500', 'text-white');
            } else if (input.checked && input.value === '') {
                // If "None" is selected, clear all selections and highlighting
                labels.forEach(l => {
                    l.querySelector('input').checked = false;
                    l.classList.remove('bg-blue-500', 'text-white');
                });
                input.checked = true; // Keep "None" checked
            }
        });

        // Ensure initial state is reflected (only for checked items)
        if (input.checked && input.value !== '') {
            label.classList.add('bg-blue-500', 'text-white');
        } else {
            label.classList.remove('bg-blue-500', 'text-white');
        }
    });
});
</script>

<script>
function openModal(id) {
    const modal = document.getElementById(id);
    modal.classList.remove('hidden');

    const form = modal.querySelector('form');
    if (!form) return;

    // Store auto-selected values before resetting
    const projectManagerSelect = form.querySelector('select[name="project_manager_id"]');
    const autoPMValue = projectManagerSelect?.value;

    // Reset the form
    form.reset();

    // Manually re-select Project Manager if user is PM
    if (projectManagerSelect && autoPMValue) {
        [...projectManagerSelect.options].forEach(opt => {
            opt.selected = opt.value === autoPMValue;
        });
    }

    // Reset all other dropdowns
    form.querySelectorAll('select').forEach(select => {
        if (select.name !== 'project_manager_id') {
            select.selectedIndex = 0;
        }
    });

    // Clear checkboxes and textareas
    form.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
    form.querySelectorAll('textarea').forEach(txt => txt.value = '');

    // Remove old error messages
    modal.querySelectorAll('.error-text').forEach(el => el.remove());
}


function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

function viewProjectDetails(id) {
    alert('View project details: ' + id);
}

function editProject(id) {
    alert('Edit project: ' + id);
}

function deleteProject(id) {
    if (confirm('Are you sure you want to delete this project?')) {
        // Delete logic here
    }
}


$(document).ready(function () {
    $('#projectForm').on('submit', function (event) {
        event.preventDefault(); // Prevent the default form submission
        
        let formData = new FormData(this); // Get form data
        let url = "{{ route('projects.store') }}"; // Laravel route
        let token = "{{ csrf_token() }}"; // CSRF token for security

        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': token
            },
            beforeSend: function () {
                $('button[type="submit"]').prop('disabled', true).text('Saving...');
                $('.error-text').remove(); // Remove previous error messages
            },
            success: function (response) {
    $('button[type="submit"]').prop('disabled', false);

    if (response.success) {

        closeModal('projectFormModal');
        $('#projectForm')[0].reset();

        openSuccessModal(response.success);

        // Fast reload after success modal appears
        setTimeout(() => {
            window.location.reload();
        }, 500); // reload in 0.5 sec
    }
},
            error: function (xhr) {
                $('button[type="submit"]').prop('disabled', false).text('Save');
                let errors = xhr.responseJSON.errors;
                if (errors) {
                    $.each(errors, function (key, value) {
                        let inputField = $('[name="' + key + '"]');
                        inputField.after('<span class="error-text text-red-500 text-sm">' + value[0] + '</span>');
                    });
                }
            }
        });
    });
});





$(document).ready(function () {
    $('#main_category').on('change', function () {
        var mainCategoryId = $(this).val();

        if (mainCategoryId) {
            $.ajax({
                url: "{{ url('/api/subcategories') }}/" + mainCategoryId, // Pass mainCategoryId in URL
                type: "GET",
                success: function (response) {
                    $('#sub_category').empty().append('<option value="">Select Subcategory</option>');
                    if (response.length > 0) {
                        $.each(response, function (key, subcategory) {
                            $('#sub_category').append('<option value="' + subcategory.id + '">' + subcategory.name + '</option>');
                        });
                        $('#sub_category').prop('disabled', false);
                    } else {
                        $('#sub_category').prop('disabled', true);
                    }
                }
            });
        } else {
            $('#sub_category').empty().append('<option value="">Select Subcategory</option>').prop('disabled', true);
        }
    });
});

$(document).ready(function () {
    $('#main_category1').on('change', function () {
        var mainCategoryId = $(this).val();

        if (mainCategoryId) {
            $.ajax({
                url: "{{ url('/api/subcategories') }}/" + mainCategoryId, // Pass mainCategoryId in URL
                type: "GET",
                success: function (response) {
                    $('#sub_category').empty().append('<option value="">Select Subcategory</option>');
                    if (response.length > 0) {
                        $.each(response, function (key, subcategory) {
                            $('#sub_category').append('<option value="' + subcategory.id + '">' + subcategory.name + '</option>');
                        });
                        $('#sub_category').prop('disabled', false);
                    } else {
                        $('#sub_category').prop('disabled', true);
                    }
                }
            });
        } else {
            $('#sub_category').empty().append('<option value="">Select Subcategory</option>').prop('disabled', true);
        }
    });
});




document.addEventListener("DOMContentLoaded", function () {
    let searchInput = document.getElementById("searchInput");
    let entriesSelect = document.getElementById("entriesPerPage");
    let userFilterForm = document.getElementById("userFilterForm");

    // Debounce function to limit search requests
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Submit form on search input
    searchInput.addEventListener("keyup", debounce(function () {
        userFilterForm.submit();
    }, 500)); // 500ms debounce

    // Submit form when entries per page changes
    entriesSelect.addEventListener("change", function () {
        userFilterForm.submit();
    });
});
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('description-modal');
    const modalContent = document.getElementById('modal-description-content');
    const closeModalBtn = document.getElementById('close-modal');
    const buttons = document.querySelectorAll('.view-description-btn');

    buttons.forEach(button => {
        button.addEventListener('click', function () {
            modalContent.innerHTML = this.getAttribute('data-full-description') || 'No description available.';
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Prevent background scroll
        });
    });

    function closeModal() {
        modal.classList.add('hidden');
        modalContent.innerHTML = '';
        document.body.style.overflow = 'auto';
    }

    closeModalBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });
});
document.querySelectorAll('.employee-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function () {
        const item = this.closest('label');
        item.classList.toggle('bg-blue-500', this.checked);
        item.classList.toggle('text-white', this.checked);
    });
});

function openSuccessModal(message) {
    document.getElementById('successMessage').innerText = message;
    document.getElementById('successModal').classList.remove('hidden');

    setTimeout(() => {
        closeSuccessModal();
        window.location.reload();
    }, 1000);
}

function closeSuccessModal() {
    document.getElementById('successModal').classList.add('hidden');
}


document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.employee-checkbox').forEach(checkbox => {
        const item = checkbox.closest('label');
        if (checkbox.checked) {
            item.classList.add('bg-blue-500', 'text-white');
        } else {
            item.classList.remove('bg-blue-500', 'text-white');
        }
    });
});
</script>
@endsection
