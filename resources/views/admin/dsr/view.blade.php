{{-- resources/views/admin/dsr/view.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'DSR Report Details')

@section('content')
<div class="max-w-5xl mx-auto py-12 px-6">

    <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-200">

        {{-- HEADER – COMPACT & THEME COLOR #0d9488 --}}
        <div class="bg-[#0d9488] text-white p-5">
            <h1 class="text-3xl font-extrabold mb-4">DSR Report Details</h1>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-base">
                <div>
                    <p class="text-teal-200 font-medium text-xs">Project Manager</p>
                    <p class="text-lg font-bold">{{ $report->pm->name }}</p>
                    <p class="text-teal-200 text-xs">(Project Manager)</p>
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
                    <p class="text-teal-200 font-medium text-xs">PM DSR Rating</p>
                    <p class="text-2xl font-black text-yellow-300">{{ $report->rating }}/10</p>
                </div>
            </div>

            <p class="mt-4 text-teal-100 bg-[#0b7a70] inline-block px-3 py-1 rounded-full text-xs font-medium">
                📅 Week: {{ $report->report_date->startOfWeek()->format('d M') }} – {{ $report->report_date->endOfWeek()->format('d M Y') }}
            </p>
        </div>

        {{-- MAIN CONTENT --}}
        <div class="p-10 space-y-12">

            {{-- DAILY TASK STATUS – THEME COLOR ACCENTS --}}
            <div>
                <h2 class="text-2xl font-bold text-[#0d9488] mb-6 border-b-2 border-[#0d9488] pb-3">
                    Daily Task Status (with Proofs)
                </h2>

                @php
                    $booleanTasks = [
                        'follow_paused_clients' => 'Followed Paused Clients',
                        'follow_closed_clients' => 'Followed Closed Clients',
                        'upsell_clients'        => 'Upsell Clients',
                        'referral_client'       => 'Referral Client',
                        'checked_teammate_dsr'  => 'Checked Teammate DSR',
                        'audited_project'       => 'Audited Project',
                    ];

                    $proofs = is_string($report->proofs) ? json_decode($report->proofs, true) : ($report->proofs ?? []);
                    $proofs = is_array($proofs) ? $proofs : [];
                @endphp

                <table class="w-full text-sm border border-gray-300 rounded-lg overflow-hidden">
                    @foreach($booleanTasks as $key => $label)
                        <tr class="border-b hover:bg-teal-50">
                            <td class="p-4 bg-gray-100 font-medium w-2/5">{{ $label }}</td>
                            <td class="p-4 w-1/5 text-center">
                                @if($report->$key)
                                    <span class="px-4 py-1 bg-green-100 text-green-800 rounded-full font-semibold">YES</span>
                                @else
                                    <span class="px-4 py-1 bg-red-100 text-red-700 rounded-full font-semibold">NO</span>
                                @endif
                            </td>
                            <td class="p-4 w-2/5">
                                @if(isset($proofs[$key]))
                                    @php $data = $proofs[$key]; @endphp
                                    @if(($data['answer'] ?? '') === 'yes' && isset($data['proof']))
                                        <a href="{{ $data['proof']['url'] ?? '#' }}" target="_blank"
                                           class="inline-flex items-center gap-2 text-[#0d9488] hover:underline font-medium">
                                            📎 {{ $data['proof']['name'] ?? 'View Proof' }}
                                        </a>
                                    @elseif(($data['answer'] ?? '') === 'no')
                                        <p class="text-red-600">
                                            <strong>Reason:</strong> {{ $data['proof']['description'] ?? 'Not provided' }}
                                        </p>
                                    @endif
                                @else
                                    <span class="text-gray-400 italic">— No proof submitted —</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>

            {{-- INVOICES & PAYMENTS – THEME ACCENTS --}}
            <div>
                <h2 class="text-2xl font-bold text-[#0d9488] mb-6 border-b-2 border-[#0d9488] pb-3">
                    Invoices & Payments
                </h2>

                <ul class="space-y-3 text-base">
                    <li><strong>Invoices Sent:</strong> {{ $report->invoices_sent }}</li>
                    <li><strong>Invoices Pending:</strong> {{ $report->invoices_pending }}</li>
                    <li><strong>Payment Follow-ups:</strong> {{ $report->payment_followups }}</li>
                </ul>

                @php
                    $screenshots = is_string($report->payment_screenshots)
                        ? json_decode($report->payment_screenshots, true)
                        : ($report->payment_screenshots ?? []);
                @endphp

                @if(!empty($screenshots))
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-[#0d9488] mb-3">Payment Screenshots</h3>
                        <ul class="space-y-2 text-sm">
                            @foreach($screenshots as $file)
                                <li class="flex items-center gap-2">
                                    <span class="text-gray-500">📸</span>
                                    <a href="{{ $file['url'] ?? '#' }}" target="_blank"
                                       class="text-[#0d9488] hover:underline">
                                        {{ $file['name'] ?? 'Screenshot' }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            {{-- HAPPY THINGS – THEME ACCENTS --}}
            <div>
                <h2 class="text-2xl font-bold text-[#0d9488] mb-6 border-b-2 border-[#0d9488] pb-3">
                    Happy Things
                </h2>

                <ul class="space-y-3 text-base">
                    <li><strong>Paused Today:</strong> {{ $report->paused_today }}</li>
                    <li><strong>Restarted Today:</strong> {{ $report->restarted_today }}</li>
                    <li><strong>Closed Today:</strong> {{ $report->closed_today }}</li>
                </ul>
            </div>

            {{-- PRODUCTION WORK – FIXED TEXT WRAPPING & CLEAN DESIGN --}}
<div>
    <h2 class="text-2xl font-bold text-[#0d9488] mb-6 border-b-2 border-[#0d9488] pb-3">
        Production Work
    </h2>

    {{-- NEW: Show Selected Projects – FIXED TEXT BREAKING --}}
    @php
        $selectedProjectIds = $report->production_projects 
            ? (is_string($report->production_projects) 
                ? json_decode($report->production_projects, true) 
                : $report->production_projects)
            : [];

        $selectedProjects = \App\Models\Project::whereIn('id', $selectedProjectIds)
            ->with('country') // Optional: if you want flags or extra info
            ->orderBy('name_or_url')
            ->get();
    @endphp

    @if($selectedProjects->isNotEmpty())
        <div class="mb-8 bg-teal-50 border border-[#0d9488] rounded-xl p-6">
            <h3 class="text-xl font-bold text-[#0d9488] mb-5">Projects Worked On Today</h3>
            <div class="space-y-5">
                @foreach($selectedProjects as $project)
                    <div class="bg-white rounded-lg px-6 py-4 shadow-sm border border-gray-200 hover:border-[#0d9488] transition">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <a href="{{ $project->dashboard_url ?? '#' }}" 
                                   target="_blank"
                                   class="text-lg font-semibold text-[#0d9488] hover:underline break-words">
                                    {{ $project->name_or_url }}
                                </a>
                                @if($project->client_name)
                                    <p class="text-sm text-gray-600 mt-1">
                                        <strong>Client:</strong> {{ $project->client_name }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="mb-8 bg-gray-50 border border-gray-200 rounded-xl p-6">
            <p class="text-gray-600 italic text-center">No projects selected for today</p>
        </div>
    @endif

    {{-- Existing Production Work Fields --}}
    <ul class="space-y-4 text-base">
        <li><strong>Meetings Completed:</strong> {{ $report->meetings_completed }}</li>
        <li><strong>Client Queries Resolved:</strong> {{ $report->client_queries_resolved ?? 'None recorded' }}</li>
    </ul>

    @if($report->additional_tasks)
        <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-5">
            <p class="font-semibold text-[#0d9488] mb-2">Additional Tasks / Notes</p>
            <p class="text-gray-800 leading-relaxed whitespace-pre-line">{{ $report->additional_tasks }}</p>
        </div>
    @endif
</div>

            {{-- HOURS SPENT – THEME ACCENTS --}}
            @php
                $hours = is_string($report->task_hours)
                    ? json_decode($report->task_hours, true)
                    : ($report->task_hours ?? []);
            @endphp

            @if(!empty($hours))
                <div>
                    <h2 class="text-2xl font-bold text-[#0d9488] mb-6 border-b-2 border-[#0d9488] pb-3">
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

        {{-- FOOTER – THEME COLOR BACK BUTTON --}}
        <div class="bg-gray-100 px-8 py-5 flex justify-between items-center border-t">
            <a href="{{ route('admin.dsr.show', $report->pm_id) }}"
               class="inline-flex items-center text-[#0d9488] hover:text-[#0b7a70] font-semibold text-lg transition">
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