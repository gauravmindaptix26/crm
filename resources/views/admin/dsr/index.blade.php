{{-- resources/views/admin/dsr/index.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'SEO Project Managers - Current Daily Status')

@section('content')
<div class="container mx-auto py-8 px-6 max-w-7xl">
    <h1 class="text-4xl font-bold text-center mb-12 text-[#0d9488]">
        SEO Project Managers -  Daily Status Report
    </h1>

    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-200">
        <table class="w-full">
            <thead class="bg-[#0d9488] text-white">
                <tr>
                    <th class="p-6 text-left text-lg font-semibold">Project Manager</th>
                    <th class="p-6 text-center text-lg font-semibold">Latest Daily Report</th>
                    <th class="p-6 text-center text-lg font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($projectManagers as $pm)
                    <tr class="hover:bg-teal-50 transition duration-200 cursor-pointer"
                        onclick="window.location='{{ route('admin.dsr.show', $pm->id) }}'">
                        <td class="p-6">
                            <p class="text-xl font-bold text-gray-800">{{ $pm->name }}</p>
                        </td>

                     <!-- LATEST DAILY REPORT – NOW WITH VIEW BUTTON -->
                     <td class="p-6 text-center" onclick="event.stopPropagation();">
                            @if($pm->latestDailyDsr)
                                <div class="space-y-2">
                                    <a href="{{ route('admin.dsr.view', $pm->latestDailyDsr->id) }}"
                                       class="block hover:text-[#0d9488] transition">
                                        <p class="text-lg font-medium text-gray-800">
                                            {{ $pm->latestDailyDsr->report_date->format('d M Y') }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{ $pm->latestDailyDsr->report_date->format('l') }}
                                            @if(!$pm->latestDailyDsr->report_date->isToday())
                                                <span class="block text-orange-600 text-xs mt-1">
                                                    ({{ $pm->latestDailyDsr->report_date->diffForHumans() }})
                                                </span>
                                            @endif
                                        </p>
                                    </a>

                                    <!-- NEW: VIEW DAILY REPORT BUTTON -->
                                    <a href="{{ route('admin.dsr.view', $pm->latestDailyDsr->id) }}"
                                       class="inline-block mt-3 bg-[#0d9488] text-white px-6 py-2 rounded-lg hover:bg-[#0b7a70] font-medium text-sm transition shadow">
                                        View Daily Report →
                                    </a>
                                </div>
                            @else
                                <p class="text-red-600 font-semibold">No daily report ever submitted</p>
                            @endif
                        </td>

                        <td class="p-6 text-center" onclick="event.stopPropagation();">
                            <a href="{{ route('admin.dsr.show', $pm->id) }}"
                               class="inline-block bg-[#0d9488] text-white px-10 py-4 rounded-xl hover:bg-[#0b7a70] font-bold text-lg transition shadow-lg">
                                View All Reports →
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="p-16 text-center text-gray-500 text-2xl">
                            No active SEO Project Managers found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- COO Status Modal & JavaScript (keep your existing working version) -->
@endsection