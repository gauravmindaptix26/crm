<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\HiredFrom;
use App\Models\User;

use Illuminate\Http\Request;

class ProjectAuditController extends Controller
{
    public function index(Request $request)
    {
        $loggedInUser = auth()->user();
    
        // ✅ Restrict access: only Admin or Project Manager allowed
        if (!($loggedInUser->hasRole('Admin') || $loggedInUser->hasRole('Project Manager'))) {
            return redirect()->route('dashboard')
                ->with('error', 'You are not authorized to access this page.');
        }
    
        $hiredFroms = HiredFrom::all();
    
        // 🔹 If user is Admin, fetch all Project Managers for filter dropdown
        $projectManagers = [];
        if (auth()->user()->hasRole('Admin')) {
            $projectManagers = User::role('Project Manager')->get();
        }
    
        // Base query with relationships
        $query = Project::with([
            'hiredFrom', 'projectManager', 'teamLead', 'salesPerson',
            'department', 'assignMainEmployee', 'upsellEmployee',
            'projectPayments', 'projectCategory', 'projectSubCategory',
            'country', 'attachments', 'saleTeamAttachments'
        ]);
    
        // ❌ REMOVE THIS LINE (it was limiting to current year only)
        // $currentYear = now()->year;
        // $query->whereYear('created_at', $currentYear);
    
        // 🔹 Only "working" projects
        $query->where('project_status', 'Working');
    
        // 🔹 Restrict for Project Managers
        if (auth()->user()->hasRole('Project Manager')) {
            $query->where('project_manager_id', auth()->id());
        }
    
        // 🔹 Default duration = 2 months if not selected
        $duration = $request->get('duration', 2);
    
        if ($duration) {
            $months = (int) $duration;
            $dateThreshold = now()->subMonths($months);
    
            // ✅ Show projects created within last X months
            $query->where('created_at', '>=', $dateThreshold);
        }
    
        // 🔹 Filter: Hired From
        if ($request->filled('hired_from_id')) {
            $query->where('hired_from_id', $request->hired_from_id);
        }
    
        // 🔹 Filter: Project Manager (Admin only)
        if ($request->filled('project_manager_id') && auth()->user()->hasRole('Admin')) {
            $query->where('project_manager_id', $request->project_manager_id);
        }
    
        // Pagination
        $paginatedProjects = $query->paginate($request->get('entries_per_page', 20));
    
        return view('projects.audit', compact('paginatedProjects', 'hiredFroms', 'projectManagers', 'duration'));
    }
    
    
}
