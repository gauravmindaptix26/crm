<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;
use App\Models\Department;
use App\Models\TaskPhase;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;





class ProjectPortfolioController extends Controller
{
    public function index(Request $request)
{

    $loggedInUser = auth()->user();

    // Redirect Employees to dashboard
    if ($loggedInUser->hasRole('Employee')) {
        return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
    }

    $user = Auth::user();

    $projects = Project::with([
        'projectManager',
        'salesPerson',
        'department',
        'assignMainEmployee',
        'projectCategory',
        'projectSubCategory',
        'country',
    ])
    ->when($request->project_manager_id, fn($q) => $q->where('project_manager_id', $request->project_manager_id))
    ->when($request->sales_person_id, fn($q) => $q->where('sales_person_id', $request->sales_person_id))
    ->when($request->department_id, fn($q) => $q->where('department_id', $request->department_id))
    ->when($request->assign_main_employee_id, fn($q) => $q->where('assign_main_employee_id', $request->assign_main_employee_id))
    ->when($request->project_status, fn($q) => $q->where('project_status', $request->project_status))
    ->when($request->client_type, fn($q) => $q->where('client_type', $request->client_type))

    // 🔐 Apply role-based visibility
    ->when($user->hasRole('Project Manager'), function ($q) use ($user) {
        $adminIds = \Spatie\Permission\Models\Role::where('name', 'Admin')
                        ->first()
                        ->users()
                        ->pluck('id')
                        ->toArray();

        $q->where(function ($subQuery) use ($user, $adminIds) {
            $subQuery->where('department_id', $user->department_id)
                     ->orWhere('project_manager_id', $user->id)
                     ->orWhereIn('project_manager_id', $adminIds);
        });
    })

    // If not Sales Team, show nothing
    ->when(!($user->hasAnyRole(['Admin', 'HR', 'Project Manager', 'Sales Team'])), function ($q) {
        $q->whereRaw('1 = 0'); // Block access for other roles
    })
    

    ->latest()
    ->paginate(10);

    return view('project-portfolio.index', [
        'projects' => $projects,
        'projectManagers' => User::role('Project Manager')->get(),
        'salesPersons' => User::role('Sales Team')->get(),
        'departments' => Department::all(),
        'employees' => User::all(),
    ]);
}
}


