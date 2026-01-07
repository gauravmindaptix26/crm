<?php

namespace App\Http\Controllers;
use App\Models\Department;
use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Facades\DB;




use Illuminate\Http\Request;

class PmProjectsReportController extends Controller
{
    public function index(Request $request)
    {
        $departments = Department::all();
    
        $selectedDept = $request->department_id;
        $selectedMonth = $request->month;
        $selectedYear = $request->year;
    
        $pms = User::role('Project Manager')->get();
    
        $report = [];
        $totals = [
            'Complete' => 0,
            'Paused' => 0,
            'Issues' => 0,
            'Hold' => 0,
            'Rehire' => 0,
            'Working' => 0,
        ];
    
        $statusAliasMap = [
            'Complete'   => 'Complete',
            'Paused'     => 'Paused',
            'Issues'     => 'Issues',
            'Hold'       => 'Hold',
            'Temp Hold'  => 'Temp Hold', // if you're tracking it separately
            'Rehire'     => 'Rehire',
        ];
    
        foreach ($pms as $pm) {
            $statusCounts = collect([
                'Complete' => 0,
                'Paused' => 0,
                'Issues' => 0,
                'Hold' => 0,
                'Rehire' => 0,
                'Working' => 0,
            ]);
    
            $baseQuery = Project::query()
                ->where('project_manager_id', $pm->id);
    
            if ($selectedDept) {
                $baseQuery->where('department_id', $selectedDept);
            }
    
            // ✅ Show all working projects (no month filter)
            $workingCount = (clone $baseQuery)
                ->where('project_status', 'Working')
                ->count();
    
            $statusCounts['Working'] = $workingCount;
            $totals['Working'] += $workingCount;
    
            // ✅ Filter other statuses by status_date (month + year)
            $filteredQuery = (clone $baseQuery)
                ->whereIn('project_status', array_keys($statusAliasMap))
                ->whereMonth('status_date', $selectedMonth ?: now()->month)
                ->whereYear('status_date', $selectedYear ?: now()->year);
    
            $filteredCounts = $filteredQuery
                ->select('project_status', DB::raw('count(*) as total'))
                ->groupBy('project_status')
                ->pluck('total', 'project_status');
    
            foreach ($filteredCounts as $status => $count) {
                $mappedStatus = $statusAliasMap[$status] ?? null;
                if ($mappedStatus && isset($statusCounts[$mappedStatus])) {
                    $statusCounts[$mappedStatus] += $count;
                    $totals[$mappedStatus] += $count;
                }
            }
    
            if ($statusCounts->sum() > 0) {
                $report[] = [
                    'pm' => $pm,
                    'statusCounts' => $statusCounts,
                ];
            }
        }
    
        return view('pm-projects-report.index', compact(
            'report',
            'departments',
            'selectedDept',
            'selectedMonth',
            'selectedYear',
            'totals'
        ));
    }
    
    public function projectList(Request $request)
    {
        $status = $request->input('status');
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
    
        // Ensure pm_id is treated as project_manager
        if ($request->filled('pm_id') && !$request->filled('project_manager')) {
            $request->merge(['project_manager' => $request->pm_id]);
        }
    
        // Base query for project list
        $query = Project::with(['country', 'department', 'projectManager'])
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year);
    
        // Apply all filters
        if ($request->filled('project_manager')) {
            $query->where('project_manager_id', $request->project_manager);
        }
    
        if ($request->filled('team_lead')) {
            $query->where('team_lead_id', $request->team_lead);
        }
    
        if ($request->filled('hired_from')) {
            $query->where('upsell_employee_id', $request->hired_from);
        }
    
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
    
        if ($request->filled('employee')) {
            $query->where(function ($q) use ($request) {
                $q->where('assign_main_employee_id', $request->employee)
                  ->orWhereJsonContains('additional_employees', $request->employee);
            });
        }
    
        if ($request->filled('sales_person')) {
            $query->where('sales_person_id', $request->sales_person);
        }
    
        if ($request->filled('client_type')) {
            $query->where('client_type', $request->client_type);
        }
    
        if ($request->filled('status')) {
            $query->where('project_status', $request->status);
        }
    
        $projects = $query->latest()->get();

        // 🔁 Add received price & duration to each project
foreach ($projects as $project) {
    // Total amount received for this project
    $project->received_price = DB::table('project_payments')
        ->where('project_id', $project->id)
        ->sum('payment_amount');

    // Duration in days from creation till today or last update
    $endDate = $project->updated_at ?? now();
    $project->duration_days = $project->created_at->diffInDays($endDate);
}
    
        // Cards Calculation — use same filters
        $monthName = date("F", mktime(0, 0, 0, $month, 10));
    
        $cardQuery = Project::whereMonth('created_at', $month)
                            ->whereYear('created_at', $year);
    
        if ($request->filled('project_manager')) {
            $cardQuery->where('project_manager_id', $request->project_manager);
        }
    
        if ($request->filled('team_lead')) {
            $cardQuery->where('team_lead_id', $request->team_lead);
        }
    
        if ($request->filled('hired_from')) {
            $cardQuery->where('upsell_employee_id', $request->hired_from);
        }
    
        if ($request->filled('department_id')) {
            $cardQuery->where('department_id', $request->department_id);
        }
    
        if ($request->filled('employee')) {
            $cardQuery->where(function ($q) use ($request) {
                $q->where('assign_main_employee_id', $request->employee)
                  ->orWhereJsonContains('additional_employees', $request->employee);
            });
        }
    
        if ($request->filled('sales_person')) {
            $cardQuery->where('sales_person_id', $request->sales_person);
        }
    
        if ($request->filled('client_type')) {
            $cardQuery->where('client_type', $request->client_type);
        }
    
        if ($request->filled('status')) {
            $cardQuery->where('project_status', $request->status);
        }
    
        // Get filtered project IDs
        $filteredProjectIds = (clone $cardQuery)->pluck('id');
    
        $newProjectsCount = $filteredProjectIds->count();
        $activeProjectsCount = (clone $cardQuery)->where('project_status', 'Working')->count();
        $predictionAmount = (clone $cardQuery)->sum('price');
    
        $amountReceived = DB::table('project_payments')
            ->whereMonth('payment_month', $month)
            ->whereYear('payment_month', $year)
            ->whereIn('project_id', $filteredProjectIds)
            ->sum('payment_amount');
    
        // Dropdowns
        $projectManagers = User::role('Project Manager')->get();
        $salesPersons = User::role('Sales Team')->get();
        $employees = User::all();
        $departments = Department::all();
        $teamLeads = User::role('Team Lead')->get();
    
        return view('pm-projects-report.project-list', compact(
            'projects',
            'monthName',
            'newProjectsCount',
            'activeProjectsCount',
            'predictionAmount',
            'amountReceived',
            'projectManagers',
            'salesPersons',
            'employees',
            'departments',
            'month',
            'year',
            'status',
            'teamLeads'
        ));
    }
    

    public function fetchSalesPersons(Request $request)
{
    $pmId = $request->pm_id;
    $status = $request->status;

    $projects = Project::where('project_manager_id', $pmId)
                ->where('project_status', $status)
                ->with('salesPerson') // assuming relation: salesPerson()
                ->get();

    $salesPersons = $projects->pluck('salesPerson')->unique('id')->filter()->values();

    return response()->json($salesPersons->map(function ($person) {
        return ['id' => $person->id, 'name' => $person->name];
    }));
}

}
