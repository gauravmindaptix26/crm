<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\HrNote;


class HrNoteController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required',
            'title' => 'required|string|max:255',
            'note_type' => 'required|string|max:255', // Now required
            'rating' => 'nullable|integer|between:1,5',
            'description' => 'nullable|string',
        ]);
    
        $validated['added_by'] = Auth::id();
    
        HrNote::create($validated);
    
        return redirect()->back()->with('success', 'HR Note added successfully.');
    }
    
}
