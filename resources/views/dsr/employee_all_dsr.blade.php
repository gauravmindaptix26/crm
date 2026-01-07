@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto py-10">
    <h2 class="text-3xl font-bold mb-8 text-gray-800">Single Employee DSR Report</h2>

    <form method="POST" action="{{ route('employee.all.dsr.search') }}" class="mb-8 bg-white p-6 rounded shadow">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div>
            <label class="block mb-1 font-semibold text-gray-700">Select Employee</label>
            <select name="user_id" class="w-full border-gray-300 rounded shadow-sm">
                <option value="">-- Select --</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ old('user_id', $user->id ?? '') == $u->id ? 'selected' : '' }}>
                        {{ $u->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block mb-1 font-semibold text-gray-700">Month</label>
            <select name="month" class="w-full border-gray-300 rounded shadow-sm">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ old('month', $month) == $m ? 'selected' : '' }}>
                        {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block mb-1 font-semibold text-gray-700">Year</label>
            <select name="year" class="w-full border-gray-300 rounded shadow-sm">
                @for($y = date('Y'); $y >= 2020; $y--)
                    <option value="{{ $y }}" {{ old('year', $year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>

        <div class="flex items-end">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded shadow">
                View Report
            </button>
        </div>
    </div>
</form>

    @isset($dsrReports)
    <div class="bg-white p-6 rounded shadow">
        <h3 class="text-xl font-semibold mb-4 text-gray-800">
            {{ $user->name }} - {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }} Summary
        </h3>

        @php
            $totalAssignedHours = 0;
            $totalWorkedHours = 0;
            $groupedProjects = $dsrDetails->groupBy('project_id');
        @endphp

        <!-- Summary Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300 rounded shadow mb-8">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="py-3 px-4 text-left">SR No.</th>
                        <th class="py-3 px-4 text-left">Project Name</th>
                        <th class="py-3 px-4 text-left">Assigned Hours</th>
                        <th class="py-3 px-4 text-left">Worked Hours</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupedProjects as $projectId => $group)
                        @php
                            $project = \App\Models\Project::find($projectId);
                            $assigned = $project->estimated_hours ?? 0;
                            $worked = $group->sum('hours');
                            $totalAssignedHours += $assigned;
                            $totalWorkedHours += $worked;
                        @endphp
                        <tr class="hover:bg-gray-50 border-t">
                            <td class="py-2 px-4">{{ $loop->iteration }}</td>
                            <td class="py-2 px-4">{{ $project->name_or_url ?? 'N/A' }}</td>
                            <td class="py-2 px-4">{{ $assigned }}</td>
                            <td class="py-2 px-4">{{ $worked }}</td>
                        </tr>
                    @endforeach

                    <!-- Totals Row -->
                    <tr class="bg-gray-200 text-gray-800 font-semibold text-sm border-t">
                        <td colspan="4" class="py-3 px-4 text-center">
                            Total Projects: {{ $groupedProjects->count() }} &nbsp;&nbsp;|&nbsp;&nbsp;
                            Assigned Hours: {{ $totalAssignedHours }} &nbsp;&nbsp;|&nbsp;&nbsp;
                            Worked Hours: {{ $totalWorkedHours }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Daily Work Table -->
        <h4 class="text-lg font-semibold mb-4 text-gray-800">Daily Work Details</h4>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300 rounded shadow">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="py-3 px-4 text-left">SR No.</th>
                        <th class="py-3 px-4 text-left">Project Name</th>
                        <th class="py-3 px-4 text-left">Work Details</th>
                        <th class="py-3 px-4 text-left">Date</th>
                        <th class="py-3 px-4 text-left">Hours</th>
                        <th class="py-3 px-4 text-left">Someone Helped</th>
                        <th class="py-3 px-4 text-left">Manager Comments</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dsrDetails as $detail)
                        <tr class="hover:bg-gray-50 border-t">
                            <td class="py-2 px-4">{{ $loop->iteration }}</td>
                            <td class="py-2 px-4">{{ $detail->project->name_or_url ?? 'N/A' }}</td>
                            <td class="py-2 px-4 text-sm text-gray-700">{{ Str::words($detail->work_description, 50, '...') }}</td>
                            <td class="py-2 px-4">{{ $detail->date }}</td>
                            <td class="py-2 px-4">{{ $detail->hours }}</td>
                            <td class="py-2 px-4">{{ $detail->someone_helped }}</td>
                            <td class="py-2 px-4 text-sm text-gray-600">{{ $detail->manager_comments }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endisset
</div>
@endsection
