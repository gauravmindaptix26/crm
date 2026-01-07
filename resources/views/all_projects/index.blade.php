@extends('layouts.dashboard')

@section('content')
<div class="p-6 bg-white shadow rounded-lg">
    <h1 class="text-2xl font-bold mb-4">All Projects</h1>

    {{-- Filter Form --}}
    <form method="GET" action="{{ $status === 'payment' ? route('projects.byPayment', request('department_id', $paginatedProjects->first()->department_id ?? '')) : route('projects.byStatus', $status) }}" class="grid grid-cols-1 md:grid-cols-7 gap-4 mb-6 items-end">
        <div>
            <label for="project_manager" class="block text-sm text-gray-600 mb-1">Project Manager</label>
            <select name="project_manager" id="project_manager" class="form-select rounded-lg border-gray-300 w-full">
                <option value="">-- Select --</option>
                @foreach ($projectManagers as $manager)
                    <option value="{{ $manager->id }}" {{ request('project_manager') == $manager->id ? 'selected' : '' }}>
                        {{ $manager->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="sales_person" class="block text-sm text-gray-600 mb-1">Sales Person</label>
            <select name="sales_person" id="sales_person" class="form-select rounded-lg border-gray-300 w-full">
                <option value="">-- Select --</option>
                @foreach ($salesPersons as $sales)
                    <option value="{{ $sales->id }}" {{ request('sales_person') == $sales->id ? 'selected' : '' }}>
                        {{ $sales->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="employee" class="block text-sm text-gray-600 mb-1">Employee</label>
            <select name="employee" id="employee" class="form-select rounded-lg border-gray-300 w-full">
                <option value="">-- Select --</option>
                @foreach ($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee') == $emp->id ? 'selected' : '' }}>
                        {{ $emp->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="department_id" class="block text-sm text-gray-600 mb-1">Department</label>
            <select name="department_id" id="department_id" class="form-select rounded-lg border-gray-300 w-full">
                <option value="">-- Select --</option>
                @foreach ($departments as $dep)
                    <option value="{{ $dep->id }}" {{ request('department_id') == $dep->id ? 'selected' : '' }}>
                        {{ $dep->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="report_month" class="block text-sm text-gray-600 mb-1">Month</label>
            <select name="report_month" id="report_month" class="form-select rounded-lg border-gray-300 w-full">
                @foreach(range(1, 12) as $month)
                    <option value="{{ sprintf('%02d', $month) }}"
                            {{ request('report_month', sprintf('%02d', now()->month)) == sprintf('%02d', $month) ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $month, 10)) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="report_year" class="block text-sm text-gray-600 mb-1">Year</label>
            <select name="report_year" id="report_year" class="form-select rounded-lg border-gray-300 w-full">
                @foreach(range(2030, 2020) as $year)
                    <option value="{{ $year }}" {{ request('report_year', now()->year) == $year ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="self-end">
            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700">
                Filter
            </button>
        </div>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-100 p-4 rounded-lg shadow text-center">
            <p class="text-sm text-gray-600">New Projects ({{ date('F', mktime(0, 0, 0, request('report_month', now()->month), 10)) }})</p>
            <h2 class="text-2xl font-bold text-blue-700">{{ $newProjectsCount }}</h2>
        </div>
        <div class="bg-green-100 p-4 rounded-lg shadow text-center">
            <p class="text-sm text-gray-600">Active Projects ({{ date('F', mktime(0, 0, 0, request('report_month', now()->month), 10)) }})</p>
            <h2 class="text-2xl font-bold text-green-700">{{ $activeProjectsCount }}</h2>
        </div>
        <div class="bg-yellow-100 p-4 rounded-lg shadow text-center">
            <p class="text-sm text-gray-600">Prediction Amount ({{ date('F', mktime(0, 0, 0, request('report_month', now()->month), 10)) }})</p>
            <h2 class="text-2xl font-bold text-yellow-700">${{ number_format($predictionAmount, 2) }}</h2>
        </div>
        <div class="bg-indigo-100 p-4 rounded-lg shadow text-center">
            <p class="text-sm text-gray-600">Amount Received ({{ date('F', mktime(0, 0, 0, request('report_month', now()->month), 10)) }})</p>
            <h2 class="text-2xl font-bold text-indigo-700">${{ number_format($amountReceived, 2) }}</h2>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto max-h-[500px]">
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-100 text-left text-sm font-semibold text-gray-600">
                        <th class="border border-gray-300 px-4 py-2">#</th>
                        <th class="border border-gray-300 px-4 py-2">Name/URL</th>
                        <th class="border border-gray-300 px-4 py-2">Office Details</th>
                        <th class="border border-gray-300 px-4 py-2">Price/Hours</th>
                        <th class="border border-gray-300 px-4 py-2">Added On</th>
                        <th class="border border-gray-300 px-4 py-2">Action</th>
                        <th class="border border-gray-300 px-4 py-2">Type</th>
                        <th class="border border-gray-300 px-4 py-2">Project Type</th>
                        <th class="border border-gray-300 px-4 py-2">Project Status</th>
                        <th class="border border-gray-300 px-4 py-2">Client Details</th>
                        <th class="border border-gray-300 px-4 py-2">Reason</th>

                        <th class="border border-gray-300 px-4 py-2">Attachment</th>
                        <th class="border border-gray-300 px-4 py-2">Description</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedProjects as $key => $project)
                        @php
                            $statusColors = [
                                'complete' => 'text-green-600 font-bold',
                                'working' => 'text-green-600 font-bold',
                                'hold' => 'text-yellow-600 font-bold',
                                'paused' => 'text-purple-600 font-bold',
                                'issues' => 'text-red-600 font-bold',
                                'temp hold' => 'text-orange-600 font-bold',
                                'closed' => 'text-gray-600 font-bold',
                            ];
                            $statusRaw = $project->project_status ?? 'working';
                            $status = strtolower(trim($statusRaw));
                            $statusClass = $statusColors[$status] ?? 'text-gray-500 font-bold';
                        @endphp
                        <tr class="text-sm text-gray-700">
                            <td class="border border-gray-300 px-4 py-2">SEODIS-{{ $paginatedProjects->firstItem() + $key }}</td>
                            <td class="border border-gray-300 px-4 py-2">
                                <strong>{{ $project->name_or_url }}</strong><br>
                                <a href="{{ $project->dashboard_url }}" target="_blank" class="text-blue-500">Dashboard Link</a>
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                <div class="mb-2 whitespace-nowrap"><strong>Business:</strong> {{ $project->business_type }}</div>
                                <div class="mb-2 whitespace-nowrap"><strong>Grade:</strong> {{ $project->project_grade }}</div>
                                <div class="mb-2 whitespace-nowrap"><strong>PM:</strong> {{ optional($project->projectManager)->name ?? '-' }}</div>
                                <div class="mb-2 whitespace-nowrap"><strong>TL:</strong> {{ optional($project->teamLead)->name ?? '-' }}</div>
                                <div class="mb-2 whitespace-nowrap"><strong>Sales:</strong> {{ optional($project->salesPerson)->name ?? '-' }}</div>
                                <div class="mb-2 whitespace-nowrap"><strong>Department:</strong> {{ optional($project->department)->name ?? '-' }}</div>
                                <div class="mb-2 whitespace-nowrap"><strong>Assigned Employee:</strong> {{ optional($project->assignMainEmployee)->name ?? '-' }}</div>
                                @if($project->upsell_employee_id && optional($project->upsellEmployee)->name)
                                    <div class="mb-2 whitespace-nowrap"><strong>Upsell Employee:</strong> {{ $project->upsellEmployee->name }}</div>
                                @endif
                                <div class="mb-2 whitespace-nowrap">
                                    <strong>Can Client Rehire?</strong> {{ $project->can_client_rehire ?? 'No' }}
                                </div>
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                <div class="mb-3 whitespace-nowrap">
                                    <b>Price:</b> ${{ number_format($project->display_price ?? 0, 2) }}
                                </div>
                                <div class="mb-3 whitespace-nowrap">
                                    <b>Content Price:</b> ${{ $project->content_price ?? '0' }}
                                </div>
                                <div class="mb-3">
                                    <a target="_blank" href="{{ route('project_payments.index', ['project_id' => $project->id]) }}">
                                        <span class="inline-block bg-blue-500 text-white font-semibold px-3 py-1 rounded-md hover:bg-blue-600 transition whitespace-nowrap">
                                            Received Price: ${{ number_format($project->received_amount ?? 0, 2) }}
                                        </span>
                                    </a>
                                </div>
                                <div class="mb-3 whitespace-nowrap">
                                    <b>Hours:</b> {{ $project->display_hours ?? 'N/A' }}
                                </div>
                                <div>
                                    @if($project->duration_days)
                                        <span class="inline-block bg-blue-500 text-white font-semibold px-3 py-1 rounded-md whitespace-nowrap">
                                            Project Duration: {{ $project->duration_days }} day(s)
                                        </span>
                                    @else
                                        <span class="text-gray-400 whitespace-nowrap">Project Duration: N/A</span>
                                    @endif
                                </div>
                            </td>
                            <td class="border border-gray-300 px-4 py-2 whitespace-nowrap text-gray-600">
                                {{ $project->created_at->format('d-M-Y') }}
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('projects.status', $project->id) }}"
                                       class="whitespace-nowrap text-white px-4 py-2 rounded-md shadow transition duration-300 inline-flex items-center gap-2 {{ $project->project_status == 'Working' ? 'bg-green-600 hover:bg-green-700' : 'bg-blue-600 hover:bg-blue-700' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582M20 20v-5h-.581M5.582 9A7 7 0 0119 15.418M18.418 15A7 7 0 015.582 9"/>
                                        </svg>
                                        Update Status
                                    </a>
                                    <a href="{{ route('project_monthly_reports.index', ['project_id' => $project->id]) }}"
                                       class="whitespace-nowrap bg-green-600 text-white px-4 py-2 rounded-md shadow hover:bg-green-700 transition duration-300 inline-flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m-3-8h.01M5 7h14a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2z"/>
                                        </svg>
                                        Monthly Report
                                    </a>
                                    <a href="{{ route('project_payments.index', ['project_id' => $project->id]) }}"
                                       class="whitespace-nowrap bg-indigo-600 text-white px-4 py-2 rounded-md shadow hover:bg-indigo-700 transition duration-300 inline-flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <rect width="20" height="14" x="2" y="5" rx="2" ry="2"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2 10h20"/>
                                        </svg>
                                        Payment Details
                                    </a>
                                    <a href="{{ route('projects.show', $project->id) }}"
                                       class="whitespace-nowrap bg-blue-600 text-white px-4 py-2 rounded-md shadow hover:bg-blue-700 transition duration-300 inline-flex items-center gap-2" title="View Project">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View
                                    </a>
                                    <a href="{{ route('projects.edit.page', $project->id) }}"
                                       class="whitespace-nowrap bg-blue-600 text-white px-4 py-2 rounded-md shadow hover:bg-blue-700 transition duration-300 inline-flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5h2M15 9l-6 6M7 17h10"/>
                                        </svg>
                                        Edit
                                    </a>
                                    <form action="{{ route('projects.duplicate', $project->id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Are you sure you want to duplicate this project?')"
                                                class="whitespace-nowrap bg-purple-600 text-white px-4 py-2 rounded-md shadow hover:bg-purple-700 transition duration-300 inline-flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16h8M8 12h8m-8-4h8M4 6h.01M4 10h.01M4 14h.01M4 18h.01"/>
                                            </svg>
                                            Duplicate
                                        </button>
                                    </form>
                                </div>
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                <div class="mb-2 whitespace-nowrap"><strong>Project Type:</strong> {{ $project->project_type }}</div>
                                <div class="mb-2 whitespace-nowrap"><strong>Report:</strong> {{ $project->report_type }}</div>
                                <div class="whitespace-nowrap"><strong>Client Type:</strong> {{ $project->client_type }}</div>
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                <strong>Category:</strong> {{ optional($project->projectCategory)->name ?? '-' }}<br>
                                <strong>Sub Category:</strong> {{ optional($project->projectSubCategory)->name ?? '-' }}<br>
                                <strong>Country:</strong> {{ optional($project->country)->name ?? '-' }}
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                <span class="{{ $statusClass }}">
                                    {{ ucfirst($statusRaw) }}
                                </span>
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                <strong>Name:</strong> {{ $project->client_name }}<br>
                                <strong>Email:</strong> {{ $project->client_email }}<br>
                                <strong>Other Info:</strong>
                                <span class="tooltip">
                                    {{ Str::limit($project->client_other_info, 20) }}
                                    <span class="tooltiptext">{{ $project->client_other_info }}</span>
                                </span>
                            </td>
                            <td class="border border-gray-300 px-4 py-2 text-sm">
                    @if (!empty($project->reason_description))
                        <span class="tooltip">
                            {{ Str::limit($project->reason_description, 20) }}
                            <span class="tooltiptext">{{ $project->reason_description }}</span>
                        </span>
                    @else
                        <span class="text-gray-500">-</span>
                    @endif
                    <br>
                                <span class="text-gray-600">
                                    <strong>Updated At:</strong>
                                    @if (!empty($project->status_date))
                                        {{ \Carbon\Carbon::parse($project->status_date)->format('d-M-Y') }}
                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                </span>
                </td>
                            <td class="py-4 px-4 text-sm space-y-1 max-w-xs break-words border border-gray-300">
                                @php
                                    $pmAttachments = $project->attachments ?? [];
                                    $salesAttachments = $project->saleTeamAttachments ?? [];
                                @endphp
                                @foreach ($pmAttachments as $attachment)
                                    <a href="{{ asset('storage/' . $attachment->file_path) }}"
                                       target="_blank"
                                       class="text-blue-600 underline block text-xs truncate"
                                       title="{{ basename($attachment->file_path) }}">
                                       📎 {{ basename($attachment->file_path) }}
                                       <span class="text-gray-400 text-xxs">(PM)</span>
                                    </a>
                                @endforeach
                                @foreach ($salesAttachments as $attachment)
                                    <a href="{{ asset('storage/' . $attachment->file_path) }}"
                                       target="_blank"
                                       class="text-green-600 underline block text-xs truncate"
                                       title="{{ basename($attachment->file_path) }}">
                                       📎 {{ basename($attachment->file_path) }}
                                       <span class="text-gray-400 text-xxs">(Sales)</span>
                                    </a>
                                @endforeach
                                @if (count($pmAttachments) + count($salesAttachments) === 0)
                                    <span class="text-gray-500 text-xs">No attachments</span>
                                @endif
                                <a href="{{ route('projects.attachments.create', $project->id) }}"
                                   class="inline-block mt-2 px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-xs whitespace-nowrap">
                                    Add Attachment
                                </a>
                            </td>
                            <td class="border border-gray-300 px-4 py-2 whitespace-pre-line align-top text-sm" style="max-height: 300px; overflow-y: auto; min-width: 300px; line-height: 1.4;">
                                {!! nl2br(e($project->display_description)) !!}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center text-gray-500 py-4">No projects found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $paginatedProjects->withQueryString()->links() }}
    </div>
</div>
@endsection