<?php
namespace App\Http\Controllers;

use App\Models\SubmissionSite;
use App\Models\SubmissionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubmissionSiteController extends Controller
{
    public function index()
    {
        $loggedInUser = auth()->user();
        
        if (!$loggedInUser->hasRole('Admin')) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
        }
        $sites = SubmissionSite::with('category')->orderBy('created_at', 'desc')->paginate(25);
        $categories = SubmissionCategory::orderBy('name')->get(); // Pass categories for the modal
        return view('submission_sites.index', compact('sites', 'categories'));
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'category_id' => 'required|exists:submission_categories,id',
                'website_name' => 'required|string|max:255',
                'register_url' => 'nullable|url|max:1000',
                'category' => 'nullable|string|max:150',
                'country' => 'nullable|string|max:150',
                'moz_da' => 'nullable|integer|min:0|max:100',
                'spam_score' => 'nullable|integer|min:0|max:100',
                'traffic' => 'nullable|string|max:50',
                'submission_type' => 'nullable|string|max:50',
                'report_url' => 'nullable|url|max:1000',
            ]);

            SubmissionSite::create($data);

            return response()->json(['message' => 'Submission site added successfully']);
        } catch (\Exception $e) {
            Log::error('Store submission site error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to add submission site: ' . $e->getMessage()], 500);
        }
    }

    public function edit(SubmissionSite $submissionSite)
    {
        try {
            Log::info('Edit submission site called for ID: ' . $submissionSite->id);
            $categories = SubmissionCategory::orderBy('name')->get();
            return response()->json(['site' => $submissionSite, 'categories' => $categories]);
        } catch (\Exception $e) {
            Log::error('Edit submission site error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch site data: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, SubmissionSite $submissionSite)
    {
        try {
            $data = $request->validate([
                'category_id' => 'required|exists:submission_categories,id',
                'website_name' => 'required|string|max:255',
                'register_url' => 'nullable|url|max:1000',
                'category' => 'nullable|string|max:150',
                'country' => 'nullable|string|max:150',
                'moz_da' => 'nullable|integer|min:0|max:100',
                'spam_score' => 'nullable|integer|min:0|max:100',
                'traffic' => 'nullable|string|max:50',
                'submission_type' => 'nullable|string|max:50',
                'report_url' => 'nullable|url|max:1000',
            ]);

            $submissionSite->update($data);

            return response()->json(['message' => 'Submission site updated successfully']);
        } catch (\Exception $e) {
            Log::error('Update submission site error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update submission site: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(SubmissionSite $submissionSite)
    {
        try {
            $submissionSite->delete();
            return response()->json(['message' => 'Submission site deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Delete submission site error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete submission site: ' . $e->getMessage()], 500);
        }
    }

    public function report(Request $request, SubmissionSite $submissionSite)
    {
        try {
            $request->validate(['reason' => 'required|string']);
            $submissionSite->reports()->create(['reason' => $request->reason]);
            return response()->json(['message' => 'Site reported successfully']);
        } catch (\Exception $e) {
            Log::error('Report submission site error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to report site: ' . $e->getMessage()], 500);
        }
    }
}