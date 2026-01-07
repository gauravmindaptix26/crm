@extends('layouts.dashboard')

@section('title', 'Web Dev PM Daily DSR')

@push('styles')
<style>
    /* Your existing styles here */

    /* FIX: Arrow rotation when section opens */
    .rotate-180 {
        transform: rotate(180deg);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 py-10 px-4">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-12 fade-in">
            <h1 class="text-5xl md:text-7xl font-extrabold text-gray-800 mb-4">Web Dev PM Daily DSR</h1>
            <p class="text-3xl font-bold text-gray-800">{{ $today }}</p>
            <p class="text-xl text-gray-600 mt-2">Daily Status Report</p>
        </div>

        @if($alreadySubmitted && !session('success'))
    <div class="max-w-3xl mx-auto text-center py-20">
        <div class="bg-gradient-to-br from-green-500 to-emerald-600 text-white p-12 rounded-3xl shadow-2xl">
            <svg class="w-32 h-32 mx-auto mb-8 text-white opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h2 class="text-5xl font-extrabold mb-6">Today's Report Already Submitted!</h2>
            <p class="text-3xl font-bold mb-4 text-yellow-200">{{ $today }}</p>
            <p class="text-xl mb-10 opacity-90">Great job completing your daily report on time!</p>
            <div class="space-y-4">
                <p class="text-lg">You can submit tomorrow's report starting from midnight.</p>
                <a href="{{ route('web.dev.pm.dsr.dashboard') }}"
                   class="inline-block bg-white text-[#0d9488] px-10 py-5 rounded-2xl text-2xl font-bold hover:bg-gray-100 transition shadow-xl">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
@else
            <form action="{{ route('web.dev.pm.dsr.store.daily') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                <div class="space-y-6">

                    <!-- Marketplace Job -->
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden card slide-up">
                        <div class="accordion-header text-white p-6 cursor-pointer flex justify-between items-center" style="background: linear-gradient(135deg, #0d9488, #0d9488)" onclick="toggleSection(this)">
                            <h3 class="text-2xl font-bold">Marketplace Job</h3>
                            <svg class="w-8 h-8 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <div class="p-8 space-y-6 bg-gray-50 hidden">
                            <div>
                                <label class="block text-lg font-bold text-gray-800 mb-3">5 Bids on Upwork Daily</label>
                                <input type="number" name="upwork_bids" min="0" class="w-full text-3xl text-center py-5 rounded-xl border-2 border-gray-300" value="{{ old('upwork_bids', 0) }}">
                                @error('upwork_bids') <span class="text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-lg font-bold text-gray-800 mb-3">2 Bids in Day on PPH</label>
                                <input type="number" name="pph_bids" min="0" class="w-full text-3xl text-center py-5 rounded-xl border-2 border-gray-300" value="{{ old('pph_bids', 0) }}">
                                @error('pph_bids') <span class="text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-lg font-bold text-gray-800 mb-3">Fiverr Account Maintain</label>
                                <input type="text" name="fiverr_maintain" class="w-full px-6 py-5 rounded-xl border-2 border-gray-300 text-xl" value="{{ old('fiverr_maintain') }}">
                                @error('fiverr_maintain') <span class="text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-lg font-bold text-gray-800 mb-3">2 Jobs Apply on Dribbble</label>
                                <input type="text" name="dribbble_jobs" class="w-full px-6 py-5 rounded-xl border-2 border-gray-300 text-xl" value="{{ old('dribbble_jobs') }}">
                                @error('dribbble_jobs') <span class="text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-lg font-bold text-gray-800 mb-3">2 Jobs Apply Online for Any Jobs Site (Remote Work)</label>
                                <textarea name="online_jobs_apply" rows="5" class="w-full px-6 py-5 rounded-xl border-2 border-gray-300 text-lg">{{ old('online_jobs_apply') }}</textarea>
                                @error('online_jobs_apply') <span class="text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
    <label class="block text-lg font-bold text-gray-800 mb-3">
        File Uploads (Multiple) <span class="text-gray-500 text-sm">(Optional)</span>
    </label>
    <input type="file" 
           name="marketplace_files[]" 
           multiple 
           accept="image/jpeg,image/png,image/jpg,application/pdf"
           class="block w-full text-sm text-gray-600 
                  file:mr-4 file:py-2 file:px-4 
                  file:rounded-lg file:border-0 
                  file:bg-green-50 file:text-green-700 
                  hover:file:bg-green-100">
    
    <!-- General error for the field array -->
    @error('marketplace_files')
        <span class="text-red-600 text-sm mt-2 block">{{ $message }}</span>
    @enderror
    
    <!-- Specific error for individual files -->
    @error('marketplace_files.*')
        <span class="text-red-600 text-sm mt-2 block">One or more files are invalid (only JPG, PNG, PDF ≤ 5MB allowed)</span>
    @enderror
</div>
                            <!-- Hours for this section -->
                            <div class="text-center p-6 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-2xl border-2 border-dashed border-indigo-400">
                                <label class="block text-xl font-bold text-gray-800 mb-4">Hours Spent on Marketplace Job</label>
                                <div class="flex items-center justify-center gap-4">
                                    <input type="number" name="hours[marketplace_job]" min="0" max="24" step="0.5" placeholder="0" value="{{ old('hours.marketplace_job') }}" class="w-32 px-6 py-4 text-3xl font-bold text-center border-2 border-indigo-400 rounded-xl focus:border-indigo-600 focus:ring-4 focus:ring-indigo-200 transition">
                                    <span class="text-2xl font-medium text-gray-700">hours</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Old Client Management -->
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden card slide-up">
                        <div class="accordion-header text-white p-6 cursor-pointer flex justify-between items-center" style="background: linear-gradient(135deg, #0d9488, #0d9488)" onclick="toggleSection(this)">
                            <h3 class="text-2xl font-bold">Old Client Management</h3>
                            <svg class="w-8 h-8 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <div class="p-8 space-y-6 bg-gray-50 {{ $errors->hasAny(['old_clients_design', 'old_leads_ask_work', 'hours.old_client_management']) ? '' : 'hidden' }}">
                            <div>
                                <label class="block text-lg font-bold text-gray-800 mb-3">10 Old Clients (Design Work Done)</label>
                                <textarea name="old_clients_design" rows="5" class="w-full px-6 py-5 rounded-xl border-2 border-gray-300 text-lg">{{ old('old_clients_design') }}</textarea>
                                @error('old_clients_design') <span class="text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-lg font-bold text-gray-800 mb-3">5 Old Leads (Asking for Work)</label>
                                <input type="text" name="old_leads_ask_work" class="w-full px-6 py-5 rounded-xl border-2 border-gray-300 text-xl" value="{{ old('old_leads_ask_work') }}">
                                @error('old_leads_ask_work') <span class="text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <!-- Hours for this section -->
                            <div class="text-center p-6 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-2xl border-2 border-dashed border-indigo-400">
                                <label class="block text-xl font-bold text-gray-800 mb-4">Hours Spent on Old Client Management</label>
                                <div class="flex items-center justify-center gap-4">
                                    <input type="number" name="hours[old_client_management]" min="0" max="24" step="0.5" placeholder="0" value="{{ old('hours.old_client_management') }}" class="w-32 px-6 py-4 text-3xl font-bold text-center border-2 border-indigo-400 rounded-xl focus:border-indigo-600 focus:ring-4 focus:ring-indigo-200 transition">
                                    <span class="text-2xl font-medium text-gray-700">hours</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Project Coordinator Job -->
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden card slide-up">
                        <div class="accordion-header text-white p-6 cursor-pointer flex justify-between items-center" style="background: linear-gradient(135deg, #0d9488, #0d9488)" onclick="toggleSection(this)">
                            <h3 class="text-2xl font-bold">Project Coordinator Job</h3>
                            <svg class="w-8 h-8 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <div class="p-8 space-y-6 bg-gray-50 {{ $errors->hasAny(['client_communication', 'current_client_more_work', 'project_completion_on_time', 'meet_pm_more_work', 'hours.project_coordinator_job']) ? '' : 'hidden' }}">
                            <div>
                                <label class="block text-lg font-bold text-gray-800 mb-3">Client Communication and Management</label>
                                <textarea name="client_communication" rows="5" class="w-full px-6 py-5 rounded-xl border-2 border-gray-300 text-lg">{{ old('client_communication') }}</textarea>
                                @error('client_communication') <span class="text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-lg font-bold text-gray-800 mb-3">Current Client Ask for More Work (Coordinate with Team)</label>
                                <textarea name="current_client_more_work" rows="5" class="w-full px-6 py-5 rounded-xl border-2 border-gray-300 text-lg">{{ old('current_client_more_work') }}</textarea>
                                @error('current_client_more_work') <span class="text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-lg font-bold text-gray-800 mb-3">Project Completion on Time</label>
                                <input type="text" name="project_completion_on_time" class="w-full px-6 py-5 rounded-xl border-2 border-gray-300 text-xl" value="{{ old('project_completion_on_time') }}">
                                @error('project_completion_on_time') <span class="text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-lg font-bold text-gray-800 mb-3">Meet Project Manager to Ask for More Web Design Work</label>
                                <input type="text" name="meet_pm_more_work" class="w-full px-6 py-5 rounded-xl border-2 border-gray-300 text-xl" value="{{ old('meet_pm_more_work') }}">
                                @error('meet_pm_more_work') <span class="text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <!-- Hours for this section -->
                            <div class="text-center p-6 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-2xl border-2 border-dashed border-indigo-400">
                                <label class="block text-xl font-bold text-gray-800 mb-4">Hours Spent on Project Coordinator Job</label>
                                <div class="flex items-center justify-center gap-4">
                                    <input type="number" name="hours[project_coordinator_job]" min="0" max="24" step="0.5" placeholder="0" value="{{ old('hours.project_coordinator_job') }}" class="w-32 px-6 py-4 text-3xl font-bold text-center border-2 border-indigo-400 rounded-xl focus:border-indigo-600 focus:ring-4 focus:ring-indigo-200 transition">
                                    <span class="text-2xl font-medium text-gray-700">hours</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-center mt-16 slide-up">
                        <button type="submit" class="px-16 py-6 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-2xl font-bold rounded-full shadow-2xl hover:scale-110 transition duration-300">
                            Submit Daily DSR
                        </button>
                    </div>
                </div>
            </form>
        @endif
    </div>
</div>

<script>
function toggleSection(el) {
    const content = el.nextElementSibling;
    const svg = el.querySelector('svg');
    
    if (content && svg) {
        content.classList.toggle('hidden');
        svg.classList.toggle('rotate-180');
    }
}

// On page load: open first section AND any section with validation errors
document.addEventListener('DOMContentLoaded', function () {
    // Always open the first section by default
    const firstHeader = document.querySelector('.accordion-header');
    if (firstHeader && firstHeader.nextElementSibling.classList.contains('hidden')) {
        firstHeader.click();
    }

    // Open any section that has validation errors
    document.querySelectorAll('.accordion-header').forEach(header => {
        const content = header.nextElementSibling;
        if (content && !content.classList.contains('hidden') && content.querySelector('.text-red-600')) {
            // Already open due to error, just ensure arrow is rotated
            header.querySelector('svg').classList.add('rotate-180');
        }
    });
});
</script>
@endsection