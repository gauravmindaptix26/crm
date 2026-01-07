@extends('layouts.dashboard')
@section('title', 'Users Management')
@section('content')
<div class="bg-white shadow-md rounded-lg p-4 mb-4">
   <form id="userFilterForm" action="{{ route('users.index') }}" method="GET" class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 sm:space-x-4">
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
<div class="mb-4">
   <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Filter by Role -->
      <div>
         <label for="filter_role" class="block text-sm font-bold text-black mb-1">User Role</label>
         <select id="filter_role" name="filter_role" 
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white shadow-sm focus:outline-none transition duration-150">
            <option value="">-- Select --</option>
            <option value="HR">HR</option>
            <option value="Team Lead">Team Lead</option>
            <option value="Employee">Employee</option>
            <option value="Freelancer">Freelancer</option>
            <option value="Project Manager">Project Manager</option>
            <option value="Sales Team">Sales Team</option>
         </select>
      </div>
      <!-- Department -->
      <div>
         <label for="filter_department" class="block text-sm font-bold text-black mb-1">Department</label>
         <select id="filter_department" name="filter_department" 
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white shadow-sm focus:outline-none transition duration-150">
            <option value="">-- Select --</option>
            @foreach ($departments as $department)
            <option value="{{ $department->id }}">{{ $department->name }}</option>
            @endforeach
         </select>
      </div>
      <!-- Reporting Person -->
      <div>
         <label for="filter_reporting_person" class="block text-sm font-bold text-black mb-1">Reporting Person</label>
         <select id="filter_reporting_person" name="filter_reporting_person" 
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white shadow-sm focus:outline-none transition duration-150">
            <option value="">-- Select --</option>
            @foreach ($allUsersForDropdown as $user)
            <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
         </select>
      </div>
      <!-- Filter Button -->
      <div class="flex items-end">
         <button id="filterButton" 
            class="w-full sm:w-auto text-white font-medium px-6 py-2 rounded-lg shadow-md 
            bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600
            hover:from-teal-500 hover:via-teal-600 hover:to-teal-700
            focus:outline-none">
         Filter
         </button>
      </div>
   </div>
