@extends('layouts.dashboard')

@section('content')
<style>
    .project-layout-wrapper {
        display: flex;
        gap: 20px;
        height: 90vh;
    }
    .project-column {
        flex: 1;
        overflow-y: auto;
    }
    .info-box {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }
    .info-box .box {
        padding: 20px;
    }
    .info-box-number {
        font-size: 36px;
        font-weight: 600;
    }
    /* Card Color Styles */
    .card-info {
        background-color: #17a2b8; /* Info card (blue) */
        color: #ffffff;
    }
    .card-primary {
        background-color: #007bff; /* Primary card (blue) */
        color: #ffffff;
    }
    .card-success {
        background-color: #28a745; /* Success card (green) */
        color: #ffffff;
    }
    .card-warning {
        background-color: #ffc107; /* Warning card (yellow) */
        color: #ffffff;
    }
    .projectbox {
    display: flex;
}
</style>
<div class="mb-6">
        <div class="flex gap-2 justify-start flex-wrap mb-4">
            <a href="{{ route('project_payments.index', ['project_id' => $project->id]) }}"
               class="px-3 py-1 whitespace-nowrap text-white rounded-lg shadow-md bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600 hover:from-teal-500 hover:via-teal-600 hover:to-teal-700 focus:outline-none text-sm font-medium">
                Payment Details
            </a>
            <a href="{{ route('project_monthly_reports.index', ['project_id' => $project->id]) }}"
               class="px-3 py-1 whitespace-nowrap text-white rounded-lg shadow-md bg-green-600 hover:bg-green-700 focus:outline-none text-sm font-medium">
                Project Monthly Report
            </a>
        </div>

<!-- <div class="row projectbox" style="margin-top:30px;">
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box" style="height: 200px;">
            <div class="info-box-content">
                <div class="card-inverse card-info" style="height: 100%; padding: 30px;">
                    <div class="box text-center">
                        <h1 class="font-light text-white info-box-number" style="font-size: 40px;">${{ number_format($project->price, 2) }}</h1>
                        <h6 class="text-white" style="font-size: 18px;">Project Price</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box" style="height: 200px;">
            <div class="info-box-content">
                <div class="card-inverse card-primary" style="height: 100%; padding: 30px;">
                    <div class="box text-center">
                        <h1 class="font-light text-white info-box-number" style="font-size: 40px;">${{ number_format($receivedMoney, 2) }}</h1>
                        <h6 class="text-white" style="font-size: 18px;">Received Money</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box" style="height: 200px;">
            <div class="info-box-content">
                <div class="card-inverse card-success" style="height: 100%; padding: 30px;">
                    <div class="box text-center">
                        <h1 class="font-light text-white info-box-number" style="font-size: 40px;">{{ $project->estimated_hours ?? 0 }} hrs</h1>
                        <h6 class="text-white" style="font-size: 18px;">Estimated Hours</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box" style="height: 200px;">
            <div class="info-box-content">
                <div class="card-inverse card-warning" style="height: 100%; padding: 30px;">
                    <div class="box text-center">
                        <h1 class="font-light text-white info-box-number" style="font-size: 40px;">{{ $spentHours ?? 0 }} hrs</h1>
                        <h6 class="text-white" style="font-size: 18px;">Spent Hours</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> -->






<div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6" bis_skin_checked="1">
    <div class="p-4 text-white rounded-lg shadow-md
         bg-gradient-to-r from-emerald-400 via-emerald-500 to-emerald-600
         hover:from-emerald-500 hover:via-emerald-600 hover:to-emerald-700
         focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2
         transition-all duration-200 ease-in-out" bis_skin_checked="1">
        <div class="text-sm" bis_skin_checked="1">Project price</div>
        <div class="text-2xl font-bold" bis_skin_checked="1">${{ number_format($project->price, 2) }}</div>
    </div>

    <div class="p-4 text-white rounded-lg shadow-md
         bg-gradient-to-r from-indigo-400 via-indigo-500 to-indigo-600
         hover:from-indigo-500 hover:via-indigo-600 hover:to-indigo-700
         focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2
         transition-all duration-200 ease-in-out" bis_skin_checked="1">
        <div class="text-sm" bis_skin_checked="1">Received Price</div>
        <div class="text-2xl font-bold" bis_skin_checked="1">${{ number_format($receivedMoney, 2) }}</div>
    </div>

    <div class="p-4 text-white rounded-lg shadow-md
         bg-gradient-to-r from-blue-400 via-blue-500 to-blue-600
         hover:from-blue-500 hover:via-blue-600 hover:to-blue-700
         focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2
         transition-all duration-200 ease-in-out" bis_skin_checked="1">
        <div class="text-sm" bis_skin_checked="1">Estimated Hours</div>
        <div class="text-2xl font-bold" bis_skin_checked="1">{{ $project->estimated_hours ?? 0 }} hrs</div>
    </div>

    <div class="w-5/6 py-2 px-4 rounded-lg shadow text-white font-medium rounded-lg shadow-md 
            bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600
            hover:from-teal-500 hover:via-teal-600 hover:to-teal-700
            focus:outline-none" bis_skin_checked="1">
        <div class="text-sm font-semibold" bis_skin_checked="1">Spent Hours</div>
        <div class="text-2xl font-bold" bis_skin_checked="1">{{ $spentHours ?? 0 }} hrs</div>
    </div>


</div>
















