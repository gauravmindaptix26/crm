<?php

namespace App\Http\Controllers;

use App\Models\AllRnd;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AllRndController extends Controller
{
    public function index()
    {
        $allRnds = AllRnd::with(['department', 'createdBy'])->latest()->paginate(10);
        $departments = Department::all();
        return view('all_rnds.index', compact('allRnds', 'departments'));
    }

    public function store(Request $request)
    {
        // Validate the incoming data
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'urls' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',  // Assuming you have a departments table
            'attachment' => 'nullable|file|mimes:jpg,png,pdf,docx|max:10240', // Allowing file attachments
        ]);

        // Handle file upload if it exists
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('rnd_attachments', 'public');
        }

        // Create a new RND entry
        $rnd = new AllRnd();
        $rnd->title = $validated['title'];
        $rnd->description = $validated['description'];
        $rnd->urls = $validated['urls'];
        $rnd->department_id = $validated['department_id'];
        $rnd->attachment = $attachmentPath;
        $rnd->created_by = auth()->id(); // Assuming the user is authenticated
        $rnd->save();

        // Return success response
        return response()->json(['success' => 'RND entry created successfully.']);
    }
    public function edit(AllRnd $allRnd)
    {
        return response()->json($allRnd);
    }

    public function update(Request $request, AllRnd $allRnd)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'urls' => 'nullable|url',
            'department_id' => 'nullable|exists:departments,id',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);
    
        // Handle attachment if it's provided
        if ($request->hasFile('attachment')) {
            // Delete the old file if it exists
            if ($allRnd->attachment && Storage::disk('public')->exists($allRnd->attachment)) {
                Storage::disk('public')->delete($allRnd->attachment);
            }
    
            // Store the new file
            $validated['attachment'] = $request->file('attachment')->store('attachments', 'public');
        }
    
        // Update the record
        $allRnd->update($validated);
    
        // Return a JSON response
        return response()->json([
            'success' => 'R&D record updated successfully.',
            'data' => $allRnd // Optionally include the updated data to use in the frontend if needed
        ]);
    }
    
    

    public function destroy(AllRnd $allRnd)
    {
        // Check if the attachment exists and delete it
        if ($allRnd->attachment && Storage::disk('public')->exists($allRnd->attachment)) {
            Storage::disk('public')->delete($allRnd->attachment);
        }
    
        // Delete the record
        $allRnd->delete();
    
        // Return a JSON response
        return response()->json([
            'success' => 'R&D record deleted successfully.'
        ]);
    }
    
}