</div>
<div class="bg-white shadow-lg rounded-lg p-6">
   <div class="flex justify-between items-center mb-4">
      <div id="successMessage" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
         <strong>Success!</strong> <span id="successText"></span>
      </div>
      <h2 class="text-2xl font-bold text-gray-700">Users Management</h2>
      <button onclick="openModal('userModal')"
         class="bg-black hover:bg-gray-900 text-white px-4 py-2 rounded-lg shadow-md transition-all flex items-center space-x-2">
         <!-- Icon -->
         <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
            stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round"
               d="M13.5 16.875h3.375m0 0h3.375m-3.375 0V13.5m0 3.375v3.375M6 10.5h2.25a2.25 2.25 0 0 0 2.25-2.25V6a2.25 2.25 0 0 0-2.25-2.25H6A2.25 2.25 0 0 0 3.75 6v2.25A2.25 2.25 0 0 0 6 10.5Zm0 9.75h2.25A2.25 2.25 0 0 0 10.5 18v-2.25a2.25 2.25 0 0 0-2.25-2.25H6a2.25 2.25 0 0 0-2.25 2.25V18A2.25 2.25 0 0 0 6 20.25Zm9.75-9.75H18a2.25 2.25 0 0 0 2.25-2.25V6A2.25 2.25 0 0 0 18 3.75h-2.25A2.25 2.25 0 0 0 13.5 6v2.25a2.25 2.25 0 0 0 2.25 2.25Z" />
         </svg>
         <!-- Text -->
         <span> Add User</span>
      </button>
   </div>
   <div class="overflow-x-auto bg-white shadow-md rounded-lg">
      <table class="min-w-full border-collapse border border-gray-200 rounded-lg overflow-hidden" id="usersTable">
         <thead>
            <tr class="bg-[#14b8a6f2] text-white text-left">
               <th class="border px-6 py-3">S/No</th>
               <th class="border px-6 py-3">Name</th>
               <th class="border px-6 py-3">User Info</th>
               <th class="border px-6 py-3">User Role</th>
               <th class="border px-6 py-3">User Target</th>
               <th class="border px-6 py-3">Action</th>
            </tr>
         </thead>
         <tbody class="divide-y divide-gray-200">
            @foreach ($users as $user)
            <tr id="user-{{ $user->id }}" class="hover:bg-gray-50 transition-all">
               <td class="border px-6 py-4 text-center">{{ $users->firstItem() + $loop->index }}</td>
               <td class="px-6 py-4">
                  <div class="flex flex-col items-center justify-center h-full">
                     <img src="{{ $user->image ? asset('storage/' . $user->image) : asset('storage/images/default.png') }}"
                        alt="User Image"
                        class="w-12 h-12 rounded-full object-cover shadow-md">
                     <span class="text-center font-semibold text-tealCustom mt-2">{{ $user->name }}</span>
                  </div>
               </td>
               <td class="border px-6 py-4 text-gray-600">
                  <span class="block"><strong style="color: #000;">Email:</strong> {{ $user->email }}</span>
                  <span class="block"><strong style="color: #000;">Phone:</strong> {{ $user->phone_number }}</span>
                  <span class="block"><strong style="color: #000;">SEO Experience:</strong> {{ $user->company_experience }}</span>
                  <span class="block"><strong style="color: #000;">Perior Experience:</strong> {{ $user->experience }}</span>

                  <span class="block"><strong style="color: #000;">Date Of Joining:</strong> {{ $user->date_of_joining }}</span>

                  <span class="block">
                  <strong style="color: #000;">Disable Login:</strong> 
                  @if ($user->disable_login)
                  <span class="text-red-600 font-semibold">Yes</span>
                  @else
                  <span class="text-green-600 font-semibold">No</span>
                  @endif
                  </span>
                  <span class="block"><strong style="color: #000;">View All Projects:</strong> {{ $user->allow_all_projects ? 'Yes' : 'No' }}</span>
                  <span class="block">
                  <strong style="color: #000;">Special Permission:</strong> 
                  @if ($user->roles->contains('name', 'Admin'))
                  <span class="text-green-600 font-semibold">Yes</span>
                  @else
                  <span class="text-red-600 font-semibold">No</span>
                  @endif
                  </span>
               </td>
               <td class="border px-6 py-4">
                  <span class="block font-semibold text-blue-700"><strong style="color: #000;">Role:</strong> {{ $user->roles->pluck('name')->first() }}</span>
                  <span class="block text-gray-600"><strong style="color: #000;">Dept: </strong> {{ $user->department->name ?? '-' }}</span>
                  <span class="block text-gray-600"><strong style="color: #000;">Emp Code: </strong> {{ $user->employee_code }}</span>
                  <span class="block text-gray-600"><strong style="color: #000;">Reporting: </strong> {{ optional($user->reportingPerson)->name ?? '-' }}</span><br>
               </td>
               <td class="border px-6 py-4 text-gray-600">
                 <strong style="color: #000;">Monthly:</strong> {{ $user->monthly_target }}<br>
                 <strong style="color: #000;">Incentive:</strong> {{ $user->upsell_incentive }}%
               </td>
               <td class="px-16 py-20 flex space-x-2">
                  <!-- <td class="px-6 py-4 flex space-x-2"> -->
                  <!-- <button onclick="editUser({{ $user->id }})" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded shadow-md transition-all">Edits</button>
                     <button onclick="deleteUser({{ $user->id }})" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded shadow-md transition-all">Delete</button>
                     <a href="{{ route('users.show', $user->id) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded shadow-md transition-all">View</a> -->
                  <!-- Edit Button -->
                  <!-- Edit Button -->
                  <button onclick="editUser({{ $user->id }})"
                     class="p-2 rounded bg-[#2dd4bf] text-white hover:bg-[#2dd4bf] hover:scale-110 active:scale-95 transition-transform duration-200 shadow-md">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                           d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                     </svg>
                  </button>
                  <!-- Delete Button -->
                  <button onclick="deleteUser({{ $user->id }})"
                     class="p-2 rounded bg-[#E74C3C] text-white hover:bg-[#E74C3C] hover:scale-110 active:scale-95 transition-transform duration-200 shadow-md">


                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                           d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                     </svg>
                  </button>
                  <!-- View Button -->
                  <a href="{{ route('users.show', $user->id) }}"
                     class="p-2 rounded bg-[#313a3ed6] text-white hover:bg-[#313a3ed6] hover:scale-110 active:scale-95 transition-transform duration-200 shadow-md inline-block">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                           d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                           d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                     </svg>
                  </a>
               </td>
            </tr>
            @endforeach
         </tbody>
      </table>
   </div>
   @if ($users instanceof \Illuminate\Pagination\LengthAwarePaginator && $users->total() > 10)
   <div id="paginationLinks" class="mt-4">
      {{ $users->links() }}
   </div>
   @endif
</div>
<!-- Add/Edit User Modal -->
<div id="userModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center p-4">
   <!-- <div class="bg-white p-6 rounded-lg w-full max-w-5xl relative overflow-y-auto max-h-[90vh]"> -->
       <div class="bg-white p-6 rounded-lg w-full max-w-5xl relative  max-h-[100vh]">
      <button onclick="closeModal('userModal')" 
         class="absolute top-3 right-3 bg-black text-white text-2xl hover:bg-gray-800 rounded-full w-8 h-8 flex items-center justify-center">
      ×
      </button>
      <h2 class="text-xl font-bold mb-4 text-center bg-[#14b8a6f2] text-white p-[10px] rounded" id="modalTitle">Add User</h2>
      <!-- <div class="max-h-[70vh] overflow-y-auto p-2"> -->
          <div class="max-h-[100vh] p-2">
         <form id="userForm" method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="userId" name="userId">
            <div class="grid grid-cols-3 gap-4">
               <div><label class="mb-[3px] inline-block">Name</label><input type="text" id="name" name="name" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none"></div>
               <div><label class="mb-[3px] inline-block">Email</label><input type="email" id="email" name="email" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none"></div>
               <div><label class="mb-[3px] inline-block">Phone Number</label><input type="text" id="phone_number" name="phone_number" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none"></div>
               <div>
                 <label class="mb-[3px] inline-block">Role</label>
                  <select id="role" name="role" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
                     <option value="">Select Role</option>
                     <option value="Admin">Admin</option>
                     <option value="HR">HR</option>
                     <option value="Team Lead">Team Lead</option>
                     <option value="Employee">Employee</option>
                     <option value="Freelancer">Freelancer</option>
                     <option value="Project Manager">Project Manager</option>
                     <option value="Sales Team">Sales Team</option>
                     <option value="Sales Team Manager">Sales Team Manager</option>
                  </select>
               </div>
               <div><label class="mb-[3px] inline-block">Monthly Target</label><input type="text" id="monthly_target" name="monthly_target" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none"></div>
               <div><label class="mb-[3px] inline-block">Upsell Incentive (%)</label><input type="number" id="upsell_incentive" name="upsell_incentive" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none"></div>
               <div>
                 <label class="mb-[3px] inline-block">Reporting Person</label>
                  <select id="reporting_person" name="reporting_person" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
                     <option value="">Select Reporting Person</option>
                     @foreach ($allUsersForDropdown as $user)
                     <option value="{{ $user->id }}">{{ $user->name }}</option>
                     @endforeach
                  </select>
               </div>
               <div>
                  <label class="mb-[3px] inline-block">Department</label>
                  <select id="department" name="department" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
                     <option value="">Select Department</option>
                     @foreach ($departments as $department)
                     <option value="{{ $department->id }}">{{ $department->name }}</option>
                     @endforeach
                  </select>
               </div>
               <div>
                  <label for="allow_all_projects" class="mb-[3px] inline-block">Allow User to View All Projects</label>
                  <select name="allow_all_projects" id="allow_all_projects" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
                     <option value="0">No</option>
                     <option value="1">Yes</option>
                  </select>
               </div>
               <div>
                  <label for="disable_login" class="mb-[3px] inline-block">Disable Login</label>
                  <select id="disable_login" name="disable_login" class="w-full px-3 py-2 border rounded text-sm outline-none focus:ring-0">
                     <option value="0" selected>No</option>
                     <option value="1">Yes</option>
                  </select>
                  <small class="text-gray-500">User will not be able to login if set to "Yes".</small>
               </div>
                <div><label class="mb-[3px] inline-block">Perior Experience</label><input type="text" id="experience" name="experience" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none"></div>
               <div><label class="mb-[3px] inline-block">Qualification</label><input type="text" id="qualification" name="qualification" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none"></div>
               <div><label class="mb-[3px] inline-block">Specialization</label><input type="text" id="specialization" name="specialization" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none"></div>
               <div><label class="mb-[3px] inline-block">Date of Joining</label><input type="date" id="date_of_joining" name="date_of_joining" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none"></div>
               <div>
                  <label class="mb-[3px] inline-block">Employee Code</label>
                  <div class="flex">
                     <input type="text" id="employee_code" name="employee_code" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none" >
                     <button type="button" id="generateEmployeeCode" class="ml-2 px-3 py-2 bg-blue-500 text-white rounded bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600
                        hover:from-teal-500 hover:via-teal-600 hover:to-teal-700">Generate</button>
                  </div>
               </div>
               <div><label class="mb-[3px] inline-block">Password</label><input type="password" id="password" name="password" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none"></div>
               <div><label class="mb-[3px] inline-block">Confirm Password</label><input type="password" id="password_confirmation" name="password_confirmation" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none"></div>
               <div class="">
                 <label class="mb-[3px] inline-block">Profile Image</label>
                  <img id="imagePreview" src="" class="w-24 h-24 rounded-full object-cover mb-2 hidden">
                  <input type="file" id="image" name="image" accept="image/*" class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none">
               </div>
               <div>
                  <label for="monthly_salary" class="mb-[3px] inline-block">Monthly Salary (USD)</label>
                  <input type="number"  name="monthly_salary" id="monthly_salary"
                     class="w-full px-3 py-2 border rounded focus:outline-none focus-visible:outline-none" placeholder="Enter monthly salary">
               </div>
            <div class="flex justify-end space-x-2 mt-4">
         <button type="button" onclick="closeModal('userModal')" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-all">Cancel</button>
         <button type="button" id="submitUserForm" class="px-4 py-2 bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600
            hover:from-teal-500 hover:via-teal-600 hover:to-teal-700 text-white rounded hover:bg-blue-700 transition-all">Save</button>
          </div>



            </div>
         </form>
      </div>
    
   </div>
