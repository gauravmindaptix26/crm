<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserNote;


class UserNoteController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'note_type' => 'required|string',
            'rating' => 'nullable|integer|min:1|max:10',
            'description' => 'nullable|string',
        ]);

        UserNote::create([
            'user_id' => $request->user_id,
            'added_by' => auth()->id(),
            'title' => $request->title,
            'note_type' => $request->note_type,
            'rating' => $request->rating,
            'description' => $request->description,
        ]);

        return redirect()->route('users.show', $request->user_id)->with('success', 'Note added successfully.');
    }
}
