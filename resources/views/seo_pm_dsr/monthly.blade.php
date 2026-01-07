{{-- resources/views/seo_pm_dsr/monthly.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'SEO PM Monthly DSR')

@push('styles')
<style>
    .fade-in { animation: fadeIn 1s ease-out forwards; }
    .slide-up { animation: slideUp 0.8s ease-out forwards; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUp { from { transform: translateY(40px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

    .accordion-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        transition: all 0.4s ease;
    }
    .accordion-header:hover { transform: translateY(-4px); box-shadow: 0 10px 25px rgba(102,126,234,0.4); }
    .card:hover { transform: translateY(-10px); box-shadow: 0 25px 50px rgba(0,0,0,0.15); }

    .has-error .accordion-header {
        background: linear-gradient(135deg, #e74c3c, #c0392b) !important;
        animation: shake 0.6s;
    }
    @keyframes shake { 0%,100% { transform: translateX(0); } 25% { transform: translateX(-8px); } 75% { transform: translateX(8px); } }

    .error { color: #e74c3c; font-weight: 600; margin-top: 6px; font-size: 0.95rem; }
    .file-preview { max-width: 220px; margin-top: 10px; border-radius: 8px; border: 2px solid #10b981; }
    .file-name { font-size: 0.95rem; color: #059669; margin-top: 6px; font-weight: 600; }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 py-10 px-4">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-12 fade-in">
            <h1 class="text-5xl md:text-7xl font-extrabold text-gray-800 mb-4">SEO PM Monthly DSR</h1>
            <p class="text-3xl font-bold text-gray-800">{{ $monthName ?? now()->format('F Y') }}</p>
            <p class="text-xl text-gray-600 mt-2">Monthly Opportunities Report • One submission per month</p>
            <div class="mt-6 h-1.5 w-48 bg-gradient-to-r from-indigo-500 to-purple-600 mx-auto rounded-full"></div>
        </div>

        @if(session('success'))
            <div class="text-center py-16 text-6xl font-bold text-green-600">SUCCESS! Monthly DSR Submitted</div>
        @endif

        @if(isset($alreadySubmitted) && $alreadySubmitted && !session('success'))
            <div class="text-center py-24 text-6xl font-bold text-purple-600">Already Submitted This Month!</div>
        @endif

        @if(!$alreadySubmitted || $errors->any())
            <form action="{{ route('seo.pm.dsr.store.monthly') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf

                <div class="bg-white rounded-2xl shadow-xl overflow-hidden card slide-up">
                    <div class="accordion-header text-white p-6 cursor-pointer flex justify-between items-center"
                         style="background: linear-gradient(135deg, #0d9488, #0d9488)" onclick="toggleSection(this)">
                        <h3 class="text-2xl font-bold">Suggestions to Clients for New Opportunities (Monthly)</h3>
                        <svg class="w-8 h-8 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                    <div class="p-8 bg-gray-50 hidden space-y-10">

                        @foreach([
                            'pr_placements' => 'Press Release (PR) Placements',
                            'guest_post_backlinking' => 'Guest Post Backlinking',
                            'website_redesign' => 'Website Redesign or Issue Resolution',
                            'blog_writing_seo' => 'Blog Writing and SEO Content',
                            'virtual_assistant' => 'Virtual Assistant Support',
                            'full_web_development' => 'Full Web Development Services',
                            'crm_setup' => 'CRM Setup and Training',
                            'google_ads' => 'Google Ads Management',
                            'social_ads' => 'Facebook/Instagram/LinkedIn Ads',
                            'logo_redesign' => 'Logo Redesign',
                            'podcast_outreach' => 'Podcast Outreach and Promotion',
                            'video_testimonial' => 'Video Testimonial Service',
                            'google_reviews_service' => 'Google Reviews Service (Reputation Management)'
                        ] as $field => $label)
                            <div>
                                <label class="block text-lg font-semibold text-gray-800 mb-4">{{ $label }}</label>
                                <div class="flex gap-12 text-xl mb-4">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="radio" name="{{ $field }}" value="yes" class="w-6 h-6 text-green-600"
                                               {{ old($field) === 'yes' ? 'checked' : '' }}
                                               onchange="toggleProof('{{ $field }}')">
                                        <span class="text-green-600 font-bold">Yes</span>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="radio" name="{{ $field }}" value="no" class="w-6 h-6 text-red-600"
                                               {{ old($field) === 'no' ? 'checked' : '' }}
                                               onchange="toggleProof('{{ $field }}')">
                                        <span class="text-red-600 font-bold">No</span>
                                    </label>
                                </div>
                                @error($field) <span class="text-red-600">{{ $message }}</span> @enderror

                                <div id="proof_{{ $field }}" class="mt-4 space-y-4">
                                    <!-- FILE UPLOAD (Yes) -->
                                    <div id="file_{{ $field }}" class="{{ old($field) === 'yes' ? '' : 'hidden' }}">
                                        <input type="file"
                                               name="{{ $field }}_proof"
                                               id="file_input_{{ $field }}"
                                               class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-green-50 file:text-green-700 hover:file:bg-green-100"
                                               onchange="saveFileAndShowPreview('{{ $field }}', this)">
                                        <div id="preview_{{ $field }}" class="mt-2"></div>
                                        <div id="file_name_{{ $field }}" class="file-name"></div>
                                        @error("{$field}_proof") <span class="text-red-600">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- REASON BOX (No) — NOW 100% WORKING -->
                                    <div id="reason_{{ $field }}" class="{{ old($field) === 'no' ? '' : 'hidden' }}">
                                        <textarea name="{{ $field }}_reason" rows="3" placeholder="Why not? (Required if No)"
                                                  class="w-full px-5 py-4 border-2 border-gray-300 rounded-xl focus:border-red-500 focus:ring-4 focus:ring-red-200">{{ old("{$field}_reason") }}</textarea>
                                        @error("{$field}_reason") <span class="text-red-600">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <!-- ONE TOTAL HOURS FIELD FOR THE ENTIRE MONTHLY SECTION -->
        <div class="mt-12 p-10 bg-gradient-to-r from-orange-50 to-red-50 rounded-3xl border-4 border-dashed border-orange-400 text-center shadow-xl">
            <label class="block text-2xl font-bold text-gray-800 mb-6">
                Total Hours Spent on Client Opportunities This Month
            </label>
            <div class="flex items-center justify-center gap-6">
                <input type="number"
                       name="hours[monthly_opportunities_total]"
                       min="0"
                       max="200"
                       step="0.5"
                       placeholder="0"
                       value="{{ old('hours.monthly_opportunities_total') }}"
                       class="w-48 px-10 py-6 text-5xl font-extrabold text-center border-4 border-orange-500 rounded-2xl focus:border-orange-700 focus:ring-8 focus:ring-orange-200 transition shadow-2xl">
                <span class="text-4xl font-bold text-gray-800">hours</span>
            </div>
            <p class="text-md text-gray-700 mt-5 max-w-2xl mx-auto">
                Total time you spent this month suggesting new services to clients (PR, Ads, Web Dev, etc.)
            </p>
        </div>

                        
                </div>

                <div class="text-center mt-16 slide-up">
                    <button type="submit" class="px-16 py-6 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-2xl font-bold rounded-full shadow-2xl hover:scale-110 transition duration-300">
                        Submit Monthly DSR
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>

<script>
// File memory
const preservedFiles = {};

function toggleSection(el) {
    const content = el.nextElementSibling;
    const svg = el.querySelector('svg');
    content.classList.toggle('hidden');
    svg.classList.toggle('rotate-180');
}

// FIXED: Now "No" opens reason box correctly
function toggleProof(field) {
    const yesRadio = document.querySelector(`input[name="${field}"][value="yes"]`);
    const noRadio = document.querySelector(`input[name="${field}"][value="no"]`);
    const fileDiv = document.getElementById(`file_${field}`);
    const reasonDiv = document.getElementById(`reason_${field}`);

    if (yesRadio.checked) {
        fileDiv.classList.remove('hidden');
        reasonDiv.classList.add('hidden');
    } else if (noRadio.checked) {
        fileDiv.classList.add('hidden');
        reasonDiv.classList.remove('hidden');
    } else {
        fileDiv.classList.add('hidden');
        reasonDiv.classList.add('hidden');
    }

    // Restore file if switching back to Yes
    if (yesRadio.checked && preservedFiles[field]) {
        const input = document.getElementById(`file_input_${field}`);
        const dto = new DataTransfer();
        dto.items.add(preservedFiles[field]);
        input.files = dto.files;
        showPreview(field, preservedFiles[field]);
    }
}

function saveFileAndShowPreview(field, input) {
    const file = input.files[0];
    if (file) {
        preservedFiles[field] = file;
        showPreview(field, file);
    }
}

function showPreview(field, file) {
    const preview = document.getElementById(`preview_${field}`);
    const nameDiv = document.getElementById(`file_name_${field}`);
    const reader = new FileReader();
    reader.onload = e => {
        preview.innerHTML = `<img src="${e.target.result}" class="file-preview" alt="Preview">`;
        nameDiv.textContent = file.name;
    };
    reader.readAsDataURL(file);
}

document.addEventListener('DOMContentLoaded', () => {
    // Open first section + error sections
    document.querySelectorAll('.accordion-header')[0]?.click();
    document.querySelectorAll('.has-error .accordion-header').forEach(h => h.click());

    // Restore all Yes/No states and files
    const fields = ['pr_placements','guest_post_backlinking','website_redesign','blog_writing_seo','virtual_assistant','full_web_development','crm_setup','google_ads','social_ads','logo_redesign','podcast_outreach','video_testimonial','google_reviews_service'];

    fields.forEach(field => {
        const yes = document.querySelector(`input[name="${field}"][value="yes"]`);
        const no = document.querySelector(`input[name="${field}"][value="no"]`);

        if (yes?.checked) toggleProof(field);
        if (no?.checked) toggleProof(field);

        // Restore file if exists
        if (preservedFiles[field]) {
            const input = document.getElementById(`file_input_${field}`);
            if (input) {
                const dto = new DataTransfer();
                dto.items.add(preservedFiles[field]);
                input.files = dto.files;
                showPreview(field, preservedFiles[field]);
            }
        }
    });
});
</script>
@endsection