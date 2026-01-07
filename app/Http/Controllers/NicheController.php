<?php

namespace App\Http\Controllers;

use App\Models\Niche;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NicheController extends Controller
{
    public function index()
    {
        $niches = Niche::with('user')->paginate(10);
        return view('niches.index', compact('niches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $validated['added_by'] = Auth::id();

        $niche = Niche::create($validated);

        return response()->json(['success' => 'Niche added successfully!', 'niche' => $niche]);
    }

    public function edit(Niche $niche)
    {
        return response()->json($niche);
    }

    public function update(Request $request, Niche $niche)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $niche->update($validated);

        return response()->json(['success' => 'Niche updated successfully']);
    }

    public function destroy($id)
    {
        $niche = Niche::findOrFail($id);
        $niche->delete();

        return response()->json(['success' => 'Niche deleted successfully']);
    }
}

