<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dsr;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DsrController extends Controller
{


   // DsrController.php

   public function index()
{
    // Allow only Project Manager, Admin, or HR
    if (!Auth::user()->hasRole('Project Manager') && !Auth::user()->hasRole('Admin') && !Auth::user()->hasRole('HR')) {
        return redirect()->route('dashboard');
    }

    $user = Auth::user();

    if ($user->hasRole('Admin') || $user->hasRole('HR')) {
        // Admin and HR: show all users' DSRs
        $dsrReports = Dsr::selectRaw('user_id, DATE(created_at) as report_date, SUM(hours) as total_hours')
            ->groupBy('user_id', 'report_date')
            ->orderByDesc('report_date')
            ->paginate(10);
    } else {
        // Project Manager: only their team's DSRs
        $teamMembers = User::where('reporting_person', $user->id)->pluck('id');

        $dsrReports = Dsr::selectRaw('user_id, DATE(created_at) as report_date, SUM(hours) as total_hours')
            ->whereIn('user_id', $teamMembers)
            ->groupBy('user_id', 'report_date')
            ->orderByDesc('report_date')
            ->paginate(10);
    }

    return view('dsr.team', compact('dsrReports'));
}

   
   public function view($user_id, $report_date)
   {
       // Fetch all DSRs by user for the given date
       $dsrReports = Dsr::where('user_id', $user_id)
                        ->whereDate('created_at', $report_date)
                        ->get();
   
       if ($dsrReports->isEmpty()) {
           abort(404, 'No DSRs found for this user on the selected date.');
       }
   
       $dsr = $dsrReports->first(); // Use first to access common user info, etc.
   
       return view('dsr.details', compact('dsr', 'dsrReports'));
   }
   

   public function create()
   {
       $user = Auth::user();
       $userId = $user->id;
   
       $projectsQuery = Project::query();
   
       // Team Lead: see assigned projects
       if ($user->hasRole('Team Lead')) {
        $projectsQuery->where(function ($q) use ($userId) {
            $q->where('team_lead_id', $userId)
              ->orWhere('project_manager_id', $userId)
              ->orWhere('assign_main_employee_id', $userId)
              ->orWhereJsonContains('additional_employees', $userId)

              // Projects assigned via assigned_projects table (TL, PM, or employee under them)
              ->orWhereIn('projects.id', function ($sub) use ($userId) {
                  $sub->select('project_id')
                      ->from('assigned_projects')
                      ->where('team_lead_id', $userId)
                      ->orWhere('project_manager_id', $userId)
                      ->orWhere('assigned_employee_id', $userId);
              });
        });
    }
   
       // Employee: see projects as main or additional
       if ($user->hasRole('Employee')) {
           $projectsQuery->where(function ($query) use ($userId) {
               $query->where('assign_main_employee_id', $userId)
               ->orWhereJsonContains('additional_employees', $userId);
           });
       }
   
       // Load projects with relations
       $assignedProjects = $projectsQuery
           ->with(['projectCategory', 'projectSubCategory', 'country'])
           ->get();
   
       $allUsers = User::all();
   
       // Last DSR
       $lastReport = DSR::where('user_id', $userId) 
           ->latest()
           ->first();
   
       // Today's total hours
       $totalTodayHours = DSR::where('user_id', $userId)
           ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
           ->sum('hours');
   
       return view('dsr.create', compact(
           'assignedProjects',
           'allUsers',
           'lastReport',
           'totalTodayHours'
       ));
   }
   


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'project_id' => [
    'required',
    function ($attribute, $value, $fail) {
        $staticIds = ['9991', '9992', '9993']; // IDs of your static projects
        
        // If not in static IDs, check if it exists in projects table
        if (!in_array($value, $staticIds)) {
            $exists = \DB::table('projects')->where('id', $value)->exists();
            if (!$exists) {
                $fail('The selected project is invalid.');
            }
        }
    },
],

            'work_description'  => 'required|string',
            'hours_spent'       => 'required|numeric|min:0.5|max:24',
            'helped_by'         => 'nullable|exists:users,id',
            'help_description'  => 'nullable|string',
            'help_rating'       => 'nullable|integer',
            'replied_emails'    => 'required|boolean',
            'updated_report'    => 'required|boolean',
            'justified_work'    => 'required|boolean',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $validator->errors(),
            ], 422);
        }
    
        $user = Auth::user();
    
        // Save the DSR data
        $dsr = Dsr::create([
            'user_id'           => $user->id,
            'project_id'        => $request->input('project_id'),
            'work_description'  => $request->input('work_description'),
            'hours'             => $request->input('hours_spent'),
            'helped_by'         => $request->input('helped_by'),
            'help_description'  => $request->input('help_description'),
            'help_rating'       => $request->input('help_rating'),
            'replied_to_emails' => $request->input('replied_emails'),
            'sent_report'       => $request->input('updated_report'),
            'justified_work'    => $request->input('justified_work'),
        ]);
      // Calculate total hours today for the user (including static projects)
      $totalTodayHours = Dsr::where('user_id', auth()->id())
      ->whereDate('created_at', now()->toDateString())
      ->sum('hours');
        return response()->json([
            'status' => 'success',
            'message' => 'DSR submitted successfully!',
            'data' => $dsr,
            'total_today_hours' => $totalTodayHours, // <== important

        ]);
    }
    public function allEmployeeDsr()
    {
        $users = User::all(); // For the dropdown
        
        // Get current month and year
        $month = now()->month;
        $year = now()->year;
    
        return view('dsr.employee_all_dsr', compact('users', 'month', 'year'));
    }
public function searchEmployeeDsr(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'month' => 'required|numeric|min:1|max:12',
        'year' => 'required|numeric|min:2000',
    ]);

    $userId = $request->user_id;
    $month = $request->month;
    $year = $request->year;

    $dsrReports = Dsr::where('user_id', $userId)
        ->whereMonth('created_at', $month)
        ->whereYear('created_at', $year)
        ->get();

    // Eager load 'project' relation here
    $dsrDetails = Dsr::with('project')
        ->whereIn('id', $dsrReports->pluck('id'))
        ->get();

    $user = User::find($userId);
    $users = User::all();

    return view('dsr.employee_all_dsr', compact('dsrReports', 'dsrDetails', 'user', 'users', 'month', 'year'));
}
public function showPreviousDsrs()
{
    $dsrs = Dsr::with(['project', 'user', 'helper'])  // Optional: eager load relationships
                ->where('user_id', Auth::id())        // Only for logged-in user
                ->orderByDesc('created_at')
                ->paginate(10);                       // Paginate if needed

    return view('dsr.previous', compact('dsrs'));
}
}    
