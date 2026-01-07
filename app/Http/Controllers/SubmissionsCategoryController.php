<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SubmissionCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubmissionsCategoryController extends Controller
{
    public function index()
    {
        $loggedInUser = auth()->user();
        
        if (!$loggedInUser->hasRole('Admin')) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
        }
        $categories = SubmissionCategory::orderBy('created_at', 'desc')->paginate(10);
        return view('submission_categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:submission_categories,slug',
            'description' => 'nullable|string'
        ]);
        SubmissionCategory::create([
            'name' => $request->name,
            'slug' => $request->slug ?: Str::slug($request->name),
            'description' => $request->description
        ]);
        return response()->json(['success' => 'Category added successfully']);
    }

    public function edit(SubmissionCategory $submissionCategory)
    {
        return response()->json(['category' => $submissionCategory]);
    }

    public function update(Request $request, SubmissionCategory $submissionCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:submission_categories,slug,' . $submissionCategory->id,
            'description' => 'nullable|string'
        ]);
        $submissionCategory->update([
            'name' => $request->name,
            'slug' => $request->slug ?: Str::slug($request->name),
            'description' => $request->description
        ]);
        return response()->json(['success' => 'Category updated successfully']);
    }

    // public function destroy(SubmissionCategory $submissionCategory)
    // {
    //     $submissionCategory->delete();
    //     return response()->json(['success' => 'Category deleted successfully']);
    // }

    public function destroy(SubmissionCategory $submissionCategory)
    {
        try {
            $submissionCategory->delete();
            return response()->json(['success' => 'Category deleted successfully']);
        } catch (\Exception $e) {
            \Log::error('Delete category error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete category: ' . $e->getMessage()], 500);
        }
    }
}