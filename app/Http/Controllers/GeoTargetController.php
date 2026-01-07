<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GeoTarget;
use App\Models\Country;
use Illuminate\Support\Facades\Auth;

class GeoTargetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $country_id = $request->country_id;
        $country = Country::findOrFail($country_id);

        $geoTargets = GeoTarget::where('country_id', $country_id)->paginate(10);

        return view('geo_targets.index', compact('geoTargets', 'country'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
    
        $country_id = $request->query('country_id'); // Get country_id from URL
    
        if (!$country_id || !Country::find($country_id)) {
            return response()->json(['success' => false, 'message' => 'Invalid country ID.'], 400);
        }
    
        $geoTarget = GeoTarget::create([
            'title' => $request->title,
            'description' => $request->description,
            'country_id' => $country_id, // Use country_id from URL
            'created_by' => Auth::id(),
        ]);
    
        return response()->json(['success' => true, 'message' => 'Geo Target added successfully.', 'geoTarget' => $geoTarget]);
    }
    

    public function edit(GeoTarget $geoTarget)
    {
        return response()->json($geoTarget);
    }

    public function update(Request $request, GeoTarget $geoTarget)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $geoTarget->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return response()->json(['success' => true, 'message' => 'Geo Target updated successfully.']);
    }

    public function destroy(GeoTarget $geoTarget)
    {
        $geoTarget->delete();
        return response()->json(['success' => true, 'message' => 'Geo Target deleted successfully.']);
    }
}
