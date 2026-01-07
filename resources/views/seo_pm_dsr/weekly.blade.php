{{-- resources/views/seo_pm_dsr/weekly.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'SEO PM Weekly DSR')

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
    .card { transition: all 0.4s ease; }
    .card:hover { transform: translateY(-10px); box-shadow: 0 25px 50px rgba(0,0,0,0.15); }

    .has-error .accordion-header {
        background: linear-gradient(135deg, #e74c3c, #c0392b) !important;
        animation: shake 0.6s;
    }
    @keyframes shake { 0%,100% { transform: translateX(0); } 25% { transform: translateX(-8px); } 75% { transform: translateX(8px); } }

    .error { color: #e74c3c; font-weight: 600; margin-top: 6px; font-size: 0.95rem; }
    .file-preview { max-width: 220px; margin-top: 10px; border-radius: 8px; border: 2px solid #10b981; }
    .file-name { font-size: 0.95rem; color: #059669; margin-top: 6px; font-weight: 600; }

    @keyframes shine { 0% { transform: translateX(-150%); } 100% { transform: translateX(150%); } }
    .animate-shine { animation: shine 4s infinite; }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 py-10 px-4">
    <div class="max-w-4xl mx-auto">

        <!-- Header -->
        <div class="text-center mb-12 fade-in">
            <h1 class="text-5xl md:text-7xl font-extrabold text-gray-800 mb-4">
                SEO PM Weekly DSR
            </h1>
            <p class="text-3xl font-bold text-gray-800">{{ $weekRange ?? now()->startOfWeek()->format('d M') . ' - ' . now()->endOfWeek()->format('d M Y') }}</p>
            <p class="text-xl text-gray-600 mt-2">Weekly Tasks Report • One submission per week</p>
            <div class="mt-6 h-1.5 w-48 bg-gradient-to-r from-indigo-500 to-purple-600 mx-auto rounded-full"></div>
        </div>

        {{-- SUCCESS MESSAGE --}}
        @if(session('success'))
            <div class="relative overflow-hidden rounded-3xl shadow-2xl mb-12">
                <div class="absolute inset-0 bg-gradient-to-r from-emerald-400 via-teal-500 to-cyan-600"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                <div class="absolute inset-0">
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent translate-x-[-100%] animate-shine"></div>
                </div>
                <div class="relative py-16 px-8 text-center text-white">
                    <div class="text-8xl mb-4 animate-bounce">Success</div>
                    <h2 class="text-5xl md:text-7xl font-extrabold tracking-tight drop-shadow-2xl">
                        Weekly DSR Submitted<br>
                        <span class="text-yellow-300">SUCCESSFULLY!</span>
                    </h2>
                    <p class="text-2xl md:text-3xl font-bold mt-6 opacity-95 drop-shadow-lg">
                        Great job this week!
                    </p>
                </div>
            </div>
        @endif

        {{-- ALREADY SUBMITTED --}}
        @if(isset($alreadySubmitted) && $alreadySubmitted && !session('success'))
            <div class="relative overflow-hidden rounded-3xl shadow-2xl mb-16 max-w-5xl mx-auto">
                <div class="absolute inset-0 bg-gradient-to-r from-purple-600 via-pink-600 to-indigo-700"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                <div class="absolute inset-0">
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent translate-x-[-100%] animate-shine"></div>
                </div>
                <div class="relative py-24 px-8 text-center text-white">
                    <div class="text-9xl mb-6 animate-bounce">Checkmark</div>
                    <h2 class="text-6xl md:text-8xl font-extrabold tracking-tight drop-shadow-2xl">
                        You're All Set This Week!
                    </h2>
                    <p class="text-3xl md:text-4xl font-bold mt-8 opacity-95 drop-shadow-lg">
                        You've already submitted your Weekly DSR
                    </p>
                    <p class="text-2xl md:text-3xl mt-6 text-yellow-300 font-bold drop-shadow-lg">
                        Come back next week!
                    </p>
                </div>
            </div>
        @endif

        @if(!$alreadySubmitted || $errors->any())
            <form action="{{ route('seo.pm.dsr.store.weekly') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf

                <!-- Case Studies / Portfolio / Reviews (Weekly) + Hours Tracking -->
<div class="bg-white rounded-2xl shadow-xl overflow-hidden card slide-up">
    <div class="accordion-header text-white p-6 cursor-pointer flex justify-between items-center"
         style="background: linear-gradient(135deg, #0d9488, #0d9488)"
         onclick="toggleSection(this)">
        <h3 class="text-2xl font-bold">Case Studies / Portfolio / Reviews (Weekly)</h3>
        <svg class="w-8 h-8 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>

    <div class="p-8 space-y-10 bg-gray-50 hidden">

        @foreach([
            'updated_case_study' => 'Updated 1 case study or ranking portfolio this week',
            'collected_review'   => 'Collected 1 review this week'
        ] as $field => $label)
            <div class="border-b border-gray-200 pb-10 last:border-0">
                <!-- Task Title + Hours Field -->
                <div class="flex justify-between items-start mb-5">
                    <label class="text-lg font-semibold text-gray-800 pr-4">{{ $label }}</label>
                    <div class="flex items-center gap-3 whitespace-nowrap">
                        <span class="text-sm text-gray-600">Hours spent:</span>
                        <input type="number"
                               name="hours[{{ $field }}]"
                               min="0"
                               max="24"
                               step="0.5"
                               placeholder="0"
                               value="{{ old("hours.$field") }}"
                               class="w-20 px-3 py-2 border border-gray-300 rounded-lg text-center focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition">
                        <span class="text-sm text-gray-600">hrs</span>
                    </div>
                </div>

                <!-- Yes / No -->
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
                @error($field) <span class="text-red-600 block mt-1">{{ $message }}</span> @enderror

                <!-- Proof Section -->
                <div id="proof_{{ $field }}" class="mt-4 space-y-4">
                    <div id="file_{{ $field }}" class="{{ old($field) === 'yes' ? '' : 'hidden' }}">
                        <input type="file"
                               name="{{ $field }}_proof"
                               id="file_input_{{ $field }}"
                               class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-green-50 file:text-green-700 hover:file:bg-green-100"
                               onchange="saveFileAndShowPreview('{{ $field }}', this)">
                        <div id="preview_{{ $field }}" class="mt-2"></div>
                        <div id="file_name_{{ $field }}" class="file-name"></div>
                        @error("{$field}_proof") <span class="text-red-600 block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div id="reason_{{ $field }}" class="{{ old($field) === 'no' ? '' : 'hidden' }}">
                        <textarea name="{{ $field }}_reason" rows="3" placeholder="Why not?"
                                  class="w-full px-5 py-4 border-2 border-gray-300 rounded-xl focus:border-red-500 focus:ring-4 focus:ring-red-200">{{ old("{$field}_reason") }}</textarea>
                        @error("{$field}_reason") <span class="text-red-600 block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        @endforeach

       

    </div>
</div>

                @php
    $seoDiscoveryErrors = collect([
        'seo_discovery_post',
        'weekly_team_session',
        'seo_video_shared',
        'seo_discovery_post_proof',
        'seo_discovery_post_reason',
        'weekly_team_session_proof',
        'weekly_team_session_reason',
        'seo_video_shared_proof',
        'seo_video_shared_reason'
    ])->filter(fn($field) => $errors->has($field))->isNotEmpty();
@endphp

<!-- SEO Discovery (Weekly Task) + Hours Tracking -->
<div class="bg-white rounded-2xl shadow-xl overflow-hidden card slide-up {{ $seoDiscoveryErrors ? 'has-error' : '' }}">
    <div class="accordion-header text-white p-6 cursor-pointer flex justify-between items-center"
         style="background: linear-gradient(135deg, #0d9488, #0d9488)"
         onclick="toggleSection(this)">
        <h3 class="text-2xl font-bold">SEO Discovery (Weekly Task)</h3>
        <svg class="w-8 h-8 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>

    <div class="p-8 space-y-10 bg-gray-50 hidden">

        @foreach([
            'seo_discovery_post'   => 'Wrote a post about SEO Discovery’s quality/results on any platform',
            'weekly_team_session'  => 'Conducted a weekly team session to explain what you learned',
            'seo_video_shared'     => 'Watched one SEO video, shared with team & reviewed feedback'
        ] as $field => $label)
            <div class="border-b border-gray-200 pb-10 last:border-0">
                <!-- Task Title + Hours Field -->
                <div class="flex justify-between items-start mb-5">
                    <label class="text-lg font-semibold text-gray-800 pr-4">{{ $label }}</label>
                    <div class="flex items-center gap-3 whitespace-nowrap">
                        <span class="text-sm text-gray-600">Hours spent:</span>
                        <input type="number"
                               name="hours[{{ $field }}]"
                               min="0"
                               max="40"
                               step="0.5"
                               placeholder="0"
                               value="{{ old("hours.$field") }}"
                               class="w-20 px-3 py-2 border border-gray-300 rounded-lg text-center focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 transition">
                        <span class="text-sm text-gray-600">hrs</span>
                    </div>
                </div>

                <!-- Yes / No -->
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
                @error($field) <span class="text-red-600 block mt-1">{{ $message }}</span> @enderror

                <!-- Proof Section -->
                <div id="proof_{{ $field }}" class="mt-4 space-y-4">
                    <div id="file_{{ $field }}" class="{{ old($field) === 'yes' ? '' : 'hidden' }}">
                        <input type="file"
                               name="{{ $field }}_proof"
                               id="file_input_{{ $field }}"
                               class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-green-50 file:text-green-700 hover:file:bg-green-100"
                               onchange="saveFileAndShowPreview('{{ $field }}', this)">
                        <div id="preview_{{ $field }}" class="mt-2"></div>
                        <div id="file_name_{{ $field }}" class="file-name"></div>
                        @error("{$field}_proof") <span class="text-red-600 block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div id="reason_{{ $field }}" class="{{ old($field) === 'no' ? '' : 'hidden' }}">
                        <textarea name="{{ $field }}_reason" rows="3" placeholder="Why not?"
                                  class="w-full px-5 py-4 border-2 border-gray-300 rounded-xl focus:border-red-500 focus:ring-4 focus:ring-red-200">{{ old("{$field}_reason") }}</textarea>
                        @error("{$field}_reason") <span class="text-red-600 block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        @endforeach

       

    </div>
</div>

                <div class="text-center mt-16 slide-up">
                    <button type="submit" class="px-16 py-6 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-2xl font-bold rounded-full shadow-2xl hover:scale-110 transition duration-300">
                        Submit Weekly DSR
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>

<script>
// Store files so they don't disappear after error
const preservedFiles = {};

function toggleSection(el) {
    const content = el.nextElementSibling;
    const svg = el.querySelector('svg');
    content.classList.toggle('hidden');
    svg.classList.toggle('rotate-180');
}

// FIXED: Now correctly show/hide file & reason
function toggleProof(field) {
    const yesChecked = document.querySelector(`input[name="${field}"][value="yes"]`).checked;
    const fileDiv = document.getElementById(`file_${field}`);
    const reasonDiv = document.getElementById(`reason_${field}`);

    if (yesChecked) {
        fileDiv.classList.remove('hidden');
        reasonDiv.classList.add('hidden');
    } else {
        fileDiv.classList.add('hidden');
        reasonDiv.classList.remove('hidden');
    }

    // Restore file if user switches back to Yes
    if (yesChecked && preservedFiles[field]) {
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
    document.querySelectorAll('.accordion-header')[0]?.click();
    document.querySelectorAll('.has-error .accordion-header').forEach(h => h.click());

    const fields = ['updated_case_study','collected_review','seo_discovery_post','weekly_team_session','seo_video_shared'];
    fields.forEach(field => {
        const yes = document.querySelector(`input[name="${field}"][value="yes"]`);
        const no = document.querySelector(`input[name="${field}"][value="no"]`);
        if (yes?.checked) {
            toggleProof(field);
        } else if (no?.checked) {
            toggleProof(field);
        }

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