</div>
@endsection
@section('scripts')
<script>
   function openModal(id) {
       document.getElementById(id).classList.remove('hidden');
   }
   
   function closeModal(id) {
   document.getElementById(id).classList.add('hidden');
   document.getElementById('userForm').reset();
   document.getElementById('userId').value = '';
   document.getElementById('modalTitle').innerText = 'Add User';
   document.getElementById('successMessage').classList.add('hidden');
   
   $('#generateEmployeeCode').show(); // Show the button when opening the modal for adding a new user
   $('#employee_code').prop('readonly', false); // Make the field editable
   }
</script>
<script>
   $(document).ready(function () {
       $('#submitUserForm').on('click', function (event) {
           event.preventDefault();
   
           let userId = $('#userId').val();
           let url = userId ? `users/${userId}` : "{{ route('users.store') }}";
           let method = userId ? "POST" : "POST"; // Always POST, use _method for PUT
   
           let formData = new FormData($('#userForm')[0]);
           formData.append('_token', '{{ csrf_token() }}');
           if (userId) {
               formData.append('_method', 'PUT'); // Required for updates
           }
   
           // Clear previous errors
           clearErrors();
   
           $.ajax({
               url: url,
               method: method,
               data: formData,
               processData: false,
               contentType: false,
               success: function (response) {
                   if (response.success) {
                       alert(response.success);
                       closeModal('userModal');
                       location.reload();
                   }
               },
               error: function (xhr) {
                   if (xhr.status === 422) {
                       let errors = xhr.responseJSON.errors;
                       $.each(errors, function (key, value) {
                           let input = $('#' + key);
                           if (input.length) {
                               input.addClass('border-red-500');
                               input.after(`<span class="error-message text-red-500 text-sm mt-1 block">${value[0]}</span>`);
                           } else {
                               // Handle errors for fields not directly tied to an input (e.g., password_confirmation)
                               $('#userForm').prepend(`<span class="error-message text-red-500 text-sm mt-1 block">${key}: ${value[0]}</span>`);
                           }
                       });
                   } else {
                       alert(`Error: ${xhr.responseJSON?.message || 'An unexpected error occurred. Please try again.'}`);
                   }
               }
           });
       });
   
       // Function to clear errors
       function clearErrors() {
           $('.error-message').remove();
           $('.border-red-500').removeClass('border-red-500');
       }
   
       // Reset form and errors when closing modal
       function closeModal(id) {
           document.getElementById(id).classList.add('hidden');
           document.getElementById('userForm').reset();
           document.getElementById('userId').value = '';
           document.getElementById('modalTitle').innerText = 'Add User';
           document.getElementById('imagePreview').classList.add('hidden');
           $('#employee_code').prop('readonly', false);
           $('#generateEmployeeCode').show();
           clearErrors();
       }
   
       // Update editUser to ensure readonly employee_code
       function editUser(userId) {
           $.ajax({
               url: `users/${userId}/edit`,
               method: "GET",
               success: function (response) {
                   clearErrors();
                   $('#userId').val(response.id);
                   $('#name').val(response.name);
                   $('#email').val(response.email);
                   $('#phone_number').val(response.phone_number);
                   $('#monthly_salary').val(response.monthly_salary);
                   $('#monthly_target').val(response.monthly_target);
                   $('#upsell_incentive').val(response.upsell_incentive);
                   $('#reporting_person').val(response.reporting_person).change();
                   $('#department').val(response.department).change();
                   $('#experience').val(response.experience);
                   $('#qualification').val(response.qualification);
                   $('#specialization').val(response.specialization);
                   $('#date_of_joining').val(response.date_of_joining);
                   $('#employee_code').val(response.employee_code);
                   $('#generateEmployeeCode').hide();
                   $('#role').val(response.role).change();
                   $('#disable_login').val(response.disable_login ? '1' : '0').change();
                   $('#allow_all_projects').val(response.allow_all_projects ? '1' : '0').change();
   
                   if (response.image) {
                       $('#imagePreview').attr('src', response.image).removeClass('hidden');
                   } else {
                       $('#imagePreview').addClass('hidden');
                   }
   
                   $('#modalTitle').text('Edit User');
                   openModal('userModal');
               },
               error: function (xhr) {
                   console.error('Error fetching user data:', xhr.responseText);
                   alert('Error fetching user data');
               }
           });
       }
   });
   
   
