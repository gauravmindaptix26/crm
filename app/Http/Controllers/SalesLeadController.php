<?php

namespace App\Http\Controllers;

use App\Models\SalesLead;
use App\Models\Country;
use App\Models\Department;
use App\Models\SalesLeadNote;

use App\Models\HiredFrom;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class SalesLeadController extends Controller
{
    public function index(Request $request)
    {
        $loggedInUser = auth()->user();
    
        if ($loggedInUser->hasRole('Employee')) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
        }
    
        $user = Auth::user();
    
        $query = SalesLead::with(['country', 'department', 'salesPerson'])->latest();
    
        // Apply filters
        if ($user->hasRole('Sales Team')) {
            $query->where('sales_person_id', $user->id);
        }
    
        if ($request->sales_person_id) {
            $query->where('sales_person_id', $request->sales_person_id);
        }
    
        if ($request->lead_from_id) {
            $query->where('lead_from_id', $request->lead_from_id);
        }
    
        if ($request->client_type) {
            $query->where('client_type', $request->client_type);
        }
    
        // 👇 Clone BEFORE pagination
        $statusQuery = clone $query;
        $hiredCount = (clone $statusQuery)->where('status', 'Hired')->count();
        $bidCount = (clone $statusQuery)->where('status', 'Bid')->count();
        $goodBidCount = (clone $statusQuery)->where('status', 'Bid')->where('client_type', 'Premium')->count();
        

    
        // 👇 Only paginate after cloning
        $salesLeads = $query->paginate(10);
    
        // Load other data
        $countries = Country::all();
        $departments = Department::all();
        $leadFroms = HiredFrom::all();
        $salesPersons = User::whereHas('roles', fn($q) => $q->where('name', 'Sales Team'))->get();
    
        return view('sales_leads.index', compact(
            'salesLeads',
            'countries',
            'departments',
            'leadFroms',
            'salesPersons',
            'hiredCount',
            'bidCount',
            'goodBidCount'
        ));
    }
    
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email',
            'client_phone' => 'required|string',
            'job_title' => 'nullable|string',
            'description' => 'nullable|string',
            'job_url' => 'nullable|url',
            'client_type' => 'required|string|in:Reseller,Premium,General',
            'lead_from_id' => 'nullable|exists:hired_froms,id',
            'country_id' => 'nullable|exists:countries,id',
            'department_id' => 'required|exists:departments,id',
            'sales_person_id' => 'required|exists:users,id',
        ]);

        $salesLead = SalesLead::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Sales Lead added successfully.',
            'salesLead' => $salesLead
        ], 201);
    }

    public function edit(SalesLead $salesLead)
    {
        return response()->json($salesLead);
    }

    public function update(Request $request, SalesLead $salesLead)
    {
        $validated = $request->validate([
            'client_name' => 'string|max:255',
            'client_email' => 'email',
            'client_phone' => 'string',
            'job_title' => 'string',
            'description' => 'nullable|string',
            'job_url' => 'nullable|url',
            'client_type' => 'string|in:Reseller,Premium,General',
            'lead_from_id' => 'nullable|exists:hired_froms,id',
            'country_id' => 'nullable|exists:countries,id',
            'department_id' => 'exists:departments,id',
            'sales_person_id' => 'exists:users,id',
        ]);

        $salesLead->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Sales Lead updated successfully.',
            'salesLead' => $salesLead
        ]);
    }

    public function destroy(SalesLead $salesLead)
    {
        $salesLead->delete();
    
        return response()->json([
            'success' => true,
            'message' => 'Sales Lead deleted successfully.'
        ]);
    }
    

    public function updateStatus(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'lead_id' => 'required|exists:sales_leads,id',
            'status' => 'required|string',
            'date' => 'nullable|date',
            'reason' => 'nullable|string',
        ]);
    
        // Find the lead by ID
        $lead = SalesLead::findOrFail($request->lead_id);
    
        // Update the lead status and reason
        $lead->status = $request->status;
        $lead->status_update_date = $request->date;
        $lead->status_reason = $request->reason;
        
        // Save the changes to the database
        $lead->save();
    
        // Return response in JSON format to update the front end
        return response()->json([
            'success' => true,
            'message' => 'Lead status updated successfully.',
            'lead' => $lead
        ]);
    }
    public function allSalesLeads(Request $request)
    {
        $user = Auth::user();
    
        $query = SalesLead::with(['country', 'department', 'salesPerson', 'leadFrom']);
    
        // Detect if any filters except 'status' are applied
        $hasFiltersExceptStatus = $request->filled('sales_person_id') || 
                                  $request->filled('hired_from_id') || 
                                  $request->filled('client_type');
    
        // Detect if status filter is applied
        $hasStatusFilter = $request->filled('status');
    
        // If no filters at all, force status = 'Hired'
        if (!$hasFiltersExceptStatus && !$hasStatusFilter) {
            $request->merge(['status' => 'Hired']);
        }
    
        // Force status = 'Bid' if coming from Bids card (using force_status)
        if ($request->filled('force_status') && $request->force_status === 'Bid') {
            $request->merge(['status' => 'Bid']);
        }
    
        // Apply filters
        if ($user->hasRole('Sales Team')) {
            $query->where('sales_person_id', $user->id);
        } elseif ($request->filled('sales_person_id')) {
            $query->where('sales_person_id', $request->sales_person_id);
        }
    
        if ($request->filled('hired_from_id')) {
            $query->where('lead_from_id', $request->hired_from_id);
        }
    
        if ($request->filled('client_type')) {
            $query->where('client_type', $request->client_type);
        }
    
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    
        $salesLeads = $query->paginate(10);
    
        $countries = Country::all();
        $departments = Department::all();
        $leadFroms = HiredFrom::all();
        $salesPersons = User::role('Sales Team')->get();
    
        $statusCountsQuery = SalesLead::query();
        if ($user->hasRole('Sales Team')) {
            $statusCountsQuery->where('sales_person_id', $user->id);
        }
    
        $hiredCount = (clone $statusCountsQuery)->where('status', 'Hired')->count();
        $bidCount   = (clone $statusCountsQuery)->where('status', 'Bid')->count();
    
        return view('sales_leads.all', compact(
            'salesLeads',
            'countries',
            'departments',
            'leadFroms',
            'salesPersons',
            'hiredCount',
            'bidCount'
        ));
    }
    
    
public function show($id)
{
    $lead = SalesLead::with(['user', 'department', 'country', 'notes'])->findOrFail($id);
    return view('sales_leads.show', compact('lead'));
}
public function addNote(Request $request, $id)
{
    $request->validate([
        'note_type' => 'required|in:Follow up,General',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'attachment' => 'nullable|file|max:2048'
    ]);

    $note = new SalesLeadNote();
    $note->sales_lead_id = $id;
    $note->note_type = $request->note_type;
    $note->title = $request->title;
    $note->description = $request->description;
    $note->added_by = auth()->id();

    if ($request->hasFile('attachment')) {
        $note->attachment = $request->file('attachment')->store('notes', 'public');
    }

    $note->save();

    return back()->with('success', 'Note added successfully.');
}
}

