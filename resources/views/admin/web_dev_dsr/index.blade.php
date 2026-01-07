@extends('layouts.dashboard')

@section('title', 'Web Dev PM DSR Reports')

@section('content')
<div class="max-w-6xl mx-auto py-8 px-6">
    <h1 class="text-4xl font-bold text-[#0d9488] mb-8">Web Development PM DSR Reports</h1>

    @if($reports->isEmpty())
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-8 text-center">
            <p class="text-xl text-yellow-800">No reports submitted yet.</p>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <table class="w-full">
                <thead class="bg-[#0d9488] text-white">
                    <tr>
                        <th class="p-4 text-left">Date</th>
                        <th class="p-4 text-left">PM Name</th>
                        <th class="p-4 text-center">Rating</th>
                        <th class="p-4 text-center">Submitted At</th>
                        <th class="p-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                        <tr class="border-b hover:bg-teal-50 transition">
                            <td class="p-4 font-medium">{{ $report->report_date->format('d M Y') }}</td>
                            <td class="p-4">{{ $report->pm->name }}</td>
                            <td class="p-4 text-center">
                                <span class="px-4 py-2 rounded-full text-white font-bold {{ $report->rating >= 8 ? 'bg-green-600' : ($report->rating >= 5 ? 'bg-yellow-600' : 'bg-red-600') }}">
                                    {{ $report->rating }}/10
                                </span>
                            </td>
                            <td class="p-4 text-center text-sm text-gray-600">{{ $report->created_at->format('d M Y h:i A') }}</td>
                            <td class="p-4 text-center">
                                <a href="{{ route('admin.web.dev.dsr.view', $report->id) }}" 
                                   class="inline-block bg-[#0d9488] text-white px-6 py-2 rounded-lg hover:bg-[#0b7a70] transition font-medium">
                                    View Report
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="p-4">
                {{ $reports->links() }}
            </div>
        </div>
    @endif
</div>
@endsection