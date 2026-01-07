<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;

class SubmittedTaskReportController extends Controller
{
    public function index(Request $request)
    {
        $users = User::all();
        $filterUser = $request->input('filter_user');

        $submittedTasksQuery = Task::with(['createdBy', 'assignedUsers'])
            ->whereNotNull('done_message');

        if ($filterUser) {
            $submittedTasksQuery->whereHas('assignedUsers', function ($q) use ($filterUser) {
                $q->where('users.id', $filterUser);
            });
        }

        $submittedTasks = $submittedTasksQuery->paginate(10);

        return view('submitted_tasks.index', compact('submittedTasks', 'users', 'filterUser'));
    }
}
