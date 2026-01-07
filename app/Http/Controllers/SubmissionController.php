<?php

namespace App\Http\Controllers;
use App\Models\SubmissionCategory;
use App\Models\SubmissionSite;
use Illuminate\Support\Facades\Log;



use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function index(Request $request)
    {
        // Get search query from textarea
        $search = trim($request->query('search', ''));
        $categories = SubmissionCategory::withCount('sites')->orderBy('name')->get();

        if ($search) {
            // Split search terms by newlines, spaces, or commas (trim and filter empty)
            $searchTerms = array_filter(array_map('trim', preg_split('/[\r\n,\s]+/', strtolower($search))));
            Log::info('Search terms from textarea: ' . json_encode($searchTerms)); // Debug log

            if (empty($searchTerms)) {
                return view('submissions.index', compact('categories', 'search'));
            }

            // Search for websites matching any term, with valid categories
            $sites = SubmissionSite::with(['submissionCategory' => function ($query) {
                $query->whereNotNull('id'); // Ensure category exists
            }])
                ->where(function ($query) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $query->orWhereRaw('LOWER(website_name) LIKE ?', ['%' . $term . '%']);
                    }
                })
                ->orderBy('created_at', 'desc') // Newest sites first
                ->get();

            // Log sites with invalid category_id for debugging
            $invalidSites = $sites->filter(function ($site) {
                return is_null($site->submissionCategory);
            });
            if ($invalidSites->isNotEmpty()) {
                Log::warning('Sites with invalid category_id found: ' . $invalidSites->pluck('id')->toJson());
            }

            // Filter out sites with null categories
            $sites = $sites->filter(function ($site) {
                return !is_null($site->submissionCategory);
            });

            // Group sites by category for display (like reference site)
            $groupedSites = $sites->groupBy('submissionCategory.id')->map(function ($group, $categoryId) use ($sites) {
                $category = $sites->firstWhere('submissionCategory.id', $categoryId)->submissionCategory;
                return [
                    'category' => $category,
                    'sites' => $group->values(),
                ];
            })->values();

            return view('submissions.index', compact('groupedSites', 'search', 'categories'));
        }

        return view('submissions.index', compact('categories', 'search'));
    }
    // Single category with sites
    public function show($slug)
    {
        $category = SubmissionCategory::where('slug', $slug)
                    ->with('sites')
                    ->firstOrFail();

        return view('submissions.show', compact('category'));
    }
}
