<?php


namespace App\Http\Controllers;

use App\Models\ProjectCategory;
use Illuminate\Http\Request;

class ProjectDirectoryController extends Controller
{
    public function index()
    {
        $categories = ProjectCategory::with('parent:id,name', 'creator:id,name')->paginate(10);
        $allCategories = ProjectCategory::all();
    
        return view('project_categories.index', compact('categories', 'allCategories'));
    }

  public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'parent_id' => 'nullable|exists:project_categories,id',
    ]);

    ProjectCategory::create([
        'name' => $request->name,
        'parent_id' => $request->parent_id ? (int) $request->parent_id : null, // Convert to integer
        'created_by' => auth()->id(),
    ]);

    return response()->json(['success' => 'Category added successfully!']);
}


    public function edit($id)
    {
        $category = ProjectCategory::with('parent')->find($id);
    
        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }
    
        return response()->json([
            'id' => $category->id,
            'name' => $category->name,
            'parent_id' => $category->parent_id
        ]);
    }
    

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:project_categories,id',
        ]);
    
        $projectCategory = ProjectCategory::find($id);
        
        if (!$projectCategory) {
            return response()->json(['error' => 'Category not found'], 404);
        }
    
        $projectCategory->update([
            'name' => $request->name,
            'parent_id' => $request->parent_id ?? null,
        ]);
    
        return response()->json(['success' => 'Category updated successfully!']);
    }
    
    public function destroy(ProjectCategory $projectCategory)
    {
        if ($projectCategory->children()->count() > 0) {
            return response()->json(['error' => 'Cannot delete category with subcategories!'], 400);
        }

        $projectCategory->delete();
        return response()->json(['success' => 'Category deleted successfully!']);
    }
}
