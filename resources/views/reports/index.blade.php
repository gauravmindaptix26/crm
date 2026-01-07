@extends('layouts.dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h2 class="text-3xl font-extrabold text-gray-800 mb-8 text-center">Team Report</h2>

    {{-- Filters --}}
    <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 bg-white p-6 rounded-xl shadow-md mb-10">
        <div>
            <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
            <select name="department_id" id="department" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="project_manager" class="block text-sm font-medium text-gray-700 mb-1">Project Manager</label>
            <select name="manager_id" id="project_manager" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                <option value="">-- All Managers --</option>
                @foreach ($projectManagers as $manager)
                    <option value="{{ $manager->id }}" {{ $managerId == $manager->id ? 'selected' : '' }}>
                        {{ $manager->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Month</label>
            <select name="month" id="month" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                    </option>
                @endfor
            </select>
        </div>

        <div>
            <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Year</label>
            <select name="year" id="year" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                @for ($y = now()->year; $y >= 2020; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>

        <div class="flex items-end">
            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition shadow">
                🔍 Search
            </button>
        </div>
    </form>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 mb-10">
        <div class="bg-white p-6 rounded-xl shadow-md">
            <h5 class="text-lg font-medium text-gray-700">Total Employees</h5>
            <p class="text-2xl text-blue-600">{{ $report['total_employees'] }}</p>
        </div>

        <!-- <div class="bg-white p-6 rounded-xl shadow-md">
            <h5 class="text-lg font-medium text-gray-700">Total Assigned</h5>
            <p class="text-2xl text-yellow-500">{{ $report['total_assigned_hours'] }} hrs</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-md">
            <h5 class="text-lg font-medium text-gray-700">Total Worked</h5>
            <p class="text-2xl text-teal-600">{{ $report['total_worked_hours'] }} hrs</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-md">
            <h5 class="text-lg font-medium text-gray-700">Total Payment</h5>
            <p class="text-2xl text-red-600">${{ number_format($report['total_payment']) }}</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-md">
            <h5 class="text-lg font-medium text-gray-700">Total Received</h5>
            <p class="text-2xl text-green-500">${{ number_format($report['total_received']) }}</p>
        </div> -->
    </div>

    {{-- Additional KPI --}}
    <!-- <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-white p-6 rounded-xl shadow-md">
            <h5 class="text-lg font-medium text-gray-700">Upsell Amount</h5>
            <p class="text-2xl text-purple-600">${{ number_format($report['total_upsell_amount'], 2) }}</p>
        </div>
    </div> -->

    {{-- Employee Table --}}
    <div class="bg-white p-6 rounded-xl shadow-md">
        <h4 class="text-xl font-extrabold text-gray-800 mb-6">Team Member Summary</h4>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm text-center">
                <thead class="bg-green-500 text-white uppercase text-xs font-semibold">
                    <tr>
                        <th class="px-6 py-3 text-left">#</th>
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3">Experience</th>
                        <th class="px-6 py-3">Assigned Hours</th>
                        <th class="px-6 py-3">Worked Hours</th>
                        <th class="px-6 py-3">Total Payment</th>
                        <th class="px-6 py-3">Received</th>
                        <!-- <th class="px-6 py-3">Upsell Amount</th> -->
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php
                        $totalAssignedHours = 0;
                        $totalWorkedHours = 0;
                        $totalPayment = 0;
                        $totalReceivedPayment = 0;
                        $totalUpsellAmount = 0;
                    @endphp

                  @foreach ($users as $index => $member)
    @php
        $experience = floatval(preg_replace('/[^0-9.]/', '', $member->experience ?? 0));
        $upsellPercent = floatval(preg_replace('/[^0-9.]/', '', $member->upsell_incentive ?? 0));

        $assignedProjects = \App\Models\Project::where('department_id', $departmentId)
            ->where(function($q) use ($member) {
                $q->where('assign_main_employee_id', $member->id)
                  ->orWhereJsonContains('additional_employees', (string) $member->id);
            })->get();

        $assignedHours = $assignedProjects->sum('estimated_hours');
        $paymentForProjects = $assignedProjects->sum('price');

        $workedHours = \App\Models\DSR::where('user_id', $member->id)
            ->whereBetween('created_at', [
                \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth(),
                \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth()
            ])
            ->sum('hours');

        $receivedPayment = \App\Models\ProjectPayment::whereIn('project_id', $assignedProjects->pluck('id'))
            ->whereBetween('payment_month', [
                \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth(),
                \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth()
            ])
            ->sum('payment_amount');

        $upsellAmount = $upsellPercent;

        // Accumulate totals
        $totalAssignedHours += $assignedHours;
        $totalWorkedHours += $workedHours;
        $totalPayment += $paymentForProjects;
        $totalReceivedPayment += $receivedPayment;
        $totalUpsellAmount += $upsellAmount;

       
    @endphp
    <tr class="hover:bg-gray-50 transition {{ $member->roles->contains('name', 'Project Manager') ? 'bg-yellow-400' : '' }}">
    <td class="px-6 py-4 text-left">{{ $index + 1 }}</td>
        <td class="px-6 py-4">{{ $member->name }}</td>
        <td class="px-6 py-4">{{ $member->email }}</td>
        <td class="px-6 py-4">{{ $experience }} yrs</td>
        <td class="px-6 py-4">{{ $assignedHours }}</td>
        <td class="px-6 py-4">{{ $workedHours }}</td>
        <td class="px-6 py-4">${{ number_format($paymentForProjects) }}</td>
        <td class="px-6 py-4">${{ number_format($receivedPayment) }}</td>
        <!-- <td class="px-6 py-4">${{ number_format($upsellAmount, 2) }}</td> -->
    </tr>
@endforeach

                </tbody>

                {{-- Footer with Total Row --}}
                <tfoot class="bg-green-500 text-white font-semibold">
                    <tr>
                        <td colspan="4" class="px-6 py-3 text-left">Totals:</td>
                        <td class="px-6 py-3">{{ $totalAssignedHours }}</td>
                        <td class="px-6 py-3">{{ $totalWorkedHours }}</td>
                        <td class="px-6 py-3">${{ number_format($totalPayment, 2) }}</td>
                        <td class="px-6 py-3">${{ number_format($totalReceivedPayment, 2) }}</td>
                        <!-- <td class="px-6 py-3">${{ number_format($totalUpsellAmount, 2) }}</td> -->
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
