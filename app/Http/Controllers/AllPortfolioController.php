<?php

namespace App\Http\Controllers;

use App\Models\AllPortfolio;
use App\Models\Country;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AllPortfolioController extends Controller
{
    public function index(Request $request)
    {
        $loggedInUser = auth()->user();

        // Redirect Employees to dashboard
        if ($loggedInUser->hasRole('Employee')) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
        }
    

        if (auth()->user()->hasRole('HR')) {
            return redirect()->route('dashboard')->with('error', 'Access denied.');
        }
        $query = AllPortfolio::with(['country', 'department', 'creator']);
    
        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }
    
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
    
        $portfolios = $query->paginate(10);
        $countries = Country::all();
        $departments = Department::all();
    
        return view('all_portfolios.index', compact('portfolios', 'countries', 'departments'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'department_id' => 'required|exists:departments,id',
            'description' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf,doc,docx,xls,xlsx'
        ]);

        if ($request->hasFile('attachment')) {
            $validated['attachment'] = $request->file('attachment')->store('portfolios', 'public');
        }

        $validated['created_by'] = Auth::id();

        $portfolio = AllPortfolio::create($validated);

        return response()->json(['success' => 'Portfolio added successfully!', 'portfolio' => $portfolio]);
    }

    public function edit(AllPortfolio $all_portfolio)
    {
        return response()->json($all_portfolio);
    }

    public function update(Request $request, AllPortfolio $all_portfolio)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'department_id' => 'required|exists:departments,id',
            'description' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf,doc,docx,xls,xlsx'
        ]);

        if ($request->hasFile('attachment')) {
            $validated['attachment'] = $request->file('attachment')->store('portfolios', 'public');
        }

        $all_portfolio->update($validated);

        return response()->json(['success' => 'Portfolio updated successfully']);
    }

    public function destroy($id)
    {
        $portfolio = AllPortfolio::findOrFail($id);
        $portfolio->delete();
        return response()->json(['success' => 'Portfolio deleted successfully']);
    }
}

