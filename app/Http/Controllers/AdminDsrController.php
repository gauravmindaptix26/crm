<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SeoPmDsr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class AdminDsrController extends Controller
{
    public function index()
    {
        $projectManagers = User::role('Project Manager')
            ->whereHas('department', function ($query) {
                $query->where('id', 4); // SEO Department
            })
            ->whereDoesntHave('department', function ($query) {
                $query->where('id', 15); // Exclude LEFT People
            })
            ->with(['latestDailyDsr' => function ($query) {
                $query->where('type', 'daily')
                      ->orderByDesc('report_date');
            }])
            ->orderBy('name')
            ->get();
    
        return view('admin.dsr.index', compact('projectManagers'));
    }

  public function show($pm_id)
{
    $pm = User::findOrFail($pm_id);

    $month = request('month');
    $year  = request('year', now()->year);
    $tab   = request('tab', 'daily');

    // DAILY
    $daily = SeoPmDsr::where('pm_id', $pm_id)
        ->where('type', 'daily')
        ->when($month, function ($q) use ($month, $year) {
            $q->whereMonth('report_date', $month)
              ->whereYear('report_date', $year);
        })
        ->orderByDesc('report_date')
        ->paginate(4);

    // WEEKLY
    $weekly = SeoPmDsr::where('pm_id', $pm_id)
        ->where('type', 'weekly')
        ->when($month, function ($q) use ($month, $year) {
            $q->whereMonth('report_date', $month)
              ->whereYear('report_date', $year);
        })
        ->orderByDesc('report_date')
        ->paginate(8);

    // MONTHLY
    $monthly = SeoPmDsr::where('pm_id', $pm_id)
        ->where('type', 'monthly')
        ->when($month, function ($q) use ($month, $year) {
            $q->whereMonth('report_date', $month)
              ->whereYear('report_date', $year);
        })
        ->orderByDesc('report_date')
        ->paginate(8);

    $reports = [
        'daily'   => $daily,
        'weekly'  => $weekly,
        'monthly' => $monthly,
    ];

    // Available filters
    $availableYears = SeoPmDsr::where('pm_id', $pm_id)
        ->selectRaw('YEAR(report_date) as year')
        ->distinct()
        ->orderByDesc('year')
        ->pluck('year');

    $availableMonths = SeoPmDsr::where('pm_id', $pm_id)
        ->when($year, fn ($q) => $q->whereYear('report_date', $year))
        ->selectRaw('MONTH(report_date) as month')
        ->distinct()
        ->orderByDesc('month')
        ->pluck('month');

    return view(
        'admin.dsr.show',
        compact('pm', 'reports', 'tab', 'month', 'year', 'availableYears', 'availableMonths')
    );
}


    public function view($id)
    {
        $report = SeoPmDsr::findOrFail($id);
        // Fetch all previous reports of SAME TYPE for SAME PM
    $previousReports = SeoPmDsr::where('pm_id', $report->pm_id)
    ->where('type', $report->type)
    ->where('id', '!=', $report->id)
    ->orderByDesc('report_date')
    ->get();
        return view('admin.dsr.view', compact('report','previousReports'));
    }

    public function updateCooRating(Request $request, $id)
    {
        Log::info('COO Rating Update Attempt Started', [
            'report_id' => $id,
            'user_id'   => auth()->id(),
            'ip'        => $request->ip(),
            'user_agent'=> $request->userAgent(),
            'request_data' => $request->all()
        ]);
    
        try {
            $request->validate([
                'coo_rating' => 'required|integer|min:1|max:10',
                'coo_notes'  => 'nullable|string|max:1000',
            ]);
    
            Log::info('Validation Passed', ['coo_rating' => $request->coo_rating]);
    
            $report = SeoPmDsr::find($id);
    
            if (!$report) {
                Log::error('Report Not Found', ['report_id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Report not found'
                ], 404);
            }
    
            Log::info('Report Found', [
                'report_id' => $report->id,
                'pm_id'     => $report->pm_id,
                'current_coo_rating' => $report->coo_rating
            ]);
    
            $updated = $report->update([
                'coo_rating'      => $request->coo_rating,
                'coo_notes'       => $request->coo_notes,
                'coo_reviewed_by' => auth()->id(),
                'coo_reviewed_at' => now(),
            ]);
    
            if ($updated) {
                Log::info('COO Rating Updated Successfully', [
                    'report_id' => $report->id,
                    'new_rating' => $request->coo_rating,
                    'reviewed_by' => auth()->id()
                ]);
    
                return response()->json([
                    'success' => true,
                    'rating'  => $request->coo_rating,
                    'color'   => $this->getRatingColor($request->coo_rating),
                ]);
            } else {
                Log::warning('Update Failed - No Changes Made', ['report_id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'No changes were made'
                ], 400);
            }
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation Failed', [
                'errors' => $e->errors(),
                'input'  => $request->all()
            ]);
    
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Unexpected Error in updateCooRating', [
                'report_id' => $id,
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'trace'     => $e->getTraceAsString()
            ]);
    
            return response()->json([
                'success' => false,
                'message' => 'Server error'
            ], 500);
        }
    }
    
    private function getRatingColor($rating)
    {
        return match (true) {
            $rating >= 9 => 'green',
            $rating >= 7 => 'blue',
            $rating >= 5 => 'yellow',
            default      => 'red',
        };
    }
}
