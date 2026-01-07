<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManageLink;
use App\Models\ProjectTask;
use Illuminate\Support\Facades\Auth;

class ManageLinkController extends Controller
{
    public function index(Request $request)
    {
        $project_task_id = $request->project_task_id;
        $projectTask = ProjectTask::findOrFail($project_task_id);
    
        $manageLinks = ManageLink::where('project_task_id', $project_task_id)->paginate(10);
    
        // Fetch all project tasks for the dropdown
        $projectTasks = ProjectTask::all();
    
        return view('manage-links.index', compact('manageLinks', 'projectTask', 'projectTasks'));
    }
    

    public function store(Request $request)
    {
        $request->validate([
            'link' => 'url|max:255',
            'pa' => 'integer',
            'da' => 'integer',
        ]);

        $manageLink = ManageLink::create([
            'project_task_id' => $request->project_task_id,
            'link' => $request->link,
            'pa' => $request->pa,
            'da' => $request->da,
            'created_by' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Link added successfully.', 'manageLink' => $manageLink]);
    }

    public function edit(ManageLink $manageLink)
    {
        return response()->json($manageLink);
    }

    public function update(Request $request, ManageLink $manageLink)
    {
        $request->validate([
            'link' => 'required|url|max:255',
            'pa' => 'required|integer|min:1|max:100',
            'da' => 'required|integer|min:1|max:100',
        ]);

        $manageLink->update([
            'link' => $request->link,
            'pa' => $request->pa,
            'da' => $request->da,
        ]);

        return response()->json(['success' => true, 'message' => 'Link updated successfully.']);
    }

    public function destroy(ManageLink $manageLink)
    {
        $manageLink->delete();
        return response()->json(['success' => true, 'message' => 'Link deleted successfully.']);
    }
}
