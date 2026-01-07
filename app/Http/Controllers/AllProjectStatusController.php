<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;
use App\Models\Department;
use App\Models\ProjectPayment;

use Carbon\Carbon;


class AllProjectStatusController extends Controller
{
   
    
    public function index($status, Request $request)
    {
        // Prioritize report_month/report_year from dashboard, then month/year, then current month/year
        $currentMonth = sprintf('%02d', $request->report_month ?? $request->month ?? now()->month);
        $currentYear = $request->report_year ?? $request->year ?? now()->year;
        $monthName = date('F', mktime(0, 0, 0, $currentMonth, 10));

        // Base query for listing
        $baseQuery = Project::query()->with([
            'projectManager',
            'assignMainEmployee',
            'salesPerson',
            'department',
            'teamLead',
            'projectPayments',
            'projectCategory',
            'projectSubCategory',
            'country',
            'saleTeamAttachments',
            'upsellEmployee'
        ])->orderBy('created_at', 'desc'); // Sort by newest first

        // Status-specific filter
        if ($status === 'new') {
            $baseQuery->whereMonth('created_at', $currentMonth)
                      ->whereYear('created_at', $currentYear);
        } elseif ($status === 'ALL') {
            $baseQuery->whereDate('created_at', '<=', now()->setDate($currentYear, $currentMonth, 31)->endOfMonth())
                      ->when($request->filled('department_id'), fn($q) => $q->where('department_id', $request->department_id));
        } elseif ($status === 'Working') {
            $baseQuery->whereRaw('LOWER(project_status) = ?', ['working'])
                      ->whereDate('created_at', '<=', now()->setDate($currentYear, $currentMonth, 31)->endOfMonth());
        } elseif (str_contains($status, ',')) {
            $statuses = explode(',', $status);
            $baseQuery->whereIn('project_status', $statuses)
                      ->whereNotNull('status_date')
                      ->whereYear('status_date', $currentYear)
                      ->whereMonth('status_date', $currentMonth);
        } elseif ($status === 'Closed') {
            $baseQuery->whereIn('project_status', ['Complete'])
                      ->whereNotNull('status_date')
                      ->whereYear('status_date', $currentYear)
                      ->whereMonth('status_date', $currentMonth);
        } else {
            $baseQuery->whereRaw('LOWER(project_status) = ?', [strtolower($status)])
                      ->whereNotNull('status_date')
                      ->whereYear('status_date', $currentYear)
                      ->whereMonth('status_date', $currentMonth);
        }

        // Apply filters from request to main query
        if ($request->filled('department_id')) {
            $baseQuery->where('department_id', $request->department_id);
        }
        if ($request->filled('project_manager')) {
            $baseQuery->where('project_manager_id', $request->project_manager);
        }
        if ($request->filled('sales_person')) {
            $baseQuery->where('sales_person_id', $request->sales_person);
        }
        if ($request->filled('employee')) {
            $baseQuery->where('assign_main_employee_id', $request->employee);
        }

        // Pending payment filter
        if ($request->filled('pending_payment') && $request->pending_payment == '1') {
            $baseQuery->leftJoin('project_payments', 'projects.id', '=', 'project_payments.project_id')
                      ->select('projects.*', \DB::raw('COALESCE(SUM(project_payments.payment_amount), 0) as total_paid'))
                      ->groupBy('projects.id')
                      ->havingRaw('projects.price > total_paid');
        } else {
            $baseQuery->select('projects.*');
        }

        // Fetch filtered projects
        $projects = $baseQuery->get();

        // Transform
        $projects->transform(function ($project) {
            $project->is_sale_team = \App\Models\AssignedProject::where('project_id', $project->id)->exists();
            $project->received_amount = $project->projectPayments->sum('payment_amount');
            $project->duration_days = $project->estimated_hours ? ceil($project->estimated_hours / 8) : null;
            return $project;
        });

        // Manual pagination
        $page = $request->get('page', 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $paginatedProjects = new \Illuminate\Pagination\LengthAwarePaginator(
            $projects->slice($offset, $perPage)->values(),
            $projects->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Stats
        $totalProjects = $projects->count();
        $activeProjects = $projects->where('project_status', 'working')->count();
        $predictionAmount = $projects->sum('price');
        $filteredProjectIds = $projects->pluck('id');

        $amountReceived = \DB::table('project_payments')
            ->whereIn('project_id', $filteredProjectIds)
            ->whereMonth('payment_month', $currentMonth)
            ->whereYear('payment_month', $currentYear)
            ->sum('payment_amount');

        // Stats queries
        $newProjectsQuery = Project::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear);
        $activeProjectsQuery = Project::whereRaw('LOWER(project_status) = ?', ['working'])
            ->whereDate('created_at', '<=', now()->setDate($currentYear, $currentMonth, 31)->endOfMonth());

        if ($request->filled('department_id')) {
            $newProjectsQuery->where('department_id', $request->department_id);
            $activeProjectsQuery->where('department_id', $request->department_id);
        }
        if ($request->filled('project_manager')) {
            $newProjectsQuery->where('project_manager_id', $request->project_manager);
            $activeProjectsQuery->where('project_manager_id', $request->project_manager);
        }
        if ($request->filled('sales_person')) {
            $newProjectsQuery->where('sales_person_id', $request->sales_person);
            $activeProjectsQuery->where('sales_person_id', $request->sales_person);
        }
        if ($request->filled('employee')) {
            $newProjectsQuery->where('assign_main_employee_id', $request->employee);
            $activeProjectsQuery->where('assign_main_employee_id', $request->employee);
        }

        $newProjectsCount = $newProjectsQuery->count();
        $activeProjectsCount = $activeProjectsQuery->count();

        // Dropdowns
        $projectManagers = User::whereHas('roles', fn($q) => $q->where('name', 'Project Manager'))->get();
        $salesPersons = User::whereHas('roles', fn($q) => $q->where('name', 'Sales Team'))->get();
        $employees = User::all();
        $departments = Department::all();
        $teamLeads = User::role('Team Lead')->get();

        // Debug logging
        \Log::info('AllProjectStatusController Debug', [
            'status' => $status,
            'department_id' => $request->department_id,
            'month' => $currentMonth,
            'year' => $currentYear,
            'total_projects' => $totalProjects,
            'project_ids' => $projects->pluck('id')->toArray()
        ]);

        return view('all_projects.index', compact(
            'paginatedProjects',
            'projectManagers',
            'salesPersons',
            'employees',
            'departments',
            'monthName',
            'totalProjects',
            'activeProjects',
            'predictionAmount',
            'amountReceived',
            'teamLeads',
            'status',
            'newProjectsCount',
            'activeProjectsCount'
        ));
    }
public function byPayment($department_id, Request $request)
{
    // Prioritize report_month/report_year, then month/year, then current month/year
    $currentMonth = sprintf('%02d', $request->report_month ?? $request->month ?? now()->month);
    $currentYear = $request->report_year ?? $request->year ?? now()->year;
    $monthName = date('F', mktime(0, 0, 0, $currentMonth, 10));

    // Base query for projects with payments
    $baseQuery = Project::query()->with([
        'projectManager',
        'assignMainEmployee',
        'salesPerson',
        'department',
        'teamLead',
        'projectPayments',
        'projectCategory',
        'projectSubCategory',
        'country',
        'saleTeamAttachments',
        'upsellEmployee'
    ])->join('project_payments', 'projects.id', '=', 'project_payments.project_id')
      ->where('projects.department_id', $department_id)
      ->whereMonth('project_payments.payment_month', $currentMonth)
      ->whereYear('project_payments.payment_month', $currentYear)
      ->select('projects.*')
      ->distinct();

    // Apply additional filters
    if ($request->filled('project_manager')) {
        $baseQuery->where('project_manager_id', $request->project_manager);
    }
    if ($request->filled('sales_person')) {
        $baseQuery->where('sales_person_id', $request->sales_person);
    }
    if ($request->filled('employee')) {
        $baseQuery->where('assign_main_employee_id', $request->employee);
    }

    // Pending payment filter
    if ($request->filled('pending_payment') && $request->pending_payment == '1') {
        $baseQuery->leftJoin('project_payments as pp', 'projects.id', '=', 'pp.project_id')
                  ->select('projects.*', \DB::raw('COALESCE(SUM(pp.payment_amount), 0) as total_paid'))
                  ->groupBy('projects.id')
                  ->havingRaw('projects.price > total_paid');
    }

    // Fetch filtered projects
    $projects = $baseQuery->get();

    // Transform
    $projects->transform(function ($project) use ($currentMonth, $currentYear) {
        $project->is_sale_team = \App\Models\AssignedProject::where('project_id', $project->id)->exists();
        $project->received_amount = \App\Models\ProjectPayment::where('project_id', $project->id)
            ->whereMonth('payment_month', $currentMonth)
            ->whereYear('payment_month', $currentYear)
            ->sum('payment_amount');
        $project->duration_days = $project->estimated_hours ? ceil($project->estimated_hours / 8) : null;
        return $project;
    });

    // Manual pagination
    $page = $request->get('page', 1);
    $perPage = 10;
    $offset = ($page - 1) * $perPage;

    $paginatedProjects = new \Illuminate\Pagination\LengthAwarePaginator(
        $projects->slice($offset, $perPage)->values(),
        $projects->count(),
        $perPage,
        $page,
        ['path' => $request->url(), 'query' => $request->query()]
    );

    // Stats
    $totalProjects = $projects->count();
    $activeProjects = $projects->where('project_status', 'working')->count();
    $predictionAmount = $projects->sum('price');
    $filteredProjectIds = $projects->pluck('id');

    $amountReceived = \DB::table('project_payments')
        ->whereIn('project_id', $filteredProjectIds)
        ->whereMonth('payment_month', $currentMonth)
        ->whereYear('payment_month', $currentYear)
        ->sum('payment_amount');

    // Calculate counts for new and active projects
    $newProjectsCount = Project::whereMonth('created_at', $currentMonth)
        ->whereYear('created_at', $currentYear)
        ->where('department_id', $department_id)
        ->count();

    $activeProjectsCount = Project::whereRaw('LOWER(project_status) = ?', ['working'])
        ->whereDate('created_at', '<=', now()->setDate($currentYear, $currentMonth, 31)->endOfMonth())
        ->where('department_id', $department_id)
        ->count();

    // Dropdowns
    $projectManagers = User::whereHas('roles', fn($q) => $q->where('name', 'Project Manager'))->get();
    $salesPersons = User::whereHas('roles', fn($q) => $q->where('name', 'Sales Team'))->get();
    $employees = User::all();
    $departments = Department::all();
    $teamLeads = User::role('Team Lead')->get();

    // Define status
    $status = 'payment';

    return view('all_projects.index', compact(
        'paginatedProjects',
        'projectManagers',
        'salesPersons',
        'employees',
        'departments',
        'monthName',
        'totalProjects',
        'activeProjects',
        'predictionAmount',
        'amountReceived',
        'teamLeads',
        'status',
        'newProjectsCount',
        'activeProjectsCount'
    ));
}
public function closedBreakdown($department_id, Request $request)
{
    $currentMonth = sprintf('%02d', $request->report_month ?? $request->month ?? now()->month);
    $currentYear = $request->report_year ?? $request->year ?? now()->year;
    $monthName = date('F', mktime(0, 0, 0, $currentMonth, 10));

    // Fetch closed projects grouped by project_manager_id with team names
    $closedBreakdown = Project::where('department_id', $department_id)
        ->whereIn('project_status', ['Closed', 'Complete'])
        ->whereMonth('updated_at', $currentMonth)
        ->whereYear('updated_at', $currentYear)
        ->join('users', 'projects.project_manager_id', '=', 'users.id')
        ->leftJoin('teams', 'users.team_id', '=', 'teams.id')
        ->select('project_manager_id', 'users.name as manager_name', 'teams.name as team_name', \DB::raw('COUNT(*) as count'))
        ->groupBy('project_manager_id', 'users.name', 'teams.name')
        ->orderBy('count', 'desc')
        ->get();

    // Total closed count
    $totalClosed = $closedBreakdown->sum('count');

    // Department name
    $department = Department::find($department_id);

    return response()->json([
        'closedBreakdown' => $closedBreakdown,
        'totalClosed' => $totalClosed,
        'monthName' => $monthName,
        'currentMonth' => $currentMonth,
        'currentYear' => $currentYear,
        'department_id' => $department_id,
        'department_name' => $department->name,
    ]);
}
}
    

