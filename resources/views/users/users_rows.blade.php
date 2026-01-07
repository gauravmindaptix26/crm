@foreach ($users as $user)
<tr id="user-{{ $user->id }}" class="hover:bg-gray-50 transition-all">
<td class="border px-6 py-4 text-center">
    @if ($users instanceof \Illuminate\Pagination\LengthAwarePaginator)
        {{ $users->firstItem() + $loop->index }}
    @else
        {{ $loop->iteration }}
    @endif
</td>   

<td class="px-6 py-4">
    <div class="flex flex-col items-center justify-center h-full">
        <img src="{{ $user->image ? asset('storage/' . $user->image) : asset('storage/images/default.png') }}" alt="User Image" class="w-12 h-12 rounded-full object-cover shadow-md">
        <span class="font-semibold text-tealCustom mt-2">{{ $user->name }}</span>
          </div>
    </td>


    <td class="border px-6 py-4 text-gray-600">
        <span class="block"><strong style="color: #000;">Email:</strong> {{ $user->email }}</span>
        <span class="block"><strong style="color: #000;">Phone:</strong> {{ $user->phone_number }}</span>
        <span class="block"><strong style="color: #000;">SEO Experience:</strong> {{ $user->company_experience }}</span>
        <span class="block"><strong style="color: #000;">Perior Experience:</strong> {{ $user->experience }}</span>


        <span class="block"><strong style="color: #000;">Date of Joining:</strong> {{ $user->date_of_joining }}</span>

        <span class="block">
                  <strong style="color: #000;">Disable Login:</strong> 
                  @if ($user->disable_login)
                  <span class="text-red-600 font-semibold">Yes</span>
                  @else
                  <span class="text-green-600 font-semibold">No</span>
                  @endif
                  </span>        <span class="block"><strong style="color: #000;">View All Projects:</strong> {{ $user->allow_all_projects ? 'Yes' : 'No' }}</span>
     
         <span class="block"><strong style="color: #000;">Special Permission:</strong> 
    @if ($user->roles->contains('name', 'Admin'))
        <span class="text-green-600 font-semibold">Yes</span>
    @else
        <span class="text-red-600 font-semibold">No</span>
    @endif
</span>

    </td>
    <td class="border px-6 py-4">
        <span class="block font-semibold text-blue-700">{{ $user->roles->pluck('name')->first() }}</span>
          <span class="block text-gray-600"><strong style="color: #000;">Dept: </strong>{{ $user->department->name ?? '-' }}</span>
          <span class="block text-gray-600"><strong style="color: #000;">Emp Code: </strong>{{ $user->employee_code }}</span>
         <span class="block text-gray-600"><strong style="color: #000;">Reporting:</strong> {{ $user->reportingPerson?->name ?? '-' }}</span><br>
        </td>
    <td class="border px-6 py-4 text-gray-600">
       <strong style="color: #000;">Monthly:</strong> {{ $user->monthly_target }}<br>
        <strong style="color: #000;">Incentive:</strong> {{ $user->upsell_incentive }}%
    </td>
    <td class="px-16 py-20 flex space-x-2">
        <button onclick="editUser({{ $user->id }})" class="p-2 rounded bg-blue-600 text-white hover:bg-blue-700 hover:scale-110 active:scale-95 transition-transform duration-200 shadow-md">  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
    stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
    <path stroke-linecap="round" stroke-linejoin="round"
      d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
  </svg></button>
        <button onclick="deleteUser({{ $user->id }})" class="p-2 rounded bg-red-600 text-white hover:bg-red-700 hover:scale-110 active:scale-95 transition-transform duration-200 shadow-md">  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
    stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
    <path stroke-linecap="round" stroke-linejoin="round"
      d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
  </svg></button>
        <a href="{{ route('users.show', $user->id) }}" class="p-2 rounded bg-green-600 text-white hover:bg-green-700 hover:scale-110 active:scale-95 transition-transform duration-200 shadow-md inline-block"> <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
    stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
    <path stroke-linecap="round" stroke-linejoin="round"
      d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
    <path stroke-linecap="round" stroke-linejoin="round"
      d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
  </svg></a>

    </td>
</tr>
@endforeach

@if ($users instanceof \Illuminate\Pagination\LengthAwarePaginator && $users->hasPages())
    <div id="paginationLinks" class="mt-4">
        {{ $users->links() }}
    </div>
@else
    <div id="paginationLinks" style="display: none;"></div>
@endif
