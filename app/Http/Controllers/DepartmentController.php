<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;



class DepartmentController extends Controller
{
    public function index()
    {
        if (!Auth::user()->hasAnyRole(['HR', 'Admin'])) {
            return redirect()->route('dashboard'); // Redirect others
        }
        //$departments = Department::all();
        $departments = Department::paginate(10);
        return view('departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|nullable|string'
        ]);
    
        $department = Department::create($validated);
    
        return response()->json(['success' => 'Department added successfully!', 'department' => $department]);
    }
    
    
    public function edit(Department $department)
    {
        return response()->json($department);
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
    
        $department->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);
    
        return response()->json(['success' => 'Department updated successfully']);
    }
    

    public function destroy($id)
    {
        $department = Department::find($id);
    
        if (!$department) {
            return response()->json(['error' => 'Department not found'], 404);
        }
    
        $department->delete();
        return response()->json(['success' => 'Department deleted successfully']);
    }
    

}
