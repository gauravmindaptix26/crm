<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ProjectTask;
use App\Models\Country;
use App\Models\TaskPhase;
use App\Models\ProjectTaskAttachment;

class ProjectTaskController extends Controller
{
    public function index()
    {

        $loggedInUser = auth()->user();

        // Redirect Employees to dashboard
        if ($loggedInUser->hasRole('Employee')) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
        }
    
        $projectTasks = ProjectTask::with('country', 'phase','createdBy')->paginate(10);
       // dd($projectTasks);
        $countries = Country::all();
        $projectPhases = TaskPhase::all();

        return view('project_tasks.index', compact('projectTasks', 'countries', 'projectPhases'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'task_phase_id' => 'required|exists:task_phases,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order_number' => 'required|integer',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
            'video_link' => 'nullable|url',
            'tool_link' => 'nullable|url',
        ]);

        $validated['created_by'] = auth()->id();
        $projectTask = ProjectTask::create($validated);

        // Handle file uploads
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments', 'public');
                ProjectTaskAttachment::create([
                    'project_task_id' => $projectTask->id,
                    'file_path' => $path
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Project Task added successfully!',
            'task' => $projectTask->load('country', 'phase')
        ]);
    }

    public function edit($id)
    {
        $projectTask = ProjectTask::with('attachments','country')->find($id);
        //dd($projectTask);
        if (!$projectTask) {
            return response()->json(['error' => 'Project Task not found'], 404);
        }

       return response()->json($projectTask);
        
    }

   public function update(Request $request, $id)
{
    $task = ProjectTask::findOrFail($id);

    $validated = $request->validate([
        'country_id' => 'nullable|exists:countries,id',
        'task_phase_id' => 'required|exists:task_phases,id',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'order_number' => 'required|integer',
        'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        'video_link' => 'nullable|url',
        'tool_link' => 'nullable|url',
    ]);

    // Only update country_id if it is provided
    if ($request->has('country_id') && $request->country_id) {
        $task->country_id = $request->country_id;
    }

    // Update other fields
    $task->update($validated);

    // Handle file uploads
    if ($request->hasFile('attachments')) {
        foreach ($request->file('attachments') as $file) {
            $path = $file->store('attachments', 'public');
            ProjectTaskAttachment::create([
                'project_task_id' => $task->id,
                'file_path' => $path
            ]);
        }
    }

    return response()->json([
        'success' => true,
        'message' => 'Project Task updated successfully!',
        'task' => $task->load('country', 'phase')
    ]);
}


public function destroy(Request $request, $id)
{
    if ($request->has('type') && $request->type == 'deleteAttachment') {
        return $this->deleteAttachment($id);
    }

    // Default delete functionality for ProjectTask
    $task = ProjectTask::findOrFail($id);
    
    if ($task) {
        $task->delete();
        return response()->json(['success' => true, 'message' => 'Project Task deleted successfully!']);
    }

    return response()->json(['success' => false, 'message' => 'Project Task not found!']);
}

/**
 * Delete attachment function (used inside destroy)
 */
private function deleteAttachment($attachmentId)
{
    $attachment = ProjectTaskAttachment::find($attachmentId);

    if (!$attachment) {
        return response()->json(['success' => false, 'message' => 'Attachment not found!']);
    }

    // Delete the file from storage
    if (\Storage::exists($attachment->file_path)) {
        \Storage::delete($attachment->file_path);
    }

    // Delete the attachment record
    $attachment->delete();

    return response()->json(['success' => true, 'message' => 'Attachment deleted successfully!']);
}



}
