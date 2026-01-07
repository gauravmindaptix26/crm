@extends('layouts.dashboard')

@section('title', 'Request Leave')

@section('content')
<div class="bg-white shadow-md rounded-lg p-6">
    @if(config('app.debug'))
    <div class="mb-6 p-4 bg-yellow-100 border border-yellow-400 rounded-lg">
        <strong>Debug Info:</strong><br>
        User ID: {{ $user->id }}<br>
        Date of Joining: {{ $user->date_of_joining ?? 'NOT SET' }}<br>
        Eligible Policies: {{ $eligiblePolicies->pluck('name')->implode(', ') }}<br>
        Total Policies: {{ $eligiblePolicies->count() }}<br>
        Unpaid Policy ID: {{ $unpaidPolicyId ?? 'NOT SET' }}
    </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Request Leave</h2>
        <a href="{{ route('leaves.history') }}" 
           class="font-semibold text-white bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-teal-300 font-medium rounded-lg px-5 py-2.5 text-center transition duration-200">
            View History
        </a>
    </div>

    @if (session('error'))
        <div class="mb-6 p-4 bg-red-100 border border-red-500 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-100 border border-red-500 text-red-700 rounded-lg">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <!-- Tabs Navigation -->
    <div class="flex border-b border-gray-200 mb-6">
        <button id="fullTab" class="px-6 py-3 text-sm font-medium text-teal-600 border-b-2 border-teal-600 whitespace-nowrap cursor-pointer">Full/Half Day</button>
        <button id="partialTab" class="px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 whitespace-nowrap border-b-2 border-transparent hover:border-gray-300 cursor-pointer">Partial Day</button>
    </div>

    <!-- Full/Half Day Form - VISIBLE BY DEFAULT -->
    <div id="fullForm">
        <form method="POST" action="{{ route('leaves.store') }}" id="fullLeaveForm">
            @csrf
            <input type="hidden" name="request_type" value="full">
            
            <div class="space-y-6">
                <!-- Date Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">From Date <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" id="start_date_full" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 shadow-sm @error('start_date') border-red-500 @enderror"
                               required min="{{ now()->format('Y-m-d') }}" value="{{ old('start_date') }}" onchange="updateFullSubmitButton();">
                        @error('start_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                        <input type="date" name="end_date" id="end_date" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 shadow-sm @error('end_date') border-red-500 @enderror"
                               value="{{ old('end_date') }}">
                        @error('end_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Leave Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Leave Type <span class="text-red-500">*</span></label>
                    <select name="leave_policy_id" id="leaveType" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 shadow-sm @error('leave_policy_id') border-red-500 @enderror"
                            required onchange="updateFullSubmitButton();">
                        <option value="">Select Leave Type</option>
                        @foreach($eligiblePolicies as $policy)
                            <option value="{{ $policy->id }}" {{ old('leave_policy_id') == $policy->id ? 'selected' : '' }}>
                                {{ $policy->name }}
                                @php
                                    try {
                                        $balance = $user->getLeaveBalance($policy->name);
                                        if ($policy->name === 'Unpaid Leave' || $balance['available'] === 'Unlimited') {
                                            echo ' - Unlimited';
                                        } elseif (isset($balance['probation']) && $balance['probation']) {
                                            echo ' - Not eligible (Probation)';
                                        } else {
                                            echo " - " . number_format($balance['available'], 1) . " days available";
                                        }
                                    } catch (\Exception $e) {
                                        echo ' - Error';
                                    }
                                @endphp
                            </option>
                        @endforeach
                    </select>
                    @error('leave_policy_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Leave Type Options -->
                <div class="space-y-3">
                    <div class="flex items-center">
                        <input type="radio" name="is_custom" value="0" id="fullDay" class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-gray-300" checked onchange="toggleHalfOptions(); updateFullSubmitButton();">
                        <label for="fullDay" class="ml-2 block text-sm text-gray-900 cursor-pointer">Full Day(s)</label>
                    </div>
                    <div class="flex items-center">
                        <input type="radio" name="is_custom" value="1" id="halfDay" class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-gray-300" onchange="toggleHalfOptions(); updateFullSubmitButton();">
                        <label for="halfDay" class="ml-2 block text-sm text-gray-900 cursor-pointer">Half Day(s)</label>
                    </div>
                </div>

                <!-- Half Day Options -->
                <div id="halfOptions" class="hidden space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Half Day Session <span class="text-red-500">*</span></label>
                        <select name="half_day_option" id="halfDayOption" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateFullSubmitButton();">
                            <option value="">Select Session</option>
                            <option value="morning">Morning Half (0.5 days)</option>
                            <option value="afternoon">Afternoon Half (0.5 days)</option>
                            <option value="full">Full Day (1 day)</option>
                        </select>
                    </div>
                </div>

                <!-- Note -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Note (Optional)</label>
                    <textarea name="note" rows="3" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 shadow-sm @error('note') border-red-500 @enderror"
                              placeholder="Reason for leave...">{{ old('note') }}</textarea>
                    @error('note')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end pt-4">
                    <button type="submit" 
                            class="font-semibold text-white bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-teal-300 font-medium rounded-lg px-6 py-2.5 text-center transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            id="fullSubmitBtn">
                        Submit Leave Request
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Partial Day Form -->
    <div id="partialForm" class="hidden">
        <form method="POST" action="{{ route('leaves.store') }}" id="partialLeaveForm">
            @csrf
            <input type="hidden" name="request_type" value="partial">
            <input type="hidden" name="leave_policy_id" value="{{ $unpaidPolicyId }}">
            
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date <span class="text-red-500">*</span></label>
                    <input type="date" name="start_date" id="start_date_partial"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 shadow-sm @error('start_date') border-red-500 @enderror"
                           required min="{{ now()->format('Y-m-d') }}" value="{{ old('start_date') }}" onchange="updatePartialSubmitButton();">
                    @error('start_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type <span class="text-red-500">*</span></label>
                    <select name="partial_type" id="partial_type" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 shadow-sm @error('partial_type') border-red-500 @enderror"
                            required onchange="updatePartialSubmitButton();">
                        <option value="">Select Type</option>
                        <option value="late_arrival" {{ old('partial_type') == 'late_arrival' ? 'selected' : '' }}>Late Arrival</option>
                        <option value="leaving_early" {{ old('partial_type') == 'leaving_early' ? 'selected' : '' }}>Leaving Early</option>
                    </select>
                    @error('partial_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Duration (minutes) <span class="text-red-500">*</span></label>
                    <input type="number" name="partial_minutes" id="partial_minutes" min="1" max="480" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 shadow-sm @error('partial_minutes') border-red-500 @enderror"
                           required value="{{ old('partial_minutes') }}" placeholder="e.g., 120" onchange="updatePartialSubmitButton();">
                    @error('partial_minutes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Maximum 8 hours (480 minutes) - Will be counted as Unpaid Leave</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Note <span class="text-red-500">*</span></label>
                    <textarea name="note" rows="3" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 shadow-sm @error('note') border-red-500 @enderror"
                              required placeholder="Reason for partial leave...">{{ old('note') }}</textarea>
                    @error('note')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button for Partial -->
                <div class="flex justify-end pt-4">
                    <button type="submit" 
                            class="font-semibold text-white bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-teal-300 font-medium rounded-lg px-6 py-2.5 text-center transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            id="partialSubmitBtn" disabled>
                        Submit Partial Request
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fullTab = document.getElementById('fullTab');
    const partialTab = document.getElementById('partialTab');
    const fullForm = document.getElementById('fullForm');
    const partialForm = document.getElementById('partialForm');

    // Tab switching function
    function showFullTab() {
        fullForm.classList.remove('hidden');
        partialForm.classList.add('hidden');
        fullTab.classList.add('text-teal-600', 'border-teal-600');
        fullTab.classList.remove('text-gray-500', 'border-transparent');
        partialTab.classList.remove('text-teal-600', 'border-teal-600');
        partialTab.classList.add('text-gray-500', 'border-transparent');
        updateFullSubmitButton();
    }

    function showPartialTab() {
        partialForm.classList.remove('hidden');
        fullForm.classList.add('hidden');
        partialTab.classList.add('text-teal-600', 'border-teal-600');
        partialTab.classList.remove('text-gray-500', 'border-transparent');
        fullTab.classList.remove('text-teal-600', 'border-teal-600');
        fullTab.classList.add('text-gray-500', 'border-transparent');
        updatePartialSubmitButton();
    }

    // Full form functions
    function updateFullSubmitButton() {
        const startDate = document.getElementById('start_date_full').value;
        const leaveType = document.getElementById('leaveType').value;
        const isFullDay = document.querySelector('input[name="is_custom"]:checked').value === '0';
        const halfOption = document.getElementById('halfDayOption').value;
        
        const isValid = startDate && leaveType && (isFullDay || halfOption);
        const submitBtn = document.getElementById('fullSubmitBtn');
        submitBtn.disabled = !isValid;
    }

    function toggleHalfOptions() {
        const isHalfDay = document.querySelector('input[name="is_custom"]:checked').value === '1';
        const halfOptions = document.getElementById('halfOptions');
        halfOptions.classList.toggle('hidden', !isHalfDay);
        updateFullSubmitButton();
    }

    // Partial form functions
    function updatePartialSubmitButton() {
        const startDate = document.getElementById('start_date_partial').value;
        const partialType = document.getElementById('partial_type').value;
        const partialMinutes = document.getElementById('partial_minutes').value;
        const submitBtn = document.getElementById('partialSubmitBtn');
        
        const isValid = startDate && partialType && partialMinutes;
        submitBtn.disabled = !isValid;
    }

    // Event listeners for tabs
    fullTab.addEventListener('click', showFullTab);
    partialTab.addEventListener('click', showPartialTab);

    // Full form event listeners
    document.querySelectorAll('input[name="is_custom"]').forEach(radio => {
        radio.addEventListener('change', toggleHalfOptions);
    });

    document.getElementById('start_date_full').addEventListener('change', updateFullSubmitButton);
    document.getElementById('leaveType').addEventListener('change', updateFullSubmitButton);
    document.getElementById('halfDayOption').addEventListener('change', updateFullSubmitButton);

    // Partial form event listeners
    document.getElementById('start_date_partial').addEventListener('change', updatePartialSubmitButton);
    document.getElementById('partial_type').addEventListener('change', updatePartialSubmitButton);
    document.getElementById('partial_minutes').addEventListener('change', updatePartialSubmitButton);

    // Initialize
    showFullTab();
});
</script>
@endsection