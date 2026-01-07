<?php
namespace App\Http\Controllers;

use App\Models\HiredFrom;
use Illuminate\Http\Request;

class HiredFromController extends Controller
{
    public function index()
    {
        $loggedInUser = auth()->user();
    
        // Redirect Employees and HR to dashboard
        if ($loggedInUser->hasRole('Employee') || $loggedInUser->hasRole('HR')) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
        }
    
        $hiredFroms = HiredFrom::paginate(10);
        return view('hired-from.index', compact('hiredFroms'));
    }
    

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $hiredFrom = HiredFrom::create($validated);

        return response()->json(['success' => true, 'message' => 'Hired From added successfully!', 'data' => $hiredFrom]);
    }

    public function edit(HiredFrom $hiredFrom)
    {
        return response()->json($hiredFrom);
    }

    public function update(Request $request, HiredFrom $hiredFrom)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $hiredFrom->update($validated);

        return response()->json(['success' => true, 'message' => 'Hired From updated successfully']);
    }

    public function destroy($id)
    {
        $hiredFrom = HiredFrom::findOrFail($id);
        $hiredFrom->delete();

        return response()->json(['success' => 'Hired From deleted successfully']);
    }
}
