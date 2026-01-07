@extends('layouts.dashboard')

@section('content')

<div class="flex items-center justify-between mb-4">
<a href="{{ route('projects.index') }}"
class="bg-black hover:bg-gray-900 text-white px-4 py-2 rounded-lg shadow-md transition-all flex items-center space-x-2">
            ← Back to Projects
        </a>
    </div>
<div class="max-w-5xl mx-auto p-6 bg-white rounded-xl shadow">
    <h2 class="text-2xl font-bold mb-4">Edit Project</h2>

    <form action="{{ route('projects.update', $project->id) }}" method="POST">
        @csrf
        @method('PUT')
        @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
        <div class="grid grid-cols-2 gap-4">
            <!-- Project Name/URL -->
            <div>
                <label class="mb-[3px] inline-block">Project Name/URL</label>
                <input type="text" name="name_or_url" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0"
                       value="{{ old('name_or_url', $project->name_or_url) }}">
            </div>

            <!-- Dashboard URL -->
            <div>
                <label class="mb-[3px] inline-block">Dashboard URL</label>
                <input type="url" name="dashboard_url" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0"
                       value="{{ old('dashboard_url', $project->dashboard_url) }}">
            </div>

            <!-- Description -->
            <div class="col-span-2">
                <label class="mb-[3px] inline-block">Description</label>
                <textarea name="description" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">{{ old('description', $project->description) }}</textarea>
            </div>

            <!-- Project Grade -->
            <div>
                <label class="mb-[3px] inline-block">Project Grade</label>
                <select name="project_grade" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
                    <option value="">Select Project Grade</option>
                    @foreach(['A', 'AA', 'AAA'] as $grade)
                        <option value="{{ $grade }}" {{ old('project_grade', $project->project_grade) == $grade ? 'selected' : '' }}>{{ $grade }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Business Type -->
            <div>
                <label class="mb-[3px] inline-block">Business Type</label>
                <select name="business_type" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
                    <option value="">Select Business Type</option>
                    @foreach(['Startup', 'Small', 'Mid-level', 'Enterprise'] as $type)
                        <option value="{{ $type }}" {{ old('business_type', $project->business_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Main Category -->
            <div>
                <label class="mb-[3px] inline-block">Main Category</label>
                <select name="project_category_id" id="main_category" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
                    <option value="">Select Main Category</option>
                    @foreach($mainCategories as $mainCategory)
                        <option value="{{ $mainCategory->id }}" {{ old('project_category_id', $project->project_category_id) == $mainCategory->id ? 'selected' : '' }}>
                            {{ $mainCategory->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Sub Category -->
            <div>
                <label class="mb-[3px] inline-block">Sub Category</label>
                <select name="project_subcategory_id" id="sub_category" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
                    <option value="">Select Subcategory</option>
                    @foreach($subCategories as $subCategory)
                        <option value="{{ $subCategory->id }}" {{ old('project_subcategory_id', $project->project_subcategory_id) == $subCategory->id ? 'selected' : '' }}>
                            {{ $subCategory->name }}
                        </option>
                    @endforeach
                </select>
            </div>

          
             @php
    $types = ['blog', 'article', 'post'];
    $contentDetails = collect(json_decode($project->content_details, true) ?? []);
@endphp

<div class="">
    <label class="mb-[3px] inline-block">Content Price</label>
    <table class="w-full text-sm border">
        <thead>
            <tr class="text-left bg-gray-100">
                <th class="p-2">Select</th>
                <th class="p-2">Type</th>
                <th class="p-2">Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($types as $type)
                @php
                    $item = $contentDetails->firstWhere('type', $type);
                @endphp
                <tr>
                    <td class="p-2">
                        <input
                            type="checkbox"
                            name="content_type[]"
                            value="{{ $type }}"
                            {{ $item ? 'checked' : '' }}
                        >
                    </td>
                    <td class="p-2 capitalize">{{ $type }}</td>
                    <td class="p-2">
                        <input
                            type="number"
                            name="content_quantity[]"
                            value="{{ $item['quantity'] ?? 0 }}"
                            class="w-24 border rounded px-2 py-1"
                            min="0"
                        >
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

          <!-- Task Phases -->
<div>
    <label class="mb-[3px] inline-block">Task Phase</label>
    <div class="border p-3 rounded max-h-40 overflow-y-auto flex flex-wrap gap-4 text-sm relative [scrollbar-width:thin] [scrollbar-color:#9ca3af_#e5e7eb]">
        @foreach($taskPhases as $phase)
            <label class="flex items-center px-3 py-1 bg-gray-100 rounded-lg shadow-sm hover:bg-gray-200 cursor-pointer">
                <input type="checkbox" 
                       class="w-4 h-4 text-teal-500 border-gray-300 rounded focus:ring-2 focus:ring-teal-400" 
                       name="task_phases[]" 
                       value="{{ $phase->id }}"
                       {{ in_array($phase->id, old('task_phases', $project->task_phases ?? [])) ? 'checked' : '' }}>
                <span class="ml-2">{{ $phase->title }}</span>
            </label>
        @endforeach
    </div>
</div>

           
  <!-- Country -->
        

    <div>
                <label class="mb-[3px] inline-block">Country</label>
                <select name="country_id" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
                    <option value="">Select Country</option>
                    @foreach($countries as $country)
                        <option value="{{ $country->id }}" {{ old('country_id', $project->country_id) == $country->id ? 'selected' : '' }}>
                            {{ $country->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Project Manager -->
            <div>
                <label class="mb-[3px] inline-block">Project Manager</label>
                <select name="project_manager_id" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
                    <option value="">Select</option>
                    @foreach($projectManagers as $manager)
                        <option value="{{ $manager->id }}" {{ old('project_manager_id', $project->project_manager_id) == $manager->id ? 'selected' : '' }}>
                            {{ $manager->name }}
                        </option>
                    @endforeach
                </select>
            </div>
<!-- Team Lead -->
<div>
    <label class="mb-[3px] inline-block">Team Lead</label>
    
    <select name="team_lead_id" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
        <option value="">Select</option>
        @foreach($teamLeads as $tl)
            <option value="{{ $tl->id }}" {{ old('team_lead_id', $project->team_lead_id) == $tl->id ? 'selected' : '' }}>
                {{ $tl->name }}
            </option>
        @endforeach
    </select>
</div>

          <!-- Assign Main Employee (Edit Page, Searchable & Preselected) -->
<div class="col-span-2 mb-4">
    <label class="mb-[3px] inline-block">Assign Main Employee</label>

    <!-- Search Box -->
    <input type="text" id="assign_main_employee_search" placeholder="Search employee..."
           class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none" autocomplete="off">

    <!-- Employee Options (clickable labels) -->
    <div id="assign_main_employee_list" class="border p-2 rounded max-h-64 overflow-y-auto flex flex-wrap gap-2 relative [scrollbar-width:thin] [scrollbar-color:#9ca3af_#e5e7eb">
        @foreach($employees as $employee)
            <label class="text-sm bg-gray-200 text-black px-4 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 hover:bg-teal-400 text-sm  hover:text-white
                          {{ old('assign_main_employee_id', $project->assign_main_employee_id) == $employee->id ? 'bg-blue-500' : 'bg-gray-200' }}"
                   data-id="{{ $employee->id }}">
                <input type="radio" class="employee-radio" name="assign_main_employee_id" 
                       value="{{ $employee->id }}" style="margin-right: 10px;"
                       {{ old('assign_main_employee_id', $project->assign_main_employee_id) == $employee->id ? 'checked' : '' }}>
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
                    @foreach($employees as $user)
                        <option value="{{ $user->id }}" {{ old('upsell_employee_id', $project->upsell_employee_id ?? '') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>



            <!-- Price -->
            <div>
                <label class="mb-[3px] inline-block">Price (USD)</label>
                <input type="number" name="price" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none" value="{{ old('price', $project->price) }}">
            </div>

            <!-- Estimated Hours -->
            <div>
                <label class="mb-[3px] inline-block">Estimated Hours</label>
                <input type="number" name="estimated_hours" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none" value="{{ old('estimated_hours', $project->estimated_hours) }}">
            </div>

            <!-- Project Type -->
            <div>
                <label class="mb-[3px] inline-block">Project Type</label>
                <select name="project_type" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
                    <option value="">Select Project Type</option>
                    @foreach(['Ongoing', 'One-time'] as $type)
                        <option value="{{ $type }}" {{ old('project_type', $project->project_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Upwork Project Type -->
            <div>
                <label class="mb-[3px] inline-block">Upwork Project Type</label>
                <select name="upwork_project_type" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
                    <option value="">Select Project Type</option>
                    @foreach(['Hourly', 'Fixed'] as $type)
                        <option value="{{ $type }}" {{ old('upwork_project_type', $project->upwork_project_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Client Type -->
            <div>
                <label class="mb-[3px] inline-block">Client Type</label>
                <select name="client_type" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
                    <option value="">Select Client Type</option>
                    @foreach(['New Client', 'Old Client'] as $type)
                        <option value="{{ $type }}" {{ old('client_type', $project->client_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Report Type -->
            <div>
                <label class="mb-[3px] inline-block">Report Type</label>
                <select name="report_type" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
                    <option value="">Select Report Type</option>
                    @foreach(['Weekly', 'Bi-Weekly', 'Monthly'] as $type)
                        <option value="{{ $type }}" {{ old('report_type', $project->report_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Project Month -->
            <div>
                <label class="mb-[3px] inline-block">Project Month</label>
                <input
    type="date"
    name="project_month"
    class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none"
    value="{{ old('project_month', $project->project_month ? $project->project_month->format('Y-m-d') : '') }}"
>
            </div>

            <!-- Sales Person -->
            <div>
                <label class="mb-[3px] inline-block">Sales Person</label>
                <select name="sales_person_id" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
                    <option value="">Select Sales Person</option>
                    @foreach($salesPersons as $sales)
                        <option value="{{ $sales->id }}" {{ old('sales_person_id', $project->sales_person_id) == $sales->id ? 'selected' : '' }}>
                            {{ $sales->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Department -->
            <div>
                <label class="mb-[3px] inline-block">Department</label>
                <select name="department_id" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
                    <option value="">Select Department</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ old('department_id', $project->department_id) == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Client Name -->
            <div>
                <label class="mb-[3px] inline-block">Client Name</label>
                <input type="text" name="client_name" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none" value="{{ old('client_name', $project->client_name) }}">
            </div>

            <!-- Client Email -->
            <div>
                <label class="mb-[3px] inline-block">Client Email</label>
                <input type="email" name="client_email" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none" value="{{ old('client_email', $project->client_email) }}">
            </div>

            <!-- Client Other Info -->
            <div class="col-span-2">
                <label class="mb-[3px] inline-block">Client Other Info</label>
                <textarea name="client_other_info" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none">{{ old('client_other_info', $project->client_other_info) }}</textarea>
            </div>

            <!-- Add More Employees -->
            <div class="col-span-2">
    <label class="mb-[3px] inline-block">Add More Employees</label>
    <div class="border p-2 rounded max-h-64 overflow-y-auto flex flex-wrap gap-3 relative [scrollbar-width:thin] [scrollbar-color:#9ca3af_#e5e7eb">
        @foreach($employees as $employee)
            <label class="inline-flex items-center px-4 py-2 rounded cursor-pointer hover:bg-teal-400 text-sm bg-blue-500 text-white" 
                   data-id="{{ $employee->id }}">
                <input type="checkbox" class="employee-checkbox" name="additional_employees[]" 
                       value="{{ $employee->id }}" 
                       {{ in_array($employee->id, old('additional_employees', $project->additional_employees ?? [])) ? 'checked' : '' }}
                       style="margin-right: 10px;">
                {{ $employee->name }}
            </label>
        @endforeach
    </div>
</div>


<div class="col-span-2 mt-4 text-left">

    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600
            hover:from-teal-500 hover:via-teal-600 hover:to-teal-700 text-white rounded hover:bg-blue-700 transition-all">
        Update
    </button>
         <button type="button" onclick="closeModal('userModal')" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-all">Cancel</button>

</div>








    </form>
</div>
<script>
    document.querySelectorAll('.employee-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function () {
        const item = this.closest('label');
        item.classList.toggle('bg-blue-500', this.checked);
        item.classList.toggle('text-white', this.checked);
    });
});

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.employee-checkbox:checked').forEach(checkbox => {
        const item = checkbox.closest('label');
        item.classList.add('bg-blue-500', 'text-white');
    });
});
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

    // Highlight selected label
    labels.forEach(label => {
        const input = label.querySelector('input');
        input.addEventListener('change', () => {
            labels.forEach(l => l.classList.remove('bg-blue-500', 'text-white'));
            if (input.checked) {
                label.classList.add('bg-blue-500', 'text-white');
            }
        });
    });

    // Pre-highlight selected on page load
    labels.forEach(label => {
        const input = label.querySelector('input');
        if (input.checked) {
            label.classList.add('bg-blue-500', 'text-white');
        }
    });
});
    </script>
@endsection
