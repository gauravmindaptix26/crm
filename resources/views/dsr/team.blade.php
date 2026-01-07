@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto py-10">
    <h2 class="text-3xl font-bold mb-6">Team DSR Reports</h2>
    @if(auth()->user()->hasRole('Admin') || auth()->user()->hasRole('HR'))
    <div class="mb-4">
        <a href="{{ route('employee.all.dsr') }}" 
           class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">
            Single Employee DSR
        </a>
    </div>
    @endif
    <!-- Table -->
    <table class="min-w-full bg-white border border-gray-300 shadow-md rounded-lg">
        <thead>
            <tr class="bg-gray-100">
                <th class="py-3 px-6 text-left">SR No</th>
                <th class="py-3 px-6 text-left">Team Member</th>
                <th class="py-3 px-6 text-left">Date of DSR</th>
                <th class="py-3 px-6 text-left">Hours in DSR</th>
                <th class="py-3 px-6 text-left">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dsrReports as $index => $report)
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-6">{{ $dsrReports->firstItem() + $index }}</td> <!-- Updated for continuous numbering -->
                    <td class="py-3 px-6">
                        {{ \App\Models\User::find($report->user_id)?->name ?? 'Unknown User' }}
                    </td>
                    <td class="py-3 px-6">{{ $report->report_date }}</td>
                    <td class="py-3 px-6">{{ $report->total_hours }}</td>
                    <td class="py-3 px-6">
                        <a href="{{ url('/team-dsr/view/' . $report->user_id . '/' . $report->report_date) }}" 
                           class="text-blue-500 hover:underline">
                           View
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $dsrReports->links() }}
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="dsrDetailsModal" tabindex="-1" aria-labelledby="dsrDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="dsrDetailsModalLabel">DSR Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="dsrDetailsContent">
        <!-- DSR content will load here -->
      </div>
    </div>
  </div>
</div>
@endsection
