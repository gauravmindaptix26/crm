@extends('layouts.dashboard')

@section('content')
<div id="closed-projects-dropdown-{{ $department_id }}" class="hidden absolute z-10 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200">
    <div class="p-2 bg-slate-800 text-white rounded-t-lg">
        <span class="text-sm font-semibold">Closed Projects - {{ $department_name }} ({{ $monthName }} {{ $currentYear }})</span>
    </div>
    <div class="p-2 divide-y divide-gray-200 max-h-60 overflow-y-auto">
        @forelse ($closedBreakdown as $manager)
            <a href="{{ route('projects.byStatus', ['status' => 'Closed,Complete']) }}?project_manager={{ $manager->project_manager_id }}&department_id={{ $department_id }}&report_month={{ $currentMonth }}&report_year={{ $currentYear }}"
               class="block py-2 px-2 text-sm text-gray-700 hover:bg-gray-100">
                {{ $manager->manager_name }} {{ $manager->team_name ? "({$manager->team_name})" : '(Project Manager)' }} - {{ $manager->count }}
            </a>
        @empty
            <div class="py-2 px-2 text-sm text-gray-500">No closed projects found.</div>
        @endforelse
    </div>
</div>
@endsection