@extends('layouts.dashboard')

@section('content')
@role('Project Manager')
<div class="max-w-4xl mx-auto bg-white p-10 rounded-lg shadow-lg text-center mt-10">
        <h2 class="text-2xl font-semibold text-gray-700 mb-6">Project Manager Access</h2>

        <div class="text-center text-red-600 font-medium text-base mb-6">
            You have unread DSR reports of your team, please mark the reports as read to access this page.
        </div>

        <div class="text-center mt-10">
            <a href="{{ route('team.dsr.index') }}"
               class="inline-block bg-blue-600 text-white font-semibold px-6 py-3 rounded hover:bg-blue-700 transition">
                📩 Check Unread Report
            </a>
        </div>
    </div>

@else


        <h2 class="text-2xl font-semibold text-gray-800 text-center animate-bounce">Add your daily status report</h2>

<!-- Last Report Details -->
<div id="last-report-details"
     class="flex justify-between bg-white shadow-md rounded-lg p-4 mb-4 {{ !$lastReport ? 'hidden' : '' }}">
    <p class="text-xl sm:text-2xl font-semibold text-gray-800">
        <span class="text-teal-600 text-lg">Last Report Details:</span>&nbsp;&nbsp;
        <span class="font-bold text-lg">Submitted on:</span>
        <span id="last-submitted-date" class="font-semibold text-black text-lg">
            {{ $lastReport ? \Carbon\Carbon::parse($lastReport->created_at)->format('Y-m-d') : '-' }}
        </span>
        &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
        <span class="font-bold text-lg">Total Hours:</span>
        <span id="last-total-hours" class="font-semibold text-black text-lg">{{ $totalTodayHours ?? '-' }}</span>
    </p>

    <!-- View Previous DSR Button (aligned right) -->
    <a href="{{ route('dsr.previous') }}"
       class="w-full sm:w-auto text-white font-medium px-6 py-2 rounded-lg shadow-md 
            bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600
            hover:from-teal-500 hover:via-teal-600 hover:to-teal-700
            focus:outline-none">
         View Previous DSRs
    </a>
</div>

<!-- Success Message -->
<div id="success-message"
     class="hidden w-full max-w-2xl mx-auto mb-0 px-2 py-2 
         bg-teal-600 text-white text-lg font-semibold 
         rounded-xl shadow-lg transform transition-opacity duration-500 opacity-100 
         flex items-center justify-center gap-2">
    <span class="text-2xl mr-2">✅</span> DSR submitted successfully!
</div>

