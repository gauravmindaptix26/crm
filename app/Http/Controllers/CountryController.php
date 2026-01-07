<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;
use Illuminate\Support\Facades\Auth;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $countries = Country::with('creator')->latest()->paginate(10);
        return view('countries.index', compact('countries'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:countries,name',
        ]);

        Country::create([
            'name' => $request->name,
            'created_by' => Auth::id(),
        ]);

        return response()->json(['success' => 'Country added successfully!']);
    }

    public function edit(Country $country)
    {
        return response()->json($country);
    }

    public function update(Request $request, Country $country)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:countries,name,' . $country->id,
        ]);

        $country->update([
            'name' => $request->name,
        ]);

        return response()->json(['success' => 'Country updated successfully!']);
    }

    public function destroy(Country $country)
    {
        $country->delete();
        return response()->json(['success' => 'Country deleted successfully!']);
    }
}
