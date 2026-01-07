@extends('layouts.dashboard')

@section('title', 'Web Dev DSR Report Details')

@section('content')
<div class="max-w-5xl mx-auto py-12 px-6">

    <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-200">

        {{-- HEADER --}}
        <div class="bg-[#0d9488] text-white p-5">
            <h1 class="text-3xl font-extrabold mb-4">Web Dev DSR Report Details</h1>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-base">
                <div>
                    <p class="text-teal-200 font-medium text-xs">Project Manager</p>
                    <p class="text-lg font-bold">{{ $report->pm->name }}</p>
                    <p class="text-teal-200 text-xs">(Web Dev PM)</p>
                </div>
                <div>
                    <p class="text-teal-200 font-medium text-xs">Report Date</p>
                    <p class="text-lg font-bold">{{ $report->report_date->format('d M Y') }}</p>
                    <p class="text-teal-200 text-xs">{{ $report->report_date->format('l') }}</p>
                </div>
                <div>
                    <p class="text-teal-200 font-medium text-xs">Report Type</p>
                    <p class="text-lg font-bold capitalize">{{ $report->type }}</p>
                </div>
                <div>
                    <p class="text-teal-200 font-medium text-xs">PM Rating</p>
                    <p class="text-2xl font-black text-yellow-300">{{ $report->rating }}/10</p>
                </div>
            </div>

            <p class="mt-4 text-teal-100 bg-[#0b7a70] inline-block px-3 py-1 rounded-full text-xs font-medium">
                📅 Week: {{ $report->report_date->startOfWeek()->format('d M') }} – {{ $report->report_date->endOfWeek()->format('d M Y') }}
            </p>
        </div>

        {{-- MAIN CONTENT --}}
        <div class="p-10 space-y-12">

            {{-- Marketplace Job --}}
            <div>
                <h2 class="text-2xl font-bold text-[#0d9488] mb-6 border-b-2 border-[#0d9488] pb-3">
                    Marketplace Job
                </h2>
                <ul class="space-y-3 text-base">
                    <li><strong>Upwork Bids:</strong> {{ $report->upwork_bids }}</li>
                    <li><strong>PPH Bids:</strong> {{ $report->pph_bids }}</li>
                    <li><strong>Fiverr Maintain:</strong> {{ $report->fiverr_maintain }}</li>
                    <li><strong>Dribbble Jobs:</strong> {{ $report->dribbble_jobs }}</li>
                    <li><strong>Online Jobs Apply:</strong> {{ $report->online_jobs_apply }}</li>
                </ul>
                @php $files = json_decode($report->marketplace_files, true) ?? []; @endphp
                @if(!empty($files))
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-[#0d9488] mb-3">Uploaded Files</h3>
                        <ul class="space-y-2 text-sm">
                            @foreach($files as $file)
                                <li class="flex items-center gap-2">
                                    <span class="text-gray-500">📸</span>
                                    <a href="{{ $file['url'] }}" target="_blank" class="text-[#0d9488] hover:underline">
                                        {{ $file['name'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            {{-- Old Client Management --}}
            <div>
                <h2 class="text-2xl font-bold text[#0d9488] mb-6 border-b-2 border-[#0d9488] pb-3">
                    Old Client Management
                </h2>
                <ul class="space-y-3 text-base">
                    <li><strong>Old Clients Design Work:</strong> {{ $report->old_clients_design }}</li>
                    <li><strong>Old Leads Asking for Work:</strong> {{ $report->old_leads_ask_work }}</li>
                </ul>
            </div>

            {{-- Project Coordinator Job --}}
            <div>
                <h2 class="text-2xl font-bold text[#0d9488] mb-6 border-b-2 border[#0d9488] pb-3">
                    Project Coordinator Job
                </h2>
                <ul class="space-y-3 text-base">
                    <li><strong>Client Communication:</strong> {{ $report->client_communication }}</li>
                    <li><strong>Current Client More Work:</strong> {{ $report->current_client_more_work }}</li>
                    <li><strong>Project Completion on Time:</strong> {{ $report->project_completion_on_time }}</li>
                    <li><strong>Meet PM for More Work:</strong> {{ $report->meet_pm_more_work }}</li>
                </ul>
            </div>

            {{-- Hours Spent --}}
            @php $hours = json_decode($report->task_hours, true) ?? []; @endphp
            @if(!empty($hours))
                <div>
                    <h2 class="text-2xl font-bold text[#0d9488] mb-6 border-b-2 border[#0d9488] pb-3">
                        Hours Spent on Tasks
                    </h2>
                    <table class="w-full text-sm border border-gray-300 rounded-lg overflow-hidden">
                        @foreach($hours as $task => $hour)
                            <tr class="border-b hover:bg-teal-50">
                                <td class="p-4 bg-gray-100 font-medium capitalize">
                                    {{ str_replace('_', ' ', $task) }}
                                </td>
                                <td class="p-4 text-right font-semibold text-gray-800">
                                    {{ $hour }} hrs
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            @endif

        </div>

        {{-- FOOTER --}}
        <div class="bg-gray-100 px-8 py-5 flex justify-between items-center border-t">
        <a href="{{ route('admin.web.dev.dsr.index') }}" class="inline-flex items-center text-[#0d9488] hover:text-[#0b7a70] font-semibold text-lg transition">
    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
    </svg>
    Back to Reports
</a>
            <p class="text-sm text-gray-600">
                Submitted on: <span class="font-medium">{{ $report->created_at->format('d M Y \a\t h:i A') }}</span>
            </p>
        </div>

    </div>
</div>
@endsection