<div class="max-w-4xl mx-auto bg-white p-10 rounded-lg shadow-lg">
    <form method="POST" class="addDSRForm" id="dsr-form" enctype="multipart/form-data">
        @csrf
        <h2 class="text-2xl font-semibold text-center text-indigo-600 mb-8 text-teal-600">Submit Daily Status Report</h2>

        <!-- Select Project -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Project</label>
            <select name="project_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white shadow-sm focus:outline-none transition duration-150">
            <option value="">-- Select Project --</option>

            <option value="333330">Seo Help</option>
            <option value="333331">R and D</option>
            <option value="333332">Other</option>

            @foreach($assignedProjects as $project)
                @if(!in_array($project->id, [333330, 333331, 333332]))
                    <option value="{{ $project->id }}">
                        {{ $project->name_or_url ?? $project->name ?? $project->title }}
                    </option>
                @endif
                @if($assignedProjects->isEmpty())
                    <p>No projects assigned.</p>
                @endif
            @endforeach
            </select>
            <div class="text-red-500 text-xs mt-1" id="project_id_error"></div>
        </div>

        <!-- Work Description -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Work Description</label>
            <textarea name="work_description" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white shadow-sm focus:outline-none transition duration-150" rows="4"></textarea>
            <p class="text-xs text-gray-500 mt-1">You can use HTML tags like <b><b></b>, <i><i></i></p>
            <div class="text-red-500 text-xs mt-1" id="work_description_error"></div>
        </div>
 <div class="flex gap-6">
        <!-- Hours -->
        <div class="mb-6 w-1/2">
            <label class="block text-sm font-medium text-gray-700 mb-2 not-italic">Add Hours</label>
            <input type="number" name="hours_spent" min="1" max="20" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white shadow-sm focus:outline-none transition duration-150">
            <div class="text-red-500 text-xs mt-1" id="hours_spent_error"></div>
        </div>

        <!-- Helped User -->
        <div class="mb-6 w-1/2">
            <label class="block text-sm font-medium text-gray-700 mb-2 not-italic">Someone Helped?</label>
            <select name="helped_by" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white shadow-sm focus:outline-none transition duration-150">
                <option value="">N/A</option>
                @foreach($allUsers as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
            <div class="text-red-500 text-xs mt-1" id="helped_by_error"></div> 
        </div>  </div>

        <!-- Help Description -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2 not-italic">Help Description</label>
            <textarea name="help_description" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white shadow-sm focus:outline-none transition duration-150" rows="3"></textarea>
            <div class="text-red-500 text-xs mt-1" id="help_description_error"></div>
        </div>

        <!-- Help Rating -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2 not-italic">Help Rating</label>
            <select name="help_rating" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white shadow-sm focus:outline-none transition duration-150">
                <option value="0">N/A</option>
                @for($i = 1; $i <= 5; $i++)
                    <option value="{{ $i }}">{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                @endfor
            </select>
            <div class="text-red-500 text-xs mt-1" id="help_rating_error"></div>
        </div>

        <!-- Checkboxes -->
        <div class="mb-6 space-y-3">
            <label class="flex items-center">
                <input type="checkbox" name="replied_emails" value="1" class="form-checkbox text-indigo-500">
                <span class="ml-2 text-gray-700 not-italic">I have replied to all emails</span>
            </label>
            <label class="flex items-center">
                <input type="checkbox" name="updated_report" value="1" class="form-checkbox text-indigo-500">
                <span class="ml-2 text-gray-700 not-italic">I have sent report to clients & PM</span>
            </label>
            <label class="flex items-center">
                <input type="checkbox" name="justified_work" value="1" class="form-checkbox text-indigo-500">
                <span class="ml-2 text-gray-700 not-italic">I have done justified work today</span>
            </label>
        </div>

        <!-- Buttons -->
        <div class="mt-8 flex justify-between">
            <!-- <button type="button" onclick="add_more_project_template()" class="font-medium px-3 py-1 whitespace-nowrap text-white rounded-lg shadow-md
         bg-gradient-to-r from-emerald-400 via-emerald-500 to-emerald-600
         hover:from-emerald-500 hover:via-emerald-600 hover:to-emerald-700
         focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2
         inline-flex items-center gap-2 transition-all duration-200 ease-in-out' }}">
                Add More Projects
            </button> -->
            <button type="submit" class="font-medium px-6 py-3 whitespace-nowrap text-white rounded-lg shadow-md
         bg-gradient-to-r from-indigo-400 via-indigo-500 to-indigo-600
         hover:from-indigo-500 hover:via-indigo-600 hover:to-indigo-700
         focus:outline-none inline-flex items-center gap-2">
                Submit Report
            </button>
        </div>

        <input type="hidden" id="row_counter" name="row_counter" value="1">
    </form>
</div>
@endrole

@endsection

@section('scripts')
<script>
    $(function () {
        $('#dsr-form').on('submit', function (e) {
            e.preventDefault();

            // Clear previous error messages
            $('.text-red-500').text('');

            let formData = new FormData(this);
            $.ajax({
                url: '{{ route("dsr.store") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.status === 'success') {
                        // Show and animate success message
                        $('#success-message').removeClass('hidden opacity-0').addClass('opacity-100');

                        // Show and update last report details
                        $('#last-report-details').removeClass('hidden');
                        let createdAt = new Date(response.data.created_at);
                        let formattedDate = createdAt.toISOString().split('T')[0]; // Format YYYY-MM-DD
                        $('#last-submitted-date').text(formattedDate);
                        $('#last-total-hours').text(response.total_today_hours);

                        // Clear the form fields
                        $('#dsr-form')[0].reset();

                        // Scroll to success message
                        $('html, body').animate({
                            scrollTop: $('#success-message').offset().top - 20
                        }, 500);

                      //  Hide success message after 5 seconds
                      setTimeout(function() {
  $('#success-message').removeClass('opacity-100').addClass('opacity-0');
  setTimeout(function() {
    $('#success-message').addClass('hidden');
  }, 500); // Wait for fade-out animation
}, 5000); // now hides after 5 seconds
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(field, messages) {
                            $('#' + field + '_error').text(messages.join(', '));
                        });
                    }
                }
            });
        });
    });

    function add_more_project_template() {
        alert('Add More Projects clicked!');
    }
</script>
<style>
    #success-message {
        transition: opacity 0.5s ease-in-out;
    }
</style>
@endsection