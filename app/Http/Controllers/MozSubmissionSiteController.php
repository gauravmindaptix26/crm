<?php

namespace App\Http\Controllers;

use App\Models\SubmissionSite;
use App\Models\SubmissionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MozSubmissionSiteController extends Controller
{
    // Show all sites with categories (for modal)
    public function index()
    {
        $sites = SubmissionSite::with('category')->latest()->paginate(25);
        $categories = SubmissionCategory::orderBy('name')->get(); // for modal
        return view('moz_sites.index', compact('sites', 'categories'));
    }

    // Store site and fetch DA/PA
    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:submission_categories,id',
            'website_name' => 'required|string|max:255',
            'register_url' => 'nullable|url|max:1000',
            'category' => 'nullable|string|max:150',
            'country' => 'nullable|string|max:150',
            'submission_type' => 'nullable|string|max:50',
            'report_url' => 'nullable|url|max:1000',
        ]);

        try {
            $mozData = $this->fetchRapidApiData($data['website_name']);
            $data['moz_da'] = $mozData['domain_authority'] ?? null;
            $data['moz_pa'] = $mozData['page_authority'] ?? null;
            $data['spam_score'] = $mozData['spam_score'] ?? null; // if API returns spam_score
            $data['traffic'] = $mozData['traffic'] ?? null;       // if API returns traffic

            $site = SubmissionSite::create($data);

            return response()->json(['message' => 'Site added successfully with DA/PA data', 'site' => $site]);
        } catch (\Exception $e) {
            Log::error('RapidAPI site store error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to add site: ' . $e->getMessage()], 500);
        }
    }

    // Edit site
    public function edit(SubmissionSite $moz_site)
    {
        try {
            $categories = SubmissionCategory::orderBy('name')->get();
            return response()->json(['site' => $moz_site, 'categories' => $categories]);
        } catch (\Exception $e) {
            Log::error('RapidAPI site edit error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch site: ' . $e->getMessage()], 500);
        }
    }

    // Update site
    public function update(Request $request, SubmissionSite $moz_site)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:submission_categories,id',
            'website_name' => 'required|string|max:255',
            'register_url' => 'nullable|url|max:1000',
            'category' => 'nullable|string|max:150',
            'country' => 'nullable|string|max:150',
            'submission_type' => 'nullable|string|max:50',
            'report_url' => 'nullable|url|max:1000',
        ]);

        try {
            $mozData = $this->fetchRapidApiData($data['website_name']);
            $data['moz_da'] = $mozData['domain_authority'] ?? null;
            $data['moz_pa'] = $mozData['page_authority'] ?? null;
            $data['spam_score'] = $mozData['spam_score'] ?? null;
            $data['traffic'] = $mozData['traffic'] ?? null;

            $moz_site->update($data);

            return response()->json(['message' => 'Site updated successfully with DA/PA data', 'site' => $moz_site]);
        } catch (\Exception $e) {
            Log::error('RapidAPI site update error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update site: ' . $e->getMessage()], 500);
        }
    }

    // Delete site
    public function destroy(SubmissionSite $moz_site)
    {
        try {
            $moz_site->delete();
            return response()->json(['message' => 'Site deleted successfully']);
        } catch (\Exception $e) {
            Log::error('RapidAPI site delete error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete site: ' . $e->getMessage()], 500);
        }
    }

    // Fetch data from RapidAPI Moz DA PA
    private function fetchRapidApiData($domain)
{
    try {
        $response = Http::withHeaders([
            'X-RapidAPI-Key' => env('RAPIDAPI_KEY'),
            'X-RapidAPI-Host' => env('RAPIDAPI_HOST'),
            'Content-Type' => 'application/json',
        ])->post('https://domain-da-pa-checker.p.rapidapi.com/v1/getDaPa', [
            'q' => $domain
        ]);

        // Log full response for debugging
        Log::info('RapidAPI response for ' . $domain . ': ' . $response->body());

        if ($response->successful()) {
            return $response->json(); // domain_authority, page_authority, etc.
        }

        Log::error('RapidAPI fetch failed for ' . $domain . ': ' . $response->body());
        return [];
    } catch (\Exception $e) {
        Log::error('RapidAPI fetch error: ' . $e->getMessage());
        return [];
    }
}


}
