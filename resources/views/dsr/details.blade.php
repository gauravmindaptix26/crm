@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto py-10">
    <h2 class="text-3xl font-bold mb-6">
        DSR Report - {{ $dsr->user->name }} ({{ $dsr->created_at->format('Y-m-d') }})
    </h2>

    @foreach ($dsrReports as $report)
        <div class="bg-white border border-gray-300 rounded-lg shadow-md mb-6 p-6">
            <h3 class="text-xl font-semibold mb-2">
                Project Name:
                {{ $report->project->name_or_url ?? 'N/A' }}
            </h3>

            <p><strong>Hours:</strong> {{ $report->hours }}</p>

            <div class="mb-3">
                <strong>Work Details:</strong>
                <p class="whitespace-pre-line">{{ $report->work_description }}</p>
            </div>

            @if ($report->links)
                <div class="mb-3">
                    <strong>Related Links:</strong>
                    <div class="text-blue-600">
                        {!! nl2br(e($report->links)) !!}
                    </div>
                </div>
            @endif

            @if ($report->helped_by || $report->help_description)
                <div class="mb-3">
                    <strong>Helped By:</strong>
                    {{ optional($report->helper)->name ?? 'N/A' }}
                </div>
                <div class="mb-3">
                    <strong>Help Description:</strong>
                    <p class="whitespace-pre-line">{{ $report->help_description }}</p>
                </div>
            @endif

            @if ($report->help_rating)
                <p><strong>Rating for the work:</strong> {{ $report->help_rating }}</p>
            @endif
        </div>
    @endforeach

    <div class="mt-6">
        <a href="{{ url()->previous() }}" class="text-blue-600 hover:underline">← Back</a>
    </div>
</div>
@endsection
