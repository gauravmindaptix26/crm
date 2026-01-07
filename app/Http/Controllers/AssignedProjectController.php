<?php

namespace App\Http\Controllers;

use App\Models\AssignedProject;
use App\Models\User;
use App\Models\Project;
use App\Models\SaleTeamProject;
use Illuminate\Support\Facades\Auth;


use Illuminate\Http\Request;

class AssignedProjectController extends Controller
{
    public function index(Request $request)
{
    $allProjects = Project::all();
    $projectManagers = User::role('Project Manager')->orderBy('name')->get();
    $teamLeaders = User::role('Team Lead')->orderBy('name')->get();
    $employees = User::role('Employee')->orderBy('name')->get();

    $project_id = $request->query('project_id');

    if ($project_id) {
        $latest = AssignedProject::with(['projectManager', 'teamLead', 'assignedEmployee'])
                    ->where('project_id', $project_id)
                    ->latest('id')
                    ->first();

        $assignedProjects = collect();
        if ($latest) {
            $assignedProjects->push($latest);
        }

        $assignedProjects = new \Illuminate\Pagination\LengthAwarePaginator(
            $assignedProjects,
            $assignedProjects->count(),
            10,
            $request->get('page', 1),
            ['path' => $request->url(), 'query' => $request->query()]
        );
    } else {
        // Group by sale_team_project_id if source_type is sale_team, else by project_id
        $latestAssignmentIds = AssignedProject::selectRaw('MAX(id) as id')
            ->groupByRaw("CASE 
                            WHEN source_type = 'sale_team' 
                                THEN (SELECT sale_team_project_id FROM projects WHERE projects.id = assigned_projects.project_id LIMIT 1)
                            ELSE project_id 
                         END")
            ->pluck('id');

        $assignedProjects = AssignedProject::with(['projectManager', 'teamLead', 'assignedEmployee'])
            ->whereIn('id', $latestAssignmentIds)
            ->orderByDesc('id')
            ->paginate(10);
    }

    return view('assigned_projects.index', compact(
        'assignedProjects',
        'allProjects',
        'projectManagers',
        'teamLeaders',
        'employees',
        'project_id'
    ));
}


    
public function store(Request $request)
{
    $project_id = $request->query('project_id');

    $saleProject = SaleTeamProject::find($project_id);
    if (!$saleProject) {
        return redirect()->route('assigned-projects.index')->with('error', 'Project not found.');
    }

    $validated = $request->validate([
        'project_manager_id' => 'nullable|exists:users,id',
        'team_lead_id' => 'nullable|exists:users,id',
        'assigned_employee_id' => 'nullable|exists:users,id',
        'hour' => 'nullable|integer',
    ]);

    AssignedProject::create([
        'project_id' => $project_id,
        'source_type' => 'sale_team',
        'project_manager_id' => $validated['project_manager_id'] ?? null,
        'team_lead_id' => $validated['team_lead_id'] ?? null,
        'assigned_employee_id' => $validated['assigned_employee_id'] ?? null,
        'hour' => $validated['hour'] ?? null,
        'price' => $saleProject->price_usd,
    ]);

    Project::updateOrCreate(
        [
            'sale_team_project_id' => $saleProject->id,
            'source_type' => 'sale_team',
        ],
        [
            'sale_team_project_id' => $saleProject->id,
            'name_or_url' => $saleProject->name_or_url,
            'dashboard_url' => $saleProject->dashboard_url,
            'project_status' => $saleProject->project_status ?? 'working',
            'project_manager_id' => $validated['project_manager_id'] ?? null,
            'team_lead_id' => $validated['team_lead_id'] ?? null,
            'assign_main_employee_id' => $validated['assigned_employee_id'] ?? null,
            'estimated_hours' => $validated['hour'] ?? null,
            'price' => $saleProject->price_usd,
            'client_name' => $saleProject->client_name,
            'client_email' => $saleProject->client_email,
            'country_id' => $saleProject->country_id,
            'sales_person_id' => $saleProject->sales_person_id,
            'department_id' => $saleProject->department_id,
            'created_by' => Auth::id(),
            'description' => $saleProject->description,
            'client_type' => $saleProject->client_type,
            'project_type' => $saleProject->project_type,
        ]
    );

    return redirect()->back()->with('success', 'Project assigned successfully')->with('project_id', $project_id);

}
    
    
    public function edit(AssignedProject $assignedProject)
    {
        // Passing the assigned project along with all the other necessary data to the view
        $allProjects = Project::all();
        $projectManagers = User::role('Project Manager')->get();
       // dd($projectManagers);
        $teamLeaders = User::role('Team Lead')->get();
        $employees = User::role('Employee')->get();

        return view('assigned_projects.index', compact('assignedProject', 'allProjects', 'projectManagers', 'teamLeaders', 'employees'));
    }

    public function update(Request $request, AssignedProject $assignedProject)
    {
        $validated = $request->validate([
            'project_manager_id' => 'required|exists:users,id',
            'team_lead_id' => 'required|exists:users,id',
            'assigned_employee_id' => 'required|exists:users,id',
        ]);

        $assignedProject->update($validated);

        return redirect()->route('assigned-projects.index')->with('success', 'Project updated successfully');
    }

    public function destroy($id)
    {
        $assignedProject = AssignedProject::find($id);

        if (!$assignedProject) {
            return response()->json(['error' => 'Assigned project not found'], 404);
        }

        $assignedProject->delete();

        return response()->json(['success' => 'Assigned project deleted successfully']);
    }
}
