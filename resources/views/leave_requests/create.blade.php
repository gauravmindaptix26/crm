@extends('layouts.dashboard')

@section('title', 'Request Leave')

@section('content')
<div class="bg-white shadow-md rounded-lg p-6">
    <h2 class="text-2xl font-semibold text-gray-800 mb-5">Request Leave</h2>
    
    {{-- Error Display --}}
    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-400 rounded">
            <ul class="text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    {{-- Show Policies Debug --}}
    @if($policies->isEmpty())
        <div class="mb-4 p-4 bg-yellow-100 border border-yellow-400 rounded">
            <p class="text-yellow-700">No leave policies found. Please contact administrator to create leave policies.</p>
        </div>
    @endif
    
    {{-- Current Balances --}}
    @if(isset($balances) && $balances->isNotEmpty())
        <div class="mb-6 p-4 bg-blue-50 rounded-lg">
            <h3 class="font-semibold mb-2">Current Leave Balances:</h3>
            @foreach($balances as $balance)
                <div class="flex justify-between py-1 text-sm">
                    <span>{{ $balance->leavePolicy->name }}:</span>
                    <span class="font-semibold {{ $balance->balance > 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($balance->balance, 2) }} days
                    </span>
                </div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('leave-requests.store') }}">
        @csrf
        
        <div class="mb-4">
            <label for="leave_policy_id" class="block text-sm font-medium text-gray-700">Leave Type *</label>
            <select id="leave_policy_id" name="leave_policy_id" class="w-full px-4 py-2 border rounded-md" required>
                <option value="">Select Leave Type</option>
                @foreach ($policies as $policy)
                    <option value="{{ $policy->id }}" {{ old('leave_policy_id') == $policy->id ? 'selected' : '' }}>
                        {{ $policy->name }} 
                        @if($policy->days_per_quarter == 9999)
                            (Unlimited)
                        @else
                            ({{ $policy->days_per_quarter }} days/quarter)
                        @endif
                    </option>
                @endforeach
            </select>
            @error('leave_policy_id')
                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>
        
        <div class="mb-4">
            <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date *</label>
            <input type="date" id="start_date" name="start_date" 
                   value="{{ old('start_date') }}" 
                   class="w-full px-4 py-2 border rounded-md" required 
                   min="{{ date('Y-m-d') }}">
            @error('start_date')
                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>
        
        <div class="mb-4">
            <label for="end_date" class="block text-sm font-medium text-gray-700">End Date (optional)</label>
            <input type="date" id="end_date" name="end_date" 
                   value="{{ old('end_date') }}" 
                   class="w-full px-4 py-2 border rounded-md"
                   min="{{ old('start_date', date('Y-m-d')) }}">
        </div>
        
        <div class="mb-4">
            <label for="duration" class="block text-sm font-medium text-gray-700">Duration (days) *</label>
            <input type="number" step="0.25" id="duration" name="duration" 
                   value="{{ old('duration', 1) }}" 
                   class="w-full px-4 py-2 border rounded-md" required min="0.25" max="30">
            <p class="text-xs text-gray-500 mt-1">Use 0.25 for quarter day, 0.5 for half day, 1 for full day</p>
            @error('duration')
                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>
        
        <div class="mb-4">
            <label for="reason" class="block text-sm font-medium text-gray-700">Reason</label>
            <textarea id="reason" name="reason" rows="3" 
                      class="w-full px-4 py-2 border rounded-md">{{ old('reason') }}</textarea>
            @error('reason')
                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>
        
        <div class="flex justify-end space-x-3">
            <a href="{{ route('leave-requests.index') }}" 
               class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Submit Request
            </button>
        </div>
    </form>
</div>
@endsection