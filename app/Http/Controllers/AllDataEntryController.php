<?php

namespace App\Http\Controllers;
use App\Models\AllDataEntry;
use Illuminate\Support\Facades\Auth;


use Illuminate\Http\Request;

class AllDataEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $entries = AllDataEntry::latest()->paginate(10);
        return view('all-data-entries.index', compact('entries'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string',
            'phone_number' => 'nullable|string|max:20',
            'data_option' => 'string'
        ]);
    
        $validated['created_by'] = Auth::id();
        AllDataEntry::create($validated);
    
        return response()->json(['success' => true, 'message' => 'Entry added successfully.']);
    }
    
    public function edit($id)
    {
        return response()->json(AllDataEntry::findOrFail($id));
    }
    
    public function update(Request $request, $id)
    {
        $entry = AllDataEntry::findOrFail($id);
    
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'email' => 'nullable|string',

            'phone_number' => 'nullable|string|max:20',
            'data_option' => 'string'
        ]);
    
        $entry->update($validated);
    
        return response()->json(['success' => true, 'message' => 'Entry updated successfully.']);
    }
    
    public function destroy($id)
    {
        AllDataEntry::destroy($id);
        return response()->json(['success' => 'Entry deleted successfully.']);
    }
}
