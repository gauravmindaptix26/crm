<?php

namespace App\Http\Controllers;

use App\Models\ProjectMonthlyReport;
use Illuminate\Http\Request;
use Auth;

class ProjectMonthlyReportController extends Controller
{
    public function index(Request $request)
    {
        $projectId = $request->get('project_id');
        $reports = ProjectMonthlyReport::with('addedBy')
            ->where('project_id', $projectId)
            ->latest()
            ->paginate(10);

        return view('project_reports.index', compact('reports', 'projectId'));
    }

    public function store(Request $request) 
    {
        try {
            $validated = $request->validate([
                'project_id' => 'exists:projects,id',
                'report_for_month' => 'date', // Validate date format (YYYY-MM-DD)
                
                'details' => 'string',
            ]);
    
           
            $validated['added_by'] = Auth::id();
    
            ProjectMonthlyReport::create($validated);
    
            return response()->json(['success' => 'Report added successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function edit(ProjectMonthlyReport $projectMonthlyReport)
    {
        return response()->json($projectMonthlyReport);
    }

    public function update(Request $request, ProjectMonthlyReport $projectMonthlyReport)
    {
        $validated = $request->validate([
            'report_for_month' => 'date',
            'details' => 'string',
        ]);

        $projectMonthlyReport->update($validated);

        return response()->json(['success' => 'Report updated successfully']);
    }

    public function destroy(ProjectMonthlyReport $projectMonthlyReport)
    {
        $projectMonthlyReport->delete();
        return response()->json(['success' => 'Report deleted successfully']);
    }
}
