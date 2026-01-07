<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Facades\Mail;
use App\Mail\TaskAssignedMail;


class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {



        $user = auth()->user(); // Get currently logged-in user

        // ❌ Redirect Employees
        if ($user->hasRole('Employee')) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
        }

        $users = User::all();
        $filterUser = $request->input('task_filter_by_user');
    
        $user = auth()->user(); // Get currently logged-in user
    
        // Start base query with assigned users loaded
        $tasksQuery = Task::with('assignedUsers')->orderBy('created_at', 'desc');
    
        // Define roles that can view all tasks
        $rolesWithFullAccess = ['Admin', 'HR', 'Team Lead'];
    
        // If the user doesn't have any of the allowed roles, only show their assigned tasks
        if (!$user->hasAnyRole($rolesWithFullAccess)) {
            $tasksQuery->whereHas('assignedUsers', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }
    
        // If the user has access and a filter is applied, show filtered tasks
        if ($user->hasAnyRole($rolesWithFullAccess) && $filterUser) {
            $tasksQuery->whereHas('assignedUsers', function ($q) use ($filterUser) {
                $q->where('users.id', $filterUser);
            });
        }
    
        $tasks = $tasksQuery->paginate(10);
    
        return view('tasks.index', [
            'tasks' => $tasks,
            'users' => $users,
            'filterUser' => $filterUser
        ]);
    }
    
    public function editJson($id)
{
    $task = Task::with('assignedUsers')->findOrFail($id);

    // Format users with their assigned days
    $users = $task->assignedUsers->map(function ($user) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'days' => json_decode($user->pivot->days, true)
        ];
    });

    return response()->json([
        'success' => true,
        'task' => [
            'id' => $task->id,
            'name' => $task->name,
            'description' => $task->description,
            'users' => $users,
        ]
    ]);
}

public function edit($id)
{
    $task = Task::with('assignedUsers')->findOrFail($id);

    // Get users and their assigned days
    $users = $task->assignedUsers->mapWithKeys(function ($user) {
        return [
            $user->id => json_decode($user->pivot->days, true),
        ];
    });

    return response()->json([
        'success' => true,
        'task' => [
            'id' => $task->id,
            'name' => $task->name,
            'description' => $task->description,
            'users' => $users,
        ]
    ]);
}
    

public function show($id)
{
    abort(404); // or redirect somewhere
}
public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'user' => 'array'
    ]);

    $task = Task::create([
        'name' => $request->name,
        'description' => $request->description,
        'created_by' => auth()->id(),
    ]);

    foreach ($request->user as $userId => $days) {
        // Ensure $days is an array before using array_keys
        $daysArray = is_array($days) ? array_keys($days) : [];

        $task->assignedUsers()->attach($userId, [
            'days' => json_encode($daysArray),
        ]);

        $assignedUser = User::find($userId);
        if ($assignedUser && $assignedUser->email) {
            Mail::to($assignedUser->email)->send(new TaskAssignedMail($task));
        }
    }

    return response()->json(['success' => true, 'task' => $task]);
}

   

    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }

    public function showAddMessageForm(Task $task)
    {
        return view('tasks.add', compact('task'));
    }



    public function submitMessage(Request $request, Task $task)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);
    
        $task->update([
            'done_message' => $request->message,
        ]);
    
        return redirect()->route('dashboard')->with('success', 'Message submitted successfully!');
    }
    
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);
    
        // Validate the request without requiring any field
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'users' => 'nullable|array', // optional users array
            'users.*' => 'nullable|array',
        ]);
    
        // Update only fields that are present in the request
        $task->update([
            'name' => $validated['name'] ?? $task->name,
            'description' => $validated['description'] ?? $task->description,
        ]);
    
        // Sync assigned users and their pivot data (if provided)
        if (!empty($validated['users'])) {
            $syncData = [];
            foreach ($validated['users'] as $userId => $days) {
                $syncData[$userId] = ['days' => json_encode($days)];
            }
            $task->assignedUsers()->sync($syncData);
        }
    
        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully!',
        ]);
    }
    
    
    
}
