<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Dsr;
use App\Models\Task;
use App\Models\Department;
use App\Models\Project;
use App\Models\HiredFrom;
use App\Models\SalesLead;
use App\Models\GuestPost;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Notifications\FollowupReminder;




class DashboardController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $role = $user->roles->first()->name; // Assuming one role per user

        $totalUsers = User::count();
        $totalDepartment = Department::count();
        $guestPost = GuestPost::count();

        if ($role === 'Project Manager') {
            $teamMembers = User::where('reporting_person', $user->id)->get();

            // Get latest tasks assigned to this PM
            $myTasks = Task::whereHas('assignedUsers', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
                ->latest()
                ->take(2)
                ->get();

            // Generate overdue follow-up notifications
            $user->notifications()->where('type', 'App\Notifications\FollowupReminder')->delete(); // Clear old notifications
            $overdueProjects = Project::where('project_status', 'Paused')
                ->where('project_manager_id', $user->id)
                ->where(function ($q) {
                    $q->whereNull('last_followup_at')
                      ->orWhere('last_followup_at', '<=', Carbon::now()->subDays(15));
                })
                ->get();

            foreach ($overdueProjects as $project) {
                $days = $project->last_followup_at
                    ? Carbon::now()->diffInDays($project->last_followup_at)
                    : Carbon::now()->diffInDays($project->created_at); // Fallback to created_at if no follow-up
                $user->notify(new FollowupReminder($project, $days));
            }

            // Get unread notifications for display
            $notifications = $user->unreadNotifications()->where('type', 'App\Notifications\FollowupReminder')->get();

            return view('dashboard.project_manager', compact('teamMembers', 'guestPost', 'role', 'myTasks', 'notifications'));
        }

        
    
    else if ($role === 'Team Lead') {
        return view('dashboard.team_lead', compact('role'));
    }
    
    
    else if ($role === 'Employee') {
        $projects = $user->projects; // Make sure projects() relation is defined

        $stats = [
            'all' => $projects->count(),
            'working' => $projects->where('project_status', 'Working')->count(),
            'complete' => $projects->where('project_status', 'Complete')->count(),
            'pause' => $projects->where('project_status', 'Paused')->count(),
            'issue' => $projects->where('project_status', 'Issues')->count(),
            'temp_hold' => $projects->where('project_status', 'Temp Hold')->count(),
        ];
        $avgUserRating = round($user->userNotes->whereNotNull('rating')->avg('rating') ?? 0, 1);
        $avgHrRating = round($user->hrNotes->whereNotNull('rating')->avg('rating') ?? 0, 1);
    
        $fineCount = $user->hrNotes->where('note_type', 'No of Fine')->count();
        $appreciationCount = $user->hrNotes->where('note_type', 'Appreciation')->count();
         // 🔹 New: Performance Review Rating from Project Manager
    $avgPmRating = \App\Models\EmployeeReview::where('employee_id', $user->id)
    ->selectRaw('ROUND(AVG((communication + team_collaboration + quality_of_work + ownership) / 4), 1) as avg_rating')
    ->value('avg_rating') ?? 0;

        return view('dashboard.employee_dashboard', compact('stats', 'role','avgUserRating','avgHrRating','fineCount','appreciationCount','avgPmRating'));
    } 

    else if (in_array($role, ['Sales Team', 'Sales Team Manager'])) 
        {
        // Fetch filters from request
        $startDate = request()->input('start_date');
        $endDate = request()->input('end_date');
        $leadFromId = request()->input('lead_from_id');
    
        // Fetch all profiles from hired_froms table
        $profiles = HiredFrom::all();
        //dd($profiles); 
    
        // Base query for SalesLead
        $baseQuery = SalesLead::where('sales_person_id', $user->id);
    
        if ($startDate && $endDate) {
            $baseQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
    
        if ($leadFromId) {
            $baseQuery->where('lead_from_id', $leadFromId);
        }
    
        // Hired Projects count grouped by lead_from_id
        $hiredLeads = (clone $baseQuery)->where('status', 'Hired')
            ->selectRaw('lead_from_id, COUNT(*) as hired_count')
            ->groupBy('lead_from_id')
            ->pluck('hired_count', 'lead_from_id');
    
        // Bids count grouped by lead_from_id
        $bidLeads = (clone $baseQuery)->where('status', 'Bid')
            ->selectRaw('lead_from_id, COUNT(*) as bids_count')
            ->groupBy('lead_from_id')
            ->pluck('bids_count', 'lead_from_id');
    
        $goodBidLeads = (clone $baseQuery)->where('status', 'Bid')
            ->where('client_type', 'Premium')
            ->selectRaw('lead_from_id, COUNT(*) as good_bids_count')
            ->groupBy('lead_from_id')
            ->pluck('good_bids_count', 'lead_from_id'); // [lead_from_id => count]
        
    
                
        

                    // Project status counts
        $allProjectsCount = Project::where('sales_person_id', $user->id)->count();
        $workingCount = Project::where('sales_person_id', $user->id)->where('project_status', 'Working')->count();
        $completedCount = Project::where('sales_person_id', $user->id)->where('project_status', 'Complete')->count();
        $pausedCount = Project::where('sales_person_id', $user->id)->where('project_status', 'Paused')->count();
        $issueCount = Project::where('sales_person_id', $user->id)->where('project_status', 'Issues')->count();
        $tempHoldCount = Project::where('sales_person_id', $user->id)->where('project_status', 'Temp Hold')->count();



 $sp = 0; // optional: logged in user ID if filtering by salesperson
    $status = 'In Progress';

    $followUps2 = $this->getFollowUps($sp, Carbon::now()->subDays(4), 1, $status);
    $followUps3 = $this->getFollowUps($sp, Carbon::now()->subDays(3), 2, $status);
    $followUps7 = $this->getFollowUps($sp, Carbon::now()->subDays(7), 3, $status);
    $followUps30 = $this->getFollowUps($sp, Carbon::now()->subDays(30), 4, $status);

    
        return view('dashboard.sales_team', compact(
            'role', 'profiles', 'hiredLeads', 'bidLeads', 'goodBidLeads',
            'startDate', 'endDate', 'leadFromId','allProjectsCount','workingCount','completedCount','pausedCount','issueCount','tempHoldCount','followUps2','followUps3','followUps7','followUps30'
        ));
    }
    

 elseif($role === 'Admin') {
    $currentMonth = request('month') ?? now()->format('m');
    $currentYear = request('year') ?? now()->format('Y');

    $departments = Department::all();
    $reportData = [];
    $totalAmountReceivedAllDepartments = 0;
    $closed_projects = 0;
    $paused_projects = 0;
    $issue_projects = 0;

    foreach ($departments as $department) {
        // All projects that were created on or before the current month and year
        $projectsInCurrentOrPast = Project::where('department_id', $department->id)
            ->whereDate('created_at', '<=', now()->setDate($currentYear, $currentMonth, 31));
    
        $newProjectsCount = Project::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->where('department_id', $department->id)
            ->count();
    
        $activeProjectsCount = (clone $projectsInCurrentOrPast)
            ->where('project_status', 'Working')
            ->count();
    
        // Count closed projects using status_date
        $closedProjectsCount = Project::where('department_id', $department->id)
            ->whereIn('project_status', ['Closed', 'Complete'])
            ->whereNotNull('status_date')
            ->whereYear('status_date', $currentYear)
            ->whereMonth('status_date', $currentMonth)
            ->count();

        $pausedProjectsCount = Project::where('department_id', $department->id)
            ->where('project_status', 'Paused')
            ->whereYear('status_date', $currentYear)
            ->whereMonth('status_date', $currentMonth)
            ->count();
        
        $issueProjectsCount = Project::where('department_id', $department->id)
            ->where('project_status', 'Issues')
            ->whereMonth('status_date', $currentMonth)
            ->whereYear('status_date', $currentYear)
            ->count();
        
        // Total amount for all ongoing (active+paused+issues) projects that started on or before current month
        $totalAmount = (clone $projectsInCurrentOrPast)->sum('price');
    
        $amountReceived = \DB::table('project_payments')
            ->join('projects', 'project_payments.project_id', '=', 'projects.id')
            ->whereMonth('project_payments.payment_month', $currentMonth)
            ->whereYear('project_payments.payment_month', $currentYear)
            ->where('projects.department_id', $department->id)
            ->sum('project_payments.payment_amount');
    
        $totalAmountReceivedAllDepartments += $amountReceived;
    
        $reportData[] = [
            'department' => $department,
            'new_projects' => $newProjectsCount,
            'active_projects' => $activeProjectsCount,
            'closed_projects' => $closedProjectsCount,
            'paused_projects' => $pausedProjectsCount,
            'issue_projects' => $issueProjectsCount,
            'total_amount' => $totalAmount,
            'amount_received' => $amountReceived,
        ];
    }

    return view('dashboard.admin', compact(
        'role', 'totalUsers', 'totalDepartment', 'guestPost',
        'reportData', 'currentMonth', 'currentYear', 'totalAmountReceivedAllDepartments',
        'closed_projects', 'paused_projects', 'issue_projects'
    ));
}
    
    else {
        $totalUsers = User::count();
    $userId = auth()->id();

    // Get tasks assigned to this HR (regardless of role)
    $myTasks = Task::whereHas('assignedUsers', function ($q) use ($userId) {
        $q->where('users.id', $userId);
    })
    ->whereDate('created_at', now()->toDateString()) // Today’s tasks
    ->orderByDesc('created_at')
    ->get();

    return view('dashboard.hr', compact('totalUsers', 'myTasks'));
    }
}
public function getFollowUps($salesPersonId = 0, $cutoffDate, $followUpNumber, $status = 'In Progress')
{
    // Your original follow-up date field (if exists in future, currently no filtering on that)
    // So we'll filter by status and status_update_date <= cutoffDate

    $query = SalesLead::query()
        ->where('status', $status)
        ->whereDate('status_update_date', '<=', $cutoffDate);

    if ($salesPersonId > 0) {
        $query->where('sales_person_id', $salesPersonId);
    }

    $results = $query->get();

    // If no leads found for follow-up, fallback dynamically to last 5 recent leads for that salesperson or all if no salesperson filter
    if ($results->isEmpty()) {
        $fallbackQuery = SalesLead::query();
        
        if ($salesPersonId > 0) {
            $fallbackQuery->where('sales_person_id', $salesPersonId);
        }
        
        // Fallback to last 5 leads by creation date
        $results = $fallbackQuery->orderBy('created_at', 'desc')->limit(5)->get();
    }

    return $results;
}


public function salesTeamProjects(Request $request)
{
    $month = $request->input('month', now()->format('m'));
    $year = $request->input('year', now()->format('Y'));

    $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
    $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

    $salesTeam = User::role('Sales Team')->get();

    $report = $salesTeam->map(function ($user) use ($startDate, $endDate) {
        $monthlyProjects = Project::with('projectManager')
            ->where('sales_person_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // Status with project manager names helper
        $statusWithPMs = function ($status) use ($monthlyProjects) {
            $filtered = $monthlyProjects->where('project_status', $status);
            $pms = $filtered->pluck('projectManager.name')->filter()->unique()->values();
            return [
                'count' => $filtered->count(),
                'pms' => $pms
            ];
        };

        return [
            'id' =>$user->id,
            'name' => $user->name,
            'project_count' => $monthlyProjects->count(),
            'amount' => $monthlyProjects->sum('price'),
            'complete' => $statusWithPMs('Complete'),
            'paused' => $statusWithPMs('Paused'),
            'issues' => $statusWithPMs('Issues'),
            'temp_hold' => $statusWithPMs('Hold'),
            'rehire' => $statusWithPMs('Rehire'),
            'working' => Project::where('sales_person_id', $user->id)
                ->where('project_status', 'Working')
                ->count(),
        ];
    });

    return view('dashboard.sales_team_projects', compact('report', 'month', 'year'));
}

public function salesTeamWorkingProjects(Request $request)
{
    // Use the passed user ID or fallback to the currently logged-in user
    $user_id = $request->input('sales_person_id', auth()->id());


    // Base project query with eager loading
    $query = Project::with([
        'country',
        'department',
        'projectManager',
        'teamLead',
        'salesPerson',
        'assignMainEmployee',
        'projectCategory',
        'projectSubCategory',
        'attachments',
        'projectPayments'
    ])->where('sales_person_id', $user_id);

    // Filter by project status (e.g., Working, Paused, etc.)
    if ($request->filled('status')) {
        $query->where('project_status', $request->status);
    }
    if ($request->filled('sales_person')) {
        $query->where('sales_person_id', $request->sales_person);
    }
    
    // Filter by month and year
    if ($request->filled('month')) {
        $query->whereMonth('created_at', $request->month);
    }

    if ($request->filled('year')) {
        $query->whereYear('created_at', $request->year);
    }

    $projects = $query->latest()->get();
    // Loop through each project to calculate received amount and duration
foreach ($projects as $project) {
    // Received amount from project payments (make sure projectPayments relation exists)
    $project->received_amount = $project->projectPayments->sum('payment_amount');

    // Duration = estimated_hours / 8 (1 day = 8 hours)
    $project->duration_days = $project->estimated_hours ? ceil($project->estimated_hours / 8) : null;
}


    // Get month name for display
    $monthName = \Carbon\Carbon::create()->month($request->month ?? now()->month)->format('F');

    // Calculate prediction and received amount
    $predictionAmount = $projects->sum('price');
    $amountReceived = $projects->sum(function ($project) {
        return $project->payments->sum('amount') ?? 0; // assuming payments relation exists
    });

    return view('dashboard.sales_team_working_projects', [
        'projects' => $projects,
        'monthName' => $monthName,
        'predictionAmount' => $predictionAmount,
        'amountReceived' => $amountReceived,
        'pm' => null,
        'status' => $request->status ?? 'working',
        'projectManagers' => User::role('Project Manager')->get(),
        'teamLeads' => User::role('Team Lead')->get(),
        'selectedSalesPersonId' => $user_id, // pass to auto-select filter if needed
        'salesPersons' => User::role('Sales Team')->get(),
    ]);
}

    
public function teamReport(Request $request)
{
    $user = Auth::user();
    $selectedMonth = $request->input('report_month', now()->format('m'));
    $selectedYear = $request->input('report_year', now()->format('Y'));

    // Project Manager
    $pmId = $user->hasRole('Project Manager') ? $user->id : 0;

    $teamMembers = User::select('users.*')
        ->join('departments', 'departments.id', '=', 'users.department_id')
        ->where(function ($query) use ($user, $pmId) {
            $query->where('users.reporting_person', $pmId ?: $user->id)
                  ->where('users.id', '!=', $pmId); // exclude PM
        })
        ->where('users.disable_login', 0)
        ->where('departments.name', '!=', 'LEFT People')
        ->get();

    $stats = [
        'totalEmployees' => $teamMembers->count() + ($pmId ? 1 : 0), // include PM
        'totalAssignedHours' => 0,
        'totalWorkedHours' => 0,
        'totalPayment' => 0,
        'totalReceived' => 0,
        'totalUpsellAmount' => 0,
    ];

    $membersData = [];

    // ✅ Add PM as first entry
    if ($pmId) {
        $pm = User::find($pmId);

        // PM Upsell
        $pmUpsell = DB::table('projects')
            ->where('upsell_employee_id', $pmId)
            ->whereYear('created_at', $selectedYear)
            ->when($selectedMonth !== 'ALL', function ($query) use ($selectedMonth, $selectedYear) {
                return $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$selectedYear . '-' . str_pad($selectedMonth, 2, '0', STR_PAD_LEFT)]);
            })
            ->sum('price') ?? 0;

        $membersData[] = [
            'user' => $pm,
            'assignedHours' => 0,
            'workedHours' => 0,
            'payment' => 0,
            'received' => 0,
            'upsell' => $pmUpsell,
        ];

        $stats['totalUpsellAmount'] += $pmUpsell;
    }

    // ✅ Loop team members
    foreach ($teamMembers as $member) {
        $assignedHours = DB::table('projects')
            ->where(function ($query) use ($member) {
                $query->where('project_manager_id', $member->id)
                      ->orWhere('assign_main_employee_id', $member->id)
                      ->orWhereRaw("JSON_CONTAINS(additional_employees, ?)", [json_encode($member->id)]);
            })
            ->where('project_status', 'Working')
            ->whereYear('created_at', '<=', $selectedYear)
            ->when($selectedMonth !== 'ALL', function ($query) use ($selectedMonth, $selectedYear) {
                return $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') <= ?", [$selectedYear . '-' . str_pad($selectedMonth, 2, '0', STR_PAD_LEFT)]);
            })
            ->sum('estimated_hours') ?? 0;

        $workedHours = DB::table('dsrs')
            ->where('user_id', $member->id)
            ->whereYear('created_at', $selectedYear)
            ->when($selectedMonth !== 'ALL', function ($query) use ($selectedMonth, $selectedYear) {
                return $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$selectedYear . '-' . str_pad($selectedMonth, 2, '0', STR_PAD_LEFT)]);
            })
            ->sum('hours') ?? 0;

        $payment = DB::table('projects')
            ->where(function ($query) use ($member) {
                $query->where('project_manager_id', $member->id)
                      ->orWhere('assign_main_employee_id', $member->id)
                      ->orWhereRaw("JSON_CONTAINS(additional_employees, ?)", [json_encode($member->id)]);
            })
            ->whereYear('created_at', '<=', $selectedYear)
            ->when($selectedMonth !== 'ALL', function ($query) use ($selectedMonth, $selectedYear) {
                return $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m') <= ?", [$selectedYear . '-' . str_pad($selectedMonth, 2, '0', STR_PAD_LEFT)]);
            })
            ->sum('price') ?? 0;

        $received = DB::table('project_payments')
            ->join('projects', 'projects.id', '=', 'project_payments.project_id')
            ->where(function ($query) use ($member) {
                $query->where('projects.project_manager_id', $member->id)
                      ->orWhere('projects.assign_main_employee_id', $member->id)
                      ->orWhereRaw("JSON_CONTAINS(projects.additional_employees, ?)", [json_encode($member->id)]);
            })
            ->whereYear('payment_month', $selectedYear)
            ->when($selectedMonth !== 'ALL', function ($query) use ($selectedMonth, $selectedYear) {
                return $query->whereRaw("DATE_FORMAT(payment_month, '%Y-%m') = ?", [$selectedYear . '-' . str_pad($selectedMonth, 2, '0', STR_PAD_LEFT)]);
            })
            ->sum('payment_amount') ?? 0;

        // Update totals
        $stats['totalAssignedHours'] += $assignedHours;
        $stats['totalWorkedHours'] += $workedHours;
        $stats['totalPayment'] += $payment;
        $stats['totalReceived'] += $received;

        $membersData[] = [
            'user' => $member,
            'assignedHours' => $assignedHours,
            'workedHours' => $workedHours,
            'payment' => $payment,
            'received' => $received,
            'upsell' => 0,
        ];
    }

    return view('dashboard.project_manager_team_report', compact(
        'stats',
        'membersData',
        'selectedMonth',
        'selectedYear'
    ));
}




public function index()
{
    $month = request('month', now()->format('m'));
    $year = request('year', now()->format('Y'));

    // ✅ Web Dev department
    $webDevDept = Department::where('name', 'Web Development')->first();
    if (!$webDevDept) {
        return back()->with('error', 'Web Development department not found.');
    }

    // ✅ 1. Get all Working projects (regardless of date)
    // ✅ 2. Get Complete projects created or completed in selected month
    $projects = Project::where('department_id', $webDevDept->id)
        ->where(function ($query) use ($month, $year) {
            $query->where('project_status', 'Working')
                ->orWhere(function ($q) use ($month, $year) {
                    $q->where('project_status', 'Complete')
                      ->where(function ($sub) use ($month, $year) {
                          $sub->whereMonth('created_at', $month)
                              ->whereYear('created_at', $year)
                              ->orWhereMonth('status_date', $month)
                              ->whereYear('status_date', $year);
                      });
                });
        })
        ->with(['dsrs' => function ($q) use ($month, $year) {
            $q->whereMonth('created_at', $month)
              ->whereYear('created_at', $year);
        }])
        ->get();

    // ✅ Summary
    $totalProjects = $projects->count();
    $totalBudget = $projects->sum('price');
    $totalEstimatedHours = $projects->sum('estimated_hours');
    $totalWorkedHours = $projects->pluck('dsrs')->flatten()->sum('hours');

    // ✅ Get employees from Web Dev department
    $employees = User::whereHas('department', function ($q) {
        $q->where('name', 'Web Development');
    })->with(['dsrs' => function ($query) use ($month, $year) {
        $query->whereMonth('created_at', $month)
              ->whereYear('created_at', $year);
    }])->get();

    // ✅ Calculate employee stats
    $employeeStats = $employees->map(function ($employee) use ($projects) {
        $workingHours = $employee->dsrs->sum('hours');

        $assignedProjects = $projects->filter(function ($project) use ($employee) {
            $additional = $project->additional_employees;

            $ids = is_string($additional)
                ? json_decode($additional, true)
                : (is_array($additional) ? $additional : []);

            return in_array($employee->id, $ids);
        });

        return [
            'name' => $employee->name,
            'projects' => $assignedProjects->count(),
            'hours' => $workingHours,
        ];
    });

    return view('dashboard.index', compact(
        'projects',
        'totalProjects',
        'totalBudget',
        'totalEstimatedHours',
        'totalWorkedHours',
        'employeeStats',
        'employees'
    ));
}




}
