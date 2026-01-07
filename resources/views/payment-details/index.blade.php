@extends('layouts.dashboard')

@section('content')

<form method="GET" class="mb-6 grid grid-cols-1 sm:grid-cols-4 gap-4">
  

    <!-- Project Month Filter -->
    <div>
        <label for="project_month" class="block text-sm font-medium text-gray-700">Select Month</label>
        <input type="month" name="project_month" id="project_month"
               value="{{ request('project_month') }}"
               class="w-full border border-gray-300 rounded px-3 py-2" />
    </div>

   
    <!-- Submit Button -->
    <div class="flex items-end">
        <button type="submit"
                class="text-white bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600 hover:bg-gradient-to-br px-5 py-2 rounded-md shadow hover:shadow-lg transition-all">
            Filter
        </button>
    </div>
</form>

<div class="container mx-auto px-4">
    <h2 class="text-3xl font-bold mb-8 text-center text-gray-800">Payment Details</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($bankAccounts as $bank)
            <div class="p-4 border rounded-xl bg-white shadow-md">

                <!-- Total payment received -->
                <div class="rounded-t-lg relative text-white bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600 hover:bg-gradient-to-br rounded-1xl p-3">
                    <h4 class="text-sm font-bold uppercase">Total Received</h4>
                    <p class="text-xl font-extrabold mt-1">${{ number_format($payments[$bank->id] ?? 0, 2) }}</p>
                </div>
               
                <!-- Account info -->
                <div class="mt-3 text-sm space-y-1">
                    <p><strong class="text-gray-700">Bank:</strong> {{ $bank->account_name }}</p>
                    <p><strong class="text-gray-700">A/C No:</strong> {{ $bank->account_number }}</p>
                    <p><strong class="text-gray-700">IFSC:</strong> {{ $bank->ifsc_code }}</p>
                    <p><strong class="text-gray-700">Branch:</strong> {{ $bank->branch }}</p>
                </div>

                <!-- Department-wise payments -->
                <div class="mt-3">
                    <h5 class="text-xs font-bold text-gray-600 mb-2">By Department</h5>
                    @if(!empty($departmentPayments[$bank->id]))
                        <ul class="space-y-1 text-xs">
                            @foreach($departmentPayments[$bank->id] as $deptName => $amount)
                                <li>
                                    <span class="text-gray-700">{{ $deptName }}:</span>
                                    <span class="font-semibold text-green-700">${{ number_format($amount, 2) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-gray-400 text-xs">No department data</p>
                    @endif
                </div>

            </div>
        @endforeach
    </div>
</div>
@endsection
