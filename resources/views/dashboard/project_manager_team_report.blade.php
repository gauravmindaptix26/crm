@extends('layouts.dashboard')

@section('content')
<div class="p-6">
    <h2 class="text-2xl font-semibold mb-4">Team Report</h2>

    {{-- Filter Form --}}
    <form method="GET" action="{{ route('projectManager.teamReport') }}" class="mb-6">
        <div class="form-group flex gap-4 items-center">
            <label for="report_month" class="font-semibold">Select Month and Year:</label>

            <select name="report_month" class="form-control w-32">
                <option value="ALL" {{ $selectedMonth == 'ALL' ? 'selected' : '' }}>ALL</option>
                @foreach(range(1,12) as $month)
                    <option value="{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}"
                        {{ $selectedMonth == str_pad($month, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($month)->format('F') }}
                    </option>
                @endforeach
            </select>

            <select name="report_year" class="form-control w-32">
                @foreach(range(2019, now()->year + 1) as $year)
                    <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary px-4 py-2 rounded bg-blue-600 text-white">Filter</button>
        </div>
    </form>

    {{-- Stats Summary --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-gray-800 text-white p-4 rounded shadow">Total Employees: {{ $stats['totalEmployees'] }}</div>
        <div class="bg-gray-800 text-white p-4 rounded shadow">Total Assigned Hours: {{ $stats['totalAssignedHours'] }}</div>
        <div class="bg-gray-800 text-white p-4 rounded shadow">Total Worked Hours: {{ $stats['totalWorkedHours'] }}</div>
        <div class="bg-gray-800 text-white p-4 rounded shadow">Total Payment:$ {{ number_format($stats['totalPayment'], 2) }}</div>
        <div class="bg-gray-800 text-white p-4 rounded shadow">Total Received:$ {{ number_format($stats['totalReceived'], 2) }}</div>
        <div class="bg-gray-800 text-white p-4 rounded shadow">Total Upsell Amount: {{ number_format($stats['totalUpsellAmount'], 2) }}</div>
    </div>

    <h3 class="text-xl font-semibold mb-3">Team Members</h3>

    @php
        $currentUser = auth()->user();
    @endphp

    <div class="bg-white shadow rounded p-4">
        <table class="min-w-full table-auto border">
            <thead>
                <tr class="bg-gray-200 text-left">
                    <th class="p-2 border">#</th>
                    <th class="p-2 border">Name</th>
                    <th class="p-2 border">Experience</th>
                    <th class="p-2 border">Assigned Hours</th>
                    <th class="p-2 border">Worked Hours</th>
                    <th class="p-2 border">Total Payment</th>
                    <th class="p-2 border">Received Payment</th>
                    <th class="p-2 border">Upsell Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($membersData as $index => $member)
                    @php
                        $isProjectManager = $member['user']->id === $currentUser->id;
                    @endphp
                    <tr class="border-b {{ $isProjectManager ? 'bg-blue-50 font-semibold' : '' }}">
                        <td class="p-2 border">{{ $index + 1 }}</td>
                        <td class="p-2 border">
                            {{ $member['user']->name }}
                            @if($isProjectManager)
                                <span class="text-sm text-blue-600 font-bold">(PM)</span>
                            @endif
                        </td>
                        <td class="p-2 border">{{ $member['user']->experience ?? '-' }}</td>
                        <td class="p-2 border">{{ $member['assignedHours'] }}</td>
                        <td class="p-2 border">{{ $member['workedHours'] }}</td>
                        <td class="p-2 border">${{ number_format($member['payment'] ?? 0, 2) }}</td>
                        <td class="p-2 border">${{ number_format($member['received'] ?? 0, 2) }}</td>
                        <td class="p-2 border">
                            @if($isProjectManager)
                                {{ number_format($member['upsell'], 2) }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-gray-100 font-semibold">
                    <td class="p-2 border" colspan="3">Total Employees: {{ $stats['totalEmployees'] }}</td>
                    <td class="p-2 border">{{ $stats['totalAssignedHours'] }}</td>
                    <td class="p-2 border">{{ $stats['totalWorkedHours'] }}</td>
                    <td class="p-2 border">${{ number_format($stats['totalPayment'], 2) }}</td>
                    <td class="p-2 border">${{ number_format($stats['totalReceived'], 2) }}</td>
                    <td class="p-2 border">{{ number_format($stats['totalUpsellAmount'], 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