</script>
<script>
   function editUser(userId) {
       $.ajax({
           url: `users/${userId}/edit`,
           method: "GET",
           success: function (response) {
               $('#userId').val(response.id);
               $('#name').val(response.name);
               $('#email').val(response.email);
               $('#phone_number').val(response.phone_number);
               $('#monthly_salary').val(response.monthly_salary);
   
               $('#monthly_target').val(response.monthly_target);
               $('#upsell_incentive').val(response.upsell_incentive);
               $('#reporting_person').val(response.reporting_person).change();
               $('#department').val(response.department).change();
               $('#experience').val(response.experience);
               $('#qualification').val(response.qualification);
               $('#specialization').val(response.specialization);
               $('#date_of_joining').val(response.date_of_joining);
               
               $('#employee_code').val(response.employee_code); // Disable field
               $('#generateEmployeeCode').hide(); // Hide the button in edit mode
   
               $('#role').val(response.role).change();
               $('#disable_login').val(response.disable_login).change();
   
   
   
               // Show stored image
               if (response.image) {
                   $('#imagePreview').attr('src', response.image).removeClass('hidden');
               } else {
                   $('#imagePreview').addClass('hidden');
               }
   
               $('#modalTitle').text('Edit User');
               openModal('userModal');
           },
           error: function (xhr) {
               console.error('Error fetching user data:', xhr.responseText);
               alert('Error fetching user data');
           }
       });
   }
   
   
   
