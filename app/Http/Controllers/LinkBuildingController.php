<?php

namespace App\Http\Controllers;

use App\Models\LinkBuilding;
use App\Models\Country;
use Illuminate\Http\Request;

class LinkBuildingController extends Controller {
    public function index() {
        $linkBuildings = LinkBuilding::latest()->paginate(10);
        $countries = Country::all();
        return view('link-building.index', compact('linkBuildings', 'countries'));
    }

    public function store(Request $request)
{
    // Decode JSON strings to arrays
    $request->merge([
        'niche' => json_decode($request->niche, true),
        'countries' => json_decode($request->countries, true),
    ]);

    // Validate the request
    $validated = $request->validate([
        'website' => 'required|string',
        'pa' => 'required|integer|min:1|max:90',
        'da' => 'required|integer|min:1|max:90',
        'niche' => 'required|array',
        'niche.*' => 'string',
        'countries' => 'required|array',
        'countries.*' => 'string',
        'type_of_link' => 'required|string',
    ]);

    // Save the entry
    LinkBuilding::create([
        'website' => $validated['website'],
        'pa' => $validated['pa'],
        'da' => $validated['da'],
        'niche' => json_encode($validated['niche']), // Store as JSON
        'countries' => json_encode($validated['countries']), // Store as JSON
        'type_of_link' => $validated['type_of_link'],
    ]);

    return response()->json(['success' => true]);
}

    

    public function edit(LinkBuilding $linkBuilding) {
        return response()->json($linkBuilding);
    }

    public function update(Request $request, LinkBuilding $linkBuilding)
    {
        $request->merge([
            'niche' => json_decode($request->niche, true),
            'countries' => json_decode($request->countries, true),
        ]);
    
        $validated = $request->validate([
            'website' => 'required|string',
            'pa' => 'required|integer|between:1,90',
            'da' => 'required|integer|between:1,90',
            'niche' => 'required|array',
            'countries' => 'required|array',
            'type_of_link' => 'required|string',
        ]);
    
        $linkBuilding->update([
            'website' => $validated['website'],
            'pa' => $validated['pa'],
            'da' => $validated['da'],
            'niche' => json_encode($validated['niche']),
            'countries' => json_encode($validated['countries']),
            'type_of_link' => $validated['type_of_link'],
        ]);
    
        return response()->json(['success' => true, 'message' => 'Link Building Entry Updated!']);
    }
    
    public function destroy(LinkBuilding $linkBuilding) {
        $linkBuilding->delete();
        return response()->json(['success' => true, 'message' => 'Entry Deleted!']);
    }
}
