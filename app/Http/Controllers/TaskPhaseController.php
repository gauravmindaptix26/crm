<?php


namespace App\Http\Controllers;

use App\Models\TaskPhase;
use Illuminate\Http\Request;

class TaskPhaseController extends Controller
{
    public function index()
    {
        $taskPhases = TaskPhase::with('creator')->paginate(10);
        return view('task_phases.index', compact('taskPhases'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        TaskPhase::create([
            'title' => $request->title,
            'description' => $request->description,
            'created_by' => auth()->id(),
        ]);
    
        return response()->json(['success' => 'Task Phase added successfully!']);
    }

    public function edit($id)
    {
        $taskPhase = TaskPhase::find($id);
        
        if (!$taskPhase) {
            return response()->json(['error' => 'Task Phase not found'], 404);
        }

        return response()->json($taskPhase);
    }

   public function update(Request $request, $id)
{
    $validator = \Validator::make($request->all(), [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $taskPhase = TaskPhase::find($id);
    
    if (!$taskPhase) {
        return response()->json(['error' => 'Task Phase not found'], 404);
    }

    $taskPhase->update([
        'title' => $request->title,
        'description' => $request->description,
    ]);

    return response()->json(['success' => 'Task Phase updated successfully!']);
}

    public function destroy(TaskPhase $taskPhase)
    {
        $taskPhase->delete();
        return response()->json(['success' => 'Task Phase deleted successfully!']);
    }
}
