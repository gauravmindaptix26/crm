{{-- resources/views/seo_pm_dsr/daily.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'SEO PM Daily DSR')

@push('styles')
<style>
    .fade-in { animation: fadeIn 1s ease-out forwards; }
    .slide-up { animation: slideUp 0.8s ease-out forwards; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUp { from { transform: translateY(40px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

    .accordion-header {
        background: linear-gradient(135deg, #0d9488, #0d9488);
        transition: all 0.4s ease;
    }
    .accordion-header:hover { transform: translateY(-4px); box-shadow: 0 10px 25px rgba(13,148,136,0.4); }
    .card { transition: all 0.4s ease; }
    .card:hover { transform: translateY(-10px); box-shadow: 0 25px 50px rgba(14, 5, 5, 0.15); }

    .has-error .accordion-header {
        background: linear-gradient(135deg, #e74c3c, #c0392b) !important;
        animation: shake 0.6s;
    }
    @keyframes shake { 0%,100% { transform: translateX(0); } 25% { transform: translateX(-8px); } 75% { transform: translateX(8px); } }

    .error { color: #e74c3c !important; font-weight: 600 !important; margin-top: 6px; font-size: 0.95rem; }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 py-10 px-4">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-12 fade-in">
            <h1 class="text-5xl md:text-7xl font-extrabold text-gray-800 mb-4">SEO PM Daily DSR</h1>
            <p class="text-3xl font-bold text-gray-800">{{ $today }}</p>
            <p class="text-xl text-gray-600 mt-2">Daily Status Report</p>
        </div>

        @if($errors->any())
            <!-- <div class="bg-red-100 border-l-6 border-red-500 text-red-700 p-6 rounded-lg mb-8 shadow-lg">
                <div class="flex items-center">
                    <span class="text-3xl mr-4">Warning</span>
                    <div>
                        <p class="font-bold text-xl">Please fix the following errors:</p>
                        <ul class="mt-3 list-disc list-inside text-lg">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div> -->
        @endif

        @if($alreadySubmitted && !session('success'))
            <div class="bg-green-600 text-white p-8 rounded-2xl shadow-2xl text-center">
                <h2 class="text-4xl font-bold mb-3">Daily DSR Already Submitted</h2>
                <p class="text-2xl opacity-90">
                    You have already submitted your Daily Report for:
                    <span class="font-bold text-yellow-300">{{ $today }}</span>
                </p>
                <p class="mt-4 text-lg opacity-80">
                    Great job! You can come back tomorrow for the next DSR.
                </p>
                <a href="{{ route('seo.pm.dsr.dashboard') }}"
                   class="mt-8 inline-block bg-indigo-500 text-white px-6 py-3 rounded-xl text-xl font-semibold hover:bg-indigo-600 transition">
                    Back to Dashboard
                </a>
            </div>
        @else
            <form action="{{ route('seo.pm.dsr.store.daily') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                <div class="space-y-6">

                  <!-- 1. Remarketing / Upsell -->
<div class="bg-white rounded-2xl shadow-xl overflow-hidden card slide-up">
    <div class="accordion-header text-white p-6 cursor-pointer flex justify-between items-center" 
         style="background: linear-gradient(135deg, #0d9488, #0d9488)" 
         onclick="toggleSection(this)">
        <h3 class="text-2xl font-bold">Remarketing / Upsell (Daily)</h3>
        <svg class="w-8 h-8 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>

    <div class="p-8 space-y-10 bg-gray-50 hidden">

        <!-- 1. Followed up with 2 paused clients -->
        <div class="border-b border-gray-200 pb-8">
            <div class="flex justify-between items-start mb-4">
                <label class="text-lg font-semibold text-gray-800">Followed up with 2 paused clients today</label>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-600">Hours spent:</span>
                    <input type="number" name="hours[follow_paused_clients]" 
                           min="0" max="24" step="0.5" placeholder="0"
                           value="{{ old('hours.follow_paused_clients') }}"
                           class="w-20 px-3 py-2 border border-gray-300 rounded-lg text-center focus:border-teal-500 focus:ring-2 focus:ring-teal-200">
                    <span class="text-sm text-gray-600">hrs</span>
                </div>
            </div>

            <div class="flex gap-12 text-xl mb-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="radio" name="follow_paused_clients" value="yes" class="w-6 h-6 text-green-600"
                           {{ old('follow_paused_clients') === 'yes' ? 'checked' : '' }}
                           onchange="toggleProof('follow_paused_clients')">
                    <span class="text-green-600 font-bold">Yes</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="radio" name="follow_paused_clients" value="no" class="w-6 h-6 text-red-600"
                           {{ old('follow_paused_clients') === 'no' ? 'checked' : '' }}
                           onchange="toggleProof('follow_paused_clients')">
                    <span class="text-red-600 font-bold">No</span>
                </label>
            </div>
            @error('follow_paused_clients') <span class="text-red-600">{{ $message }}</span> @enderror

            <div id="proof_follow_paused_clients" class="mt-4 space-y-4">
                <div id="file_follow_paused_clients" class="{{ old('follow_paused_clients') === 'yes' ? '' : 'hidden' }}">
                    <input type="file" name="follow_paused_clients_proof" class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                    @error('follow_paused_clients_proof') <span class="text-red-600">{{ $message }}</span> @enderror
                </div>
                <div id="reason_follow_paused_clients" class="{{ old('follow_paused_clients') === 'no' ? '' : 'hidden' }}">
                    <textarea name="follow_paused_clients_reason" rows="3" placeholder="Why not?" class="w-full px-5 py-4 border-2 border-gray-300 rounded-xl focus:border-red-500 focus:ring-4 focus:ring-red-200">{{ old('follow_paused_clients_reason') }}</textarea>
                    @error('follow_paused_clients_reason') <span class="text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- 2. Followed up with 2 closed clients -->
        <div class="border-b border-gray-200 pb-8">
            <div class="flex justify-between items-start mb-4">
                <label class="text-lg font-semibold text-gray-800">Followed up with 2 closed clients today</label>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-600">Hours spent:</span>
                    <input type="number" name="hours[follow_closed_clients]" 
                           min="0" max="24" step="0.5" placeholder="0"
                           value="{{ old('hours.follow_closed_clients') }}"
                           class="w-20 px-3 py-2 border border-gray-300 rounded-lg text-center focus:border-teal-500 focus:ring-2 focus:ring-teal-200">
                    <span class="text-sm text-gray-600">hrs</span>
                </div>
            </div>

            <div class="flex gap-12 text-xl mb-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="radio" name="follow_closed_clients" value="yes" class="w-6 h-6 text-green-600"
                           {{ old('follow_closed_clients') === 'yes' ? 'checked' : '' }}
                           onchange="toggleProof('follow_closed_clients')">
                    <span class="text-green-600 font-bold">Yes</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="radio" name="follow_closed_clients" value="no" class="w-6 h-6 text-red-600"
                           {{ old('follow_closed_clients') === 'no' ? 'checked' : '' }}
                           onchange="toggleProof('follow_closed_clients')">
                    <span class="text-red-600 font-bold">No</span>
                </label>
            </div>
            @error('follow_closed_clients') <span class="text-red-600">{{ $message }}</span> @enderror

            <div id="proof_follow_closed_clients" class="mt-4 space-y-4">
                <div id="file_follow_closed_clients" class="{{ old('follow_closed_clients') === 'yes' ? '' : 'hidden' }}">
                    <input type="file" name="follow_closed_clients_proof" class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                    @error('follow_closed_clients_proof') <span class="text-red-600">{{ $message }}</span> @enderror
                </div>
                <div id="reason_follow_closed_clients" class="{{ old('follow_closed_clients') === 'no' ? '' : 'hidden' }}">
                    <textarea name="follow_closed_clients_reason" rows="3" placeholder="Why not?" class="w-full px-5 py-4 border-2 border-gray-300 rounded-xl focus:border-red-500 focus:ring-4 focus:ring-red-200">{{ old('follow_closed_clients_reason') }}</textarea>
                    @error('follow_closed_clients_reason') <span class="text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- 3. Approached 2 clients for upsell -->
        <div class="border-b border-gray-200 pb-8">
            <div class="flex justify-between items-start mb-4">
                <label class="text-lg font-semibold text-gray-800">Approached 2 clients for upsell today</label>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-600">Hours spent:</span>
                    <input type="number" name="hours[upsell_clients]" 
                           min="0" max="24" step="0.5" placeholder="0"
                           value="{{ old('hours.upsell_clients') }}"
                           class="w-20 px-3 py-2 border border-gray-300 rounded-lg text-center focus:border-teal-500 focus:ring-2 focus:ring-teal-200">
                    <span class="text-sm text-gray-600">hrs</span>
                </div>
            </div>

            <div class="flex gap-12 text-xl mb-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="radio" name="upsell_clients" value="yes" class="w-6 h-6 text-green-600"
                           {{ old('upsell_clients') === 'yes' ? 'checked' : '' }}
                           onchange="toggleProof('upsell_clients')">
                    <span class="text-green-600 font-bold">Yes</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="radio" name="upsell_clients" value="no" class="w-6 h-6 text-red-600"
                           {{ old('upsell_clients') === 'no' ? 'checked' : '' }}
                           onchange="toggleProof('upsell_clients')">
                    <span class="text-red-600 font-bold">No</span>
                </label>
            </div>
            @error('upsell_clients') <span class="text-red-600">{{ $message }}</span> @enderror

            <div id="proof_upsell_clients" class="mt-4 space-y-4">
                <div id="file_upsell_clients" class="{{ old('upsell_clients') === 'yes' ? '' : 'hidden' }}">
                    <input type="file" name="upsell_clients_proof" class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                    @error('upsell_clients_proof') <span class="text-red-600">{{ $message }}</span> @enderror
                </div>
                <div id="reason_upsell_clients" class="{{ old('upsell_clients') === 'no' ? '' : 'hidden' }}">
                    <textarea name="upsell_clients_reason" rows="3" placeholder="Why not?" class="w-full px-5 py-4 border-2 border-gray-300 rounded-xl focus:border-red-500 focus:ring-4 focus:ring-red-200">{{ old('upsell_clients_reason') }}</textarea>
                    @error('upsell_clients_reason') <span class="text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- 4. Asked 1 client for referral -->
        <div>
            <div class="flex justify-between items-start mb-4">
                <label class="text-lg font-semibold text-gray-800">Asked 1 client for a referral today</label>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-600">Hours spent:</span>
                    <input type="number" name="hours[referral_client]" 
                           min="0" max="24" step="0.5" placeholder="0"
                           value="{{ old('hours.referral_client') }}"
                           class="w-20 px-3 py-2 border border-gray-300 rounded-lg text-center focus:border-teal-500 focus:ring-2 focus:ring-teal-200">
                    <span class="text-sm text-gray-600">hrs</span>
                </div>
            </div>

            <div class="flex gap-12 text-xl mb-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="radio" name="referral_client" value="yes" class="w-6 h-6 text-green-600"
                           {{ old('referral_client') === 'yes' ? 'checked' : '' }}
                           onchange="toggleProof('referral_client')">
                    <span class="text-green-600 font-bold">Yes</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="radio" name="referral_client" value="no" class="w-6 h-6 text-red-600"
                           {{ old('referral_client') === 'no' ? 'checked' : '' }}
                           onchange="toggleProof('referral_client')">
                    <span class="text-red-600 font-bold">No</span>
                </label>
            </div>
            @error('referral_client') <span class="text-red-600">{{ $message }}</span> @enderror

            <div id="proof_referral_client" class="mt-4 space-y-4">
                <div id="file_referral_client" class="{{ old('referral_client') === 'yes' ? '' : 'hidden' }}">
                    <input type="file" name="referral_client_proof" class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                    @error('referral_client_proof') <span class="text-red-600">{{ $message }}</span> @enderror
                </div>
                <div id="reason_referral_client" class="{{ old('referral_client') === 'no' ? '' : 'hidden' }}">
                    <textarea name="referral_client_reason" rows="3" placeholder="Why not?" class="w-full px-5 py-4 border-2 border-gray-300 rounded-xl focus:border-red-500 focus:ring-4 focus:ring-red-200">{{ old('referral_client_reason') }}</textarea>
                    @error('referral_client_reason') <span class="text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

    </div>
</div>

                    
                   <!-- Invoices & Payments (Daily) + Multiple Screenshots -->
<div class="bg-white rounded-2xl shadow-xl overflow-hidden card slide-up {{ $errors->hasAny(['invoices_sent','invoices_pending','payment_followups','payment_screenshots']) ? 'has-error' : '' }}">
    <div class="accordion-header text-white p-6 cursor-pointer flex justify-between items-center" 
         style="background: linear-gradient(135deg, #0d9488, #0d9488)" onclick="toggleSection(this)">
        <h3 class="text-2xl font-bold">Invoices & Payments (Daily)</h3>
        <svg class="w-8 h-8 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>

    <div class="p-8 bg-gray-50 hidden">
        <!-- 3 Number Fields -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div>
                <label class="block text-lg font-bold text-gray-800 mb-3">No of Invoice's Sent</label>
                <input type="number" name="invoices_sent" class="w-full text-3xl text-center py-6 rounded-xl border-2 border-gray-300" value="{{ old('invoices_sent') }}" min="0">
                @error('invoices_sent') <span class="text-red-600 block mt-2">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-lg font-bold text-gray-800 mb-3">No of Invoice's Pending</label>
                <input type="number" name="invoices_pending" class="w-full text-3xl text-center py-6 rounded-xl border-2 border-gray-300" value="{{ old('invoices_pending') }}" min="0">
                @error('invoices_pending') <span class="text-red-600 block mt-2">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-lg font-bold text-gray-800 mb-3">No of Payment Follow-ups</label>
                <input type="number" name="payment_followups" class="w-full text-3xl text-center py-6 rounded-xl border-2 border-gray-300" value="{{ old('payment_followups') }}" min="0">
                @error('payment_followups') <span class="text-red-600 block mt-2">{{ $message }}</span> @enderror
            </div>
        </div>
        <!-- ONE SINGLE HOURS FIELD FOR THIS SECTION -->
        <div class="text-center p-6 bg-gradient-to-r from-amber-50 to-orange-50 rounded-2xl border-2 border-dashed border-amber-400 mb-10">
            <label class="block text-xl font-bold text-gray-800 mb-4">
                Total Hours Spent on Invoices & Payments Today
            </label>
            <div class="flex items-center justify-center gap-4">
                <input type="number"
                       name="hours[invoices_payments]"
                       min="0"
                       max="24"
                       step="0.5"
                       placeholder="0"
                       value="{{ old('hours.invoices_payments') }}"
                       class="w-32 px-6 py-4 text-3xl font-bold text-center border-2 border-amber-400 rounded-xl focus:border-amber-600 focus:ring-4 focus:ring-amber-200 transition">
                <span class="text-2xl font-medium text-gray-700">hours</span>
            </div>
            <p class="text-sm text-gray-600 mt-3">Include time spent on invoicing, follow-ups, and payments</p>
        </div>

        <!-- SIMPLE MULTIPLE FILE UPLOAD -->
        <div class="mt-10">
            <label class="block text-lg font-bold text-gray-800 mb-4">
                Payment Screenshots <span class="text-sm text-gray-500 font-normal">(Optional - Multiple files supported)</span>
            </label>

            <input type="file"
                   name="payment_screenshots[]"
                   id="payment_screenshots"
                   multiple
                   accept="image/*,.pdf"
                   class="block w-full text-sm text-gray-700
                          file:mr-4 file:py-3 file:px-6
                          file:rounded-lg file:border-0
                          file:text-sm file:font-medium
                          file:bg-gradient-to-r file:from-teal-500 file:to-emerald-600 file:text-white
                          hover:file:from-teal-600 hover:file:to-emerald-700
                          cursor-pointer">

            <!-- Simple Preview List -->
            <div id="payment-preview" class="mt-4 space-y-2"></div>

            @error('payment_screenshots') 
                <span class="text-red-600 block mt-2">{{ $message }}</span> 
            @enderror
            @error('payment_screenshots.*') 
                <span class="text-red-600 block mt-2">Invalid file (max 5MB, JPG/PNG/PDF only)</span> 
            @enderror
        </div>
    </div>
</div>

                  <!-- Happy Things Today (Daily) + Total Hours Tracking -->
<div class="bg-white rounded-2xl shadow-xl overflow-hidden card slide-up">
    <div class="accordion-header text-white p-6 cursor-pointer flex justify-between items-center" 
         style="background: linear-gradient(135deg, #0d9488, #0d9488)" onclick="toggleSection(this)">
        <h3 class="text-2xl font-bold">Happy Things Today (Daily)</h3>
        <svg class="w-8 h-8 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>

    <div class="p-8 bg-gray-50 hidden">
        <!-- Happy Numbers Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10">
            <div class="text-center">
                <label class="block text-lg font-bold text-gray-800 mb-3">Paused Today</label>
                <input type="number" name="paused_today" class="w-full text-4xl text-center py-6 rounded-xl border-2 border-gray-300 focus:border-gray-500" 
                       value="{{ old('paused_today', 0) }}" min="0">
                @error('paused_today') <span class="text-red-600 block mt-2">{{ $message }}</span> @enderror
            </div>
            <div class="text-center">
                <label class="block text-lg font-bold text-green-600 mb-3">Restarted Today</label>
                <input type="number" name="restarted_today" class="w-full text-4xl text-center py-6 rounded-xl border-2 border-green-300 focus:border-green-500" 
                       value="{{ old('restarted_today', 0) }}" min="0">
                @error('restarted_today') <span class="text-red-600 block mt-2">{{ $message }}</span> @enderror
            </div>
            <div class="text-center">
                <label class="block text-lg font-bold text-purple-600 mb-3">Closed Today</label>
                <input type="number" name="closed_today" class="w-full text-4xl text-center py-6 rounded-xl border-2 border-purple-300 focus:border-purple-500" 
                       value="{{ old('closed_today', 0) }}" min="0">
                @error('closed_today') <span class="text-red-600 block mt-2">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- SINGLE HOURS FIELD FOR THIS SECTION -->
        <div class="text-center p-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl border-2 border-dashed border-indigo-300">
            <label class="block text-xl font-bold text-gray-800 mb-4">
                Total Hours Spent on Happy Things Today</label>
            <div class="flex items-center justify-center gap-4">
                <input type="number"
                       name="hours[happy_things]"
                       min="0"
                       max="24"
                       step="0.5"
                       placeholder="0"
                       value="{{ old('hours.happy_things') }}"
                       class="w-32 px-6 py-4 text-3xl font-bold text-center border-2 border-indigo-300 rounded-xl focus:border-indigo-600 focus:ring-4 focus:ring-indigo-200 transition">
                <span class="text-2xl font-medium text-gray-700">hours</span>
            </div>
            <p class="text-sm text-gray-600 mt-3">Include time spent on paused, restarted, or closed clients</p>
        </div>
    </div>
</div>

                  <!-- Daily Production Work – CLEAN, COMPACT & CONSISTENT WITH OTHER SECTIONS -->
<div class="bg-white rounded-2xl shadow-xl overflow-hidden card slide-up">
    <div class="accordion-header text-white p-6 cursor-pointer flex justify-between items-center" 
         style="background: linear-gradient(135deg, #0d9488, #0d9488)" 
         onclick="toggleSection(this)">
        <h3 class="text-2xl font-bold">Daily Production Work (Daily)</h3>
        <svg class="w-8 h-8 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>

    <div class="p-8 space-y-10 bg-gray-50 hidden">
        <!-- Multiple Project Selection – Compact & Clean -->
        <div>
            <label class="block text-lg font-semibold text-gray-800 mb-3">
                Projects Worked On Today
                <span class="text-sm font-normal text-gray-600">(Hold Ctrl/Cmd to select multiple)</span>
            </label>

            @if($assignedProjects->isEmpty())
                <p class="text-gray-500 italic">No projects assigned to you yet.</p>
            @else
                <select name="production_projects[]" multiple 
                        class="w-full h-32 px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-[#0d9488] focus:ring-4 focus:ring-[#0d9488]/20 text-base bg-white"
                        size="6">
                    @foreach($assignedProjects as $project)
                        <option value="{{ $project->id }}" {{ in_array($project->id, old('production_projects', [])) ? 'selected' : '' }}>
                            {{ $project->name_or_url }}
                        </option>
                    @endforeach
                </select>
            @endif

            @error('production_projects')
                <span class="text-red-600 text-sm block mt-2">{{ $message }}</span>
            @enderror
        </div>

        <!-- Meetings & Client Queries – Side by side -->
        <div class="grid md:grid-cols-2 gap-8">
            <div>
                <label class="block text-lg font-semibold text-gray-800 mb-3">Meetings Completed</label>
                <input type="number" 
                       name="meetings_completed" 
                       min="0"
                       class="w-full text-2xl text-center py-4 rounded-lg border-2 border-gray-300 focus:border-[#0d9488] focus:ring-4 focus:ring-[#0d9488]/20 transition"
                       value="{{ old('meetings_completed', 0) }}">
                @error('meetings_completed') 
                    <span class="text-red-600 text-sm block mt-2">{{ $message }}</span> 
                @enderror
            </div>
            <div>
                <label class="block text-lg font-semibold text-gray-800 mb-3">Client Queries Resolved</label>
                <input type="text" 
                       name="client_queries_resolved" 
                       class="w-full px-5 py-4 rounded-lg border-2 border-gray-300 text-base focus:border-[#0d9488] focus:ring-4 focus:ring-[#0d9488]/20 transition"
                       value="{{ old('client_queries_resolved') }}"
                       placeholder="e.g., Answered emails, fixed issues...">
                @error('client_queries_resolved') 
                    <span class="text-red-600 text-sm block mt-2">{{ $message }}</span> 
                @enderror
            </div>
        </div>

        <!-- Additional Tasks / Notes -->
        <div>
            <label class="block text-lg font-semibold text-gray-800 mb-3">Additional Tasks / Notes</label>
            <textarea name="additional_tasks" 
                      rows="4" 
                      class="w-full px-5 py-4 rounded-lg border-2 border-gray-300 text-base focus:border-[#0d9488] focus:ring-4 focus:ring-[#0d9488]/20 transition"
                      placeholder="Any other work or notes...">{{ old('additional_tasks') }}</textarea>
            @error('additional_tasks') 
                <span class="text-red-600 text-sm block mt-2">{{ $message }}</span> 
            @enderror
        </div>

        <!-- Total Hours – Compact & Matches Other Sections -->
        <div class="text-center p-6 bg-gradient-to-r from-[#0d9488]/10 to-teal-100 rounded-2xl border-2 border-dashed border-[#0d9488]">
            <label class="block text-xl font-bold text-[#0d9488] mb-4">
                Total Hours Spent on Production Work Today
            </label>
            <div class="flex items-center justify-center gap-4">
                <input type="number"
                       name="hours[production_work]"
                       min="0"
                       max="24"
                       step="0.5"
                       placeholder="0"
                       value="{{ old('hours.production_work') }}"
                       class="w-32 px-5 py-3 text-2xl font-bold text-center border-2 border-[#0d9488] rounded-lg focus:ring-4 focus:ring-[#0d9488]/20 transition">
                <span class="text-2xl font-medium text-gray-700">hours</span>
            </div>
            <p class="text-sm text-gray-600 mt-3">Include time spent on meetings, queries, and other production tasks</p>
        </div>
    </div>
</div>
                    @php
    $companyTaskErrors = collect([
        'checked_teammate_dsr',
        'audited_project',
        'checked_teammate_dsr_proof',
        'checked_teammate_dsr_reason',
        'audited_project_proof',
        'audited_project_reason',
        'daily_tasks_description'
    ])->filter(fn($field) => $errors->has($field))->isNotEmpty();
@endphp

<!-- Daily Tasks Recommended by Company (Daily) + Hours Tracking -->
<div class="bg-white rounded-2xl shadow-xl overflow-hidden card slide-up {{ $companyTaskErrors ? 'has-error' : '' }}">
    <div class="accordion-header text-white p-6 cursor-pointer flex justify-between items-center"
         style="background: linear-gradient(135deg, #0d9488, #0d9488)"
         onclick="toggleSection(this)">
        <h3 class="text-2xl font-bold">Daily Tasks Recommended by Company (Daily)</h3>
        <svg class="w-8 h-8 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>

    <div class="p-8 space-y-12 bg-gray-50 hidden">

        <!-- 1. Checked a teammate’s DSR and provided a rating -->
        <div class="border-b border-gray-200 pb-10">
            <div class="flex justify-between items-start mb-4">
                <label class="text-lg font-semibold text-gray-800">Checked a teammate’s DSR and provided a rating</label>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-600">Hours spent:</span>
                    <input type="number" 
                           name="hours[checked_teammate_dsr]" 
                           min="0" max="24" step="0.5" 
                           placeholder="0"
                           value="{{ old('hours.checked_teammate_dsr') }}"
                           class="w-20 px-3 py-2 border border-gray-300 rounded-lg text-center focus:border-teal-500 focus:ring-2 focus:ring-teal-200">
                    <span class="text-sm text-gray-600">hrs</span>
                </div>
            </div>

            <div class="flex gap-12 text-xl mb-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="radio" name="checked_teammate_dsr" value="yes" class="w-6 h-6 text-green-600"
                           {{ old('checked_teammate_dsr') === 'yes' ? 'checked' : '' }}
                           onchange="toggleProof('checked_teammate_dsr')">
                    <span class="text-green-600 font-bold">Yes</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="radio" name="checked_teammate_dsr" value="no" class="w-6 h-6 text-red-600"
                           {{ old('checked_teammate_dsr') === 'no' ? 'checked' : '' }}
                           onchange="toggleProof('checked_teammate_dsr')">
                    <span class="text-red-600 font-bold">No</span>
                </label>
            </div>
            @error('checked_teammate_dsr') <span class="text-red-600">{{ $message }}</span> @enderror

            <div id="proof_checked_teammate_dsr" class="mt-4 space-y-4">
                <div id="file_checked_teammate_dsr" class="{{ old('checked_teammate_dsr') === 'yes' ? '' : 'hidden' }}">
                    <input type="file" name="checked_teammate_dsr_proof" class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                    @error('checked_teammate_dsr_proof') <span class="text-red-600">{{ $message }}</span> @enderror
                </div>
                <div id="reason_checked_teammate_dsr" class="{{ old('checked_teammate_dsr') === 'no' ? '' : 'hidden' }}">
                    <textarea name="checked_teammate_dsr_reason" rows="3" placeholder="Why not?" class="w-full px-5 py-4 border-2 border-gray-300 rounded-xl focus:border-red-500 focus:ring-4 focus:ring-red-200">{{ old('checked_teammate_dsr_reason') }}</textarea>
                    @error('checked_teammate_dsr_reason') <span class="text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- 2. Audited at least one active project -->
        <div>
            <div class="flex justify-between items-start mb-4">
                <label class="text-lg font-semibold text-gray-800">Audited at least one active project with a teammate and gave suggestions</label>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-600">Hours spent:</span>
                    <input type="number" 
                           name="hours[audited_project]" 
                           min="0" max="24" step="0.5" 
                           placeholder="0"
                           value="{{ old('hours.audited_project') }}"
                           class="w-20 px-3 py-2 border border-gray-300 rounded-lg text-center focus:border-teal-500 focus:ring-2 focus:ring-teal-200">
                    <span class="text-sm text-gray-600">hrs</span>
                </div>
            </div>

            <div class="flex gap-12 text-xl mb-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="radio" name="audited_project" value="yes" class="w-6 h-6 text-green-600"
                           {{ old('audited_project') === 'yes' ? 'checked' : '' }}
                           onchange="toggleProof('audited_project')">
                    <span class="text-green-600 font-bold">Yes</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="radio" name="audited_project" value="no" class="w-6 h-6 text-red-600"
                           {{ old('audited_project') === 'no' ? 'checked' : '' }}
                           onchange="toggleProof('audited_project')">
                    <span class="text-red-600 font-bold">No</span>
                </label>
            </div>
            @error('audited_project') <span class="text-red-600">{{ $message }}</span> @enderror

            <div id="proof_audited_project" class="mt-4 space-y-4">
                <div id="file_audited_project" class="{{ old('audited_project') === 'yes' ? '' : 'hidden' }}">
                    <input type="file" name="audited_project_proof" class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                    @error('audited_project_proof') <span class="text-red-600">{{ $message }}</span> @enderror
                </div>
                <div id="reason_audited_project" class="{{ old('audited_project') === 'no' ? '' : 'hidden' }}">
                    <textarea name="audited_project_reason" rows="3" placeholder="Why not?" class="w-full px-5 py-4 border-2 border-gray-300 rounded-xl focus:border-red-500 focus:ring-4 focus:ring-red-200">{{ old('audited_project_reason') }}</textarea>
                    @error('audited_project_reason') <span class="text-red-600">{{ $message }}</span> @enderror
                </div>
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
    content.classList.toggle('hidden');
    svg.classList.toggle('rotate-180');
}

function toggleProof(field) {
    const value = document.querySelector(`input[name="${field}"]:checked`)?.value;
    document.getElementById(`file_${field}`).classList.toggle('hidden', value !== 'yes');
    document.getElementById(`reason_${field}`).classList.toggle('hidden', value !== 'no');
}

// Auto-open first section + restore proof fields
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.accordion-header')[0]?.click();
    document.querySelectorAll('.has-error .accordion-header').forEach(h => h.click());

    ['follow_paused_clients','follow_closed_clients','upsell_clients','referral_client','audit_project'].forEach(field => {
        const checked = document.querySelector(`input[name="${field}"]:checked`);
        if (checked) toggleProof(field);
    });
});
</script>

@endsection