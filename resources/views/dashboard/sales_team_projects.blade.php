@extends('layouts.dashboard')
@section('title', 'Sales Team Projects')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h2 class="text-3xl font-extrabold text-gray-800 mb-8 text-center">📊 Sales Team Projects - Monthly Report</h2>

    <!-- Filter Form -->
    <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 bg-white p-6 rounded-xl shadow-md mb-10">
        <div>
            <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Select Month</label>
            <select name="month" id="month" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                @foreach(range(1, 12) as $m)
                    <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ $month == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                        {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Select Year</label>
            <select name="year" id="year" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                @for ($y = now()->year; $y >= 2020; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>

        <div class="flex items-end">
            <button type="submit"
                class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition shadow">
                🔍 Search
            </button>
        </div>
    </form>

   <!-- Report Table -->
<div class="overflow-x-auto bg-white rounded-xl shadow-md ring-1 ring-gray-200">
    <table class="min-w-full divide-y divide-gray-200 text-sm text-center">
        <thead class="bg-gray-100 text-gray-700 uppercase text-xs font-semibold tracking-wider">
            <tr>
                <th class="px-6 py-3 text-left">Name</th>
                <th class="px-6 py-3">Projects</th>
                <th class="px-6 py-3">Amount</th>
                <th class="px-6 py-3">Complete</th>
                <th class="px-6 py-3">Pause</th>
                <th class="px-6 py-3">Issue</th>
                <th class="px-6 py-3">Hold</th>
                <th class="px-6 py-3">Rehire</th>
                <th class="px-6 py-3">Working</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @php
                $totalProjects = 0;
                $totalAmount = 0;
                $totalComplete = 0;
                $totalPaused = 0;
                $totalIssues = 0;
                $totalTempHold = 0;
                $totalRehire = 0;
                $totalWorking = 0;
            @endphp

            @forelse ($report as $row)
                @php
                    $totalProjects += $row['project_count'];
                    $totalAmount += $row['amount'];
                    $totalComplete += $row['complete']['count'];
                    $totalPaused += $row['paused']['count'];
                    $totalIssues += $row['issues']['count'];
                    $totalTempHold += $row['temp_hold']['count'];
                    $totalRehire += $row['rehire']['count'];
                    $totalWorking += $row['working'];
                @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 text-left font-medium text-gray-900">{{ $row['name'] }}</td>
                    <td class="px-6 py-4">{{ $row['project_count'] }}</td>
                    <td class="px-6 py-4 text-green-600 font-semibold">${{ number_format($row['amount'], 2) }}</td>
                    <td class="px-6 py-4">
    <button onclick="toggleDetails(this)" class="text-blue-600 hover:underline">
        {{ $row['complete']['count'] }}
    </button>
    @if(!empty($row['complete']['pms']))
        <div class="mt-1 hidden text-xs text-gray-600">
            <ul class="text-left list-disc list-inside">
                @foreach($row['complete']['pms'] as $pm)
                    <li>{{ $pm }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</td>

                    

                    <td class="px-6 py-4">
                        <button onclick="toggleDetails(this)" class="text-blue-600 hover:underline">
                            {{ $row['paused']['count'] }}
                        </button>
                        @if($row['paused']['pms']->count())
                            <div class="mt-1 hidden text-xs text-gray-600">
                                <ul class="text-left list-disc list-inside">
                                    @foreach($row['paused']['pms'] as $pm)
                                        <li>{{ $pm }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <button onclick="toggleDetails(this)" class="text-blue-600 hover:underline">
                            {{ $row['issues']['count'] }}
                        </button>
                        @if($row['issues']['pms']->count())
                            <div class="mt-1 hidden text-xs text-gray-600">
                                <ul class="text-left list-disc list-inside">
                                    @foreach($row['issues']['pms'] as $pm)
                                        <li>{{ $pm }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <button onclick="toggleDetails(this)" class="text-blue-600 hover:underline">
                            {{ $row['temp_hold']['count'] }}
                        </button>
                        @if($row['temp_hold']['pms']->count())
                            <div class="mt-1 hidden text-xs text-gray-600">
                                <ul class="text-left list-disc list-inside">
                                    @foreach($row['temp_hold']['pms'] as $pm)
                                        <li>{{ $pm }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <button onclick="toggleDetails(this)" class="text-blue-600 hover:underline">
                            {{ $row['rehire']['count'] }}
                        </button>
                        @if($row['rehire']['pms']->count())
                            <div class="mt-1 hidden text-xs text-gray-600">
                                <ul class="text-left list-disc list-inside">
                                    @foreach($row['rehire']['pms'] as $pm)
                                        <li>{{ $pm }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
    @if($row['working'] > 0)
        <a href="{{ route('sales.team.projects.working', [
            'sales_person_id' => $row['id'],
            'status' => 'working'
        ]) }}" class="text-blue-600 hover:underline">
            {{ $row['working'] }}
        </a>
    @else
        {{ $row['working'] }}
    @endif
</td>

                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-6 py-6 text-center text-gray-500">No data found for selected month and year.</td>
                </tr>
            @endforelse
        </tbody>

        @if(count($report))
        <tfoot class="bg-gray-100 font-semibold text-gray-800">
            <tr>
                <td class="px-6 py-4 text-left">Total</td>
                <td class="px-6 py-4">{{ $totalProjects }}</td>
                <td class="px-6 py-4 text-green-700">${{ number_format($totalAmount, 2) }}</td>
                <td class="px-6 py-4">{{ $totalComplete }}</td>
                <td class="px-6 py-4">{{ $totalPaused }}</td>
                <td class="px-6 py-4">{{ $totalIssues }}</td>
                <td class="px-6 py-4">{{ $totalTempHold }}</td>
                <td class="px-6 py-4">{{ $totalRehire }}</td>
                <td class="px-6 py-4">{{ $totalWorking }}</td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>
<script>
function toggleDetails(button) {
    // Find the next element sibling (skipping text nodes)
    let next = button.nextSibling;
    while (next && next.nodeType !== 1) {
        next = next.nextSibling;
    }

    if (next && next.classList.contains('hidden')) {
        next.classList.remove('hidden');
    } else if (next) {
        next.classList.add('hidden');
    }
}
</script>

@endsection