<div class="container-fluid px-4">
    <div class="project-layout-wrapper">
        <!-- Project Details Section -->
        <div class="project-column">
            <div class="card shadow rounded">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-3 p-4">Project Details</h4>
                </div>
                <div class="card-body">
                    <!-- All project detail fields -->
                    <div class="row mb-3">
                        <div class="col-md-6 d-flex mb-2">
                            <strong class="me-2">Name or URL:</strong>
                            <span>{{ $project->name_or_url }}</span>
                        </div>
                        <div class="col-md-6 d-flex mb-2">
                            <strong class="me-2">Dashboard URL:</strong>
                            <a href="{{ $project->dashboard_url }}" target="_blank">{{ $project->dashboard_url }}</a>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12 d-flex mb-2">
                            <strong class="me-2">Description:</strong>
                            <span>{!! nl2br(e($project->description)) !!}</span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 d-flex mb-2"><strong class="me-2">Project Grade:</strong> <span>{{ $project->project_grade }}</span></div>
                        <div class="col-md-4 d-flex mb-2"><strong class="me-2">Business Type:</strong> <span>{{ $project->business_type }}</span></div>
                        <div class="col-md-4 d-flex mb-2"><strong class="me-2">Project Type:</strong> <span>{{ $project->project_type }}</span></div>
                    </div>

                    @php
                        $statusColors = [
                            'complete'   => 'text-success',
                            'working'    => 'text-success',
                            'hold'       => 'text-warning',
                            'paused'     => 'text-primary',
                            'issues'     => 'text-danger',
                            'temp hold'  => 'text-orange-600',
                            'closed'     => 'text-muted',
                        ];
                        $statusRaw = $project->project_status ?? 'working';
                        $status = strtolower(trim($statusRaw));
                        $statusClass = $statusColors[$status] ?? 'text-secondary';
                    @endphp

                    <div class="row mb-3">
                        <div class="col-md-4 d-flex mb-2"><strong class="me-2">Report Type:</strong> <span>{{ $project->report_type }}</span></div>
                        <div class="col-md-4 d-flex mb-2"><strong class="me-2">Client Type:</strong> <span>{{ $project->client_type }}</span></div>
                        <div class="col-md-4 d-flex mb-2"><strong class="me-2">Status:</strong> <span class="{{ $statusClass }}">{{ ucfirst($statusRaw) }}</span></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 d-flex mb-2"><strong class="me-2">Category:</strong> <span>{{ $project->projectCategory->cat_name ?? '-' }}</span></div>
                        <div class="col-md-4 d-flex mb-2"><strong class="me-2">Sub Category:</strong> <span>{{ $project->projectSubCategory->cat_name ?? '-' }}</span></div>
                        <div class="col-md-4 d-flex mb-2"><strong class="me-2">Country:</strong> <span>{{ $project->country->name ?? '-' }}</span></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 d-flex mb-2"><strong class="me-2">Project Manager:</strong> <span>{{ $project->projectManager->name ?? '-' }}</span></div>
                        <div class="col-md-4 d-flex mb-2"><strong class="me-2">Sales Person:</strong> <span>{{ $project->salesPerson->name ?? '-' }}</span></div>
                        <div class="col-md-4 d-flex mb-2"><strong class="me-2">Department:</strong> <span>{{ $project->department->name ?? '-' }}</span></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 d-flex mb-2"><strong class="me-2">Assigned Employee:</strong> <span>{{ $project->assignMainEmployee->name ?? '-' }}</span></div>
                        <div class="col-md-4 d-flex mb-2"><strong class="me-2">Price (USD):</strong> <span>${{ $project->price ?? '0' }}</span></div>
                        <div class="col-md-4 d-flex mb-2"><strong class="me-2">Estimated Hours:</strong> <span>{{ $project->estimated_hours ?? 'N/A' }}</span></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 d-flex mb-2"><strong class="me-2">Client Name:</strong> <span>{{ $project->client_name }}</span></div>
                        <div class="col-md-4 d-flex mb-2"><strong class="me-2">Client Email:</strong> <span>{{ $project->client_email }}</span></div>
                        <div class="col-md-4 d-flex mb-2"><strong class="me-2">Client Other Info:</strong> <span>{{ $project->client_other_info }}</span></div>
                    </div>
                    <div class="col-md-4 d-flex mb-2"><strong class="me-2">Project Status:</strong> <span>{{ $project->project_status }}</span></div>


                    <div class="mt-3 text-center">
                        <a href="{{ route('projects.index') }}" class="btn btn-secondary">Back to Projects</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Work Reports Section -->
        <div class="project-column">
            <div class="card shadow rounded">
                <div class="card-header bg-dark text-white text-center">
                    <h4 class="mb-0">Work Reports</h4>
                </div>
                <div class="card-body">
                    @if($dsrs->isEmpty())
                        <div class="text-center text-muted">No work reports found for this project.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-secondary">
                                    <tr>
                                        <th>Employee</th>
                                        <th>Work Description</th>
                                        <th>Date</th>
                                        <th>Hours</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dsrs as $dsr)
                                        <tr>
                                            <td>{{ $dsr->user->name ?? '-' }}</td>
                                            <td>{!! nl2br(e($dsr->work_description)) !!}</td>
                                            <td>{{ $dsr->created_at ? $dsr->created_at->format('d M Y') : '-' }}</td>
                                            <td>{{ $dsr->hours ?? 0 }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
