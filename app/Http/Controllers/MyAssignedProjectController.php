<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project; // Adjust model name if needed
use Illuminate\Support\Facades\Auth;

class MyAssignedProjectController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
    
        $projects = Project::where('assign_main_employee_id', $userId) // Ensure column name matches
            ->with(['projectManager', 'salesPerson', 'department', 'projectCategory', 'projectSubCategory', 'country', 'assignMainEmployee'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    
        return view('my-assigned-projects.index', compact('projects'));
    }
}