</script>
<script>
   function deleteUser(userId) {
       if (!confirm('Are you sure you want to delete this user?')) return;
   
       fetch(`users/${userId}`, {
           method: 'DELETE',
           headers: {
               'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
               'Content-Type': 'application/json'
           }
       })
       .then(response => response.json())
       .then(data => {
           alert(data.success);
           location.reload(); // Reload the page after successful deletion
       })
       .catch(error => console.error('Error:', error));
   }
</script>
<script>
   $(document).ready(function () {
    function fetchUsers(url) {
        // If no URL is provided, construct it with form data
        if (!url) {
            var formData = {
                search: $('#searchInput').val() || '',
                filter_role: $('#filter_role').val() || '',
                filter_department: $('#filter_department').val() || '',
                filter_reporting_person: $('#filter_reporting_person').val() || '',
                entries_per_page: $('#entriesPerPage').val() || '10',
                _t: new Date().getTime() // Cache-busting parameter
            };
            url = "{{ route('users.filter') }}?" + $.param(formData);
        }

        console.log('Fetching users with URL:', url); // Debug log

        $.ajax({
            url: url,
            type: "GET",
            cache: false,
            success: function (response) {
                console.log('Response received:', response); // Debug log
                let tbody = $('table#usersTable tbody');
                let htmlContent = $.trim(response.html);

                if (htmlContent && htmlContent !== '<div id="paginationLinks" style="display: none;"></div>') {
                    tbody.html(htmlContent);
                } else {
                    tbody.html('<tr><td colspan="6" class="text-center py-4 text-gray-500">No records found</td></tr>');
                }

                $('#paginationLinks').html(response.pagination || '');
            },
            error: function (xhr) {
                console.error('Error:', xhr.responseText);
                alert("Error fetching users. Please try again.");
            }
        });
    }

    // Load initial data on page load
    fetchUsers();

    // Prevent default form submission
    $('#userFilterForm').submit(function (e) {
        e.preventDefault();
        fetchUsers(); // Trigger AJAX with form data
    });

    // Apply filter on button click
    $('#filterButton').click(function (e) {
        e.preventDefault();
        fetchUsers(); // Trigger AJAX with form data
    });

    // Handle search input on keyup
    $('#searchInput').on('keyup', function (e) {
        if (e.key === 'Enter') {
            fetchUsers(); // Trigger search on Enter key
        }
    });

    // Handle entries per page change
    $('#entriesPerPage').change(function () {
        fetchUsers(); // Trigger AJAX with form data
    });

    // Handle pagination clicks
    $(document).on('click', '#paginationLinks a', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        console.log('Navigating to URL:', url); // Debug log
        fetchUsers(url); // Use the pagination link's URL directly
    });
});
   
   
   
</script>
<script>
   var generateCodeUrl = "{{ route('users.generateEmployeeCode') }}";
</script>
<script>
   $(document).ready(function () {
   $('#generateEmployeeCode').on('click', function () {
       $.ajax({
           url: generateCodeUrl,
           method: "GET",
           success: function (response) {
               if (response.code) {
                   $('#employee_code').val(response.code);
               }
           },
           error: function (xhr) {
               alert("Error generating Employee Code. Please try again.");
           }
       });
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
   
   
   
</script>
@endsection