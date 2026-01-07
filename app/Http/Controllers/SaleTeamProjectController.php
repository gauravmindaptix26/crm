<?php

namespace App\Http\Controllers;

use App\Models\SaleTeamProject;
use App\Models\Country;
use App\Models\Department;
use App\Models\HiredFrom;
use App\Models\User;
use App\Models\SalesProjectAttachment;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;

class SaleTeamProjectController extends Controller
{
    public function index(Request $request)
    {
        $loggedInUser = auth()->user();
    
        // Redirect Employees or HR to dashboard
        if ($loggedInUser->hasRole('Employee') || $loggedInUser->hasRole('HR')) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
        }
    
        $user = $loggedInUser;
    
        // Initialize query
        $query = SaleTeamProject::with(['country', 'department', 'salesPerson', 'hiredFromProfile'])->latest();
    
        // If not admin, only show projects added by the logged-in sales person
        if (!($user->hasRole('Admin') || $user->hasRole('Project Manager'))) {
            $query->where('sales_person_id', $user->id);
        }
    
        // Apply department filter if present
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
    
        // Apply global search if search query is present
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('client_name', 'like', "%{$searchTerm}%")
                  ->orWhere('client_email', 'like', "%{$searchTerm}%")
                  ->orWhere('name_or_url', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('project_type', 'like', "%{$searchTerm}%")
                  ->orWhere('client_type', 'like', "%{$searchTerm}%")
                  ->orWhere('business_type', 'like', "%{$searchTerm}%")
                  ->orWhere('specific_keywords', 'like', "%{$searchTerm}%")
                  ->orWhere('client_behaviour', 'like', "%{$searchTerm}%")
                  ->orWhere('communication_details', 'like', "%{$searchTerm}%");
            });
        }
    
        // Handle entries per page
        $perPage = $request->input('per_page', 10); // Default to 10 if not specified
        $projects = $query->paginate($perPage)->withQueryString();
    
        $countries = Country::all();
        $departments = Department::all();
        $hiredFroms = HiredFrom::all();
        $salesPersons = User::role(['Sales Team', 'Sales Team Manager'])->get();
    
        return view('sale-team-projects.index', compact('projects', 'countries', 'departments', 'hiredFroms', 'salesPersons'));
    }
    public function edit($id)
    {
        $project = SaleTeamProject::findOrFail($id);
       // dd($project);
        return response()->json($project);
    }
    
    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'hired_from_portal' => 'nullable|in:PPH,Upwork,Fiver',
        'hired_from_profile_id' => 'exists:hired_froms,id',
        'name_or_url' => 'required|string',
        'description' => 'nullable|string',
        'price_usd' => 'nullable|numeric',
        'time_to_contact' => 'nullable|string',
        'specific_keywords' => 'nullable|string',
        'result_commitment' => 'nullable|string',
        'project_type' => 'required|in:Ongoing,One-time',
        'client_type' => 'required|in:new client,old client',
        'business_type' => 'required|in:Startup,Small,Midlevel,Enterprise',
        'project_month' => 'nullable|date_format:Y-m-d',
        'country_id' => 'required|exists:countries,id',
        'sales_person_id' => 'required|exists:users,id',
        'department_id' => 'required|exists:departments,id',
        'client_name' => 'required|string',
        'client_email' => 'nullable|email',
        'client_contact_time' => 'nullable|string',
        'client_other_info' => 'nullable|string',
        'client_behaviour' => 'nullable|string',
        'communication_details' => 'nullable|string',
        'client_target_keyword' => 'nullable|string',
        'commitment_for_results' => 'nullable|string',
        'website_speed_included' => 'required|in:Yes,No',
        'website_dev_commitment' => 'nullable|string',
        'internal_explainer_video' => 'nullable|string',
        'content_commitment' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $data = $request->only([
        'hired_from_portal',
        'hired_from_profile_id',
        'name_or_url',
        'description',
        'price_usd',
        'time_to_contact',
        'specific_keywords',
        'result_commitment',
        'project_type',
        'client_type',
        'business_type',
        'project_month',
        'country_id',
        'sales_person_id',
        'department_id',
        'client_name',
        'client_email',
        'client_contact_time',
        'client_other_info',
        'client_behaviour',
        'communication_details',
        'client_target_keyword',
        'commitment_for_results',
        'website_speed_included',
        'website_dev_commitment',
        'internal_explainer_video',
        'content_commitment',
    ]);

    $saleTeamProject = SaleTeamProject::create($data);

    return response()->json(['message' => 'Project created successfully', 'project' => $saleTeamProject], 201);
}
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hired_from_portal' => 'nullable|in:PPH,Upwork,Fiver',
            'hired_from_profile_id' => 'exists:hired_froms,id',
            'name_or_url' => 'string',
            'description' => 'nullable|string',
            'price_usd' => 'nullable|numeric',
            'time_to_contact' => 'nullable|string',
            'specific_keywords' => 'nullable|string',
            'result_commitment' => 'nullable|string',
            'project_type' => 'in:Ongoing,One-time',
            'client_type' => 'in:new client,old client',
            'business_type' => 'in:Startup,Small,Midlevel,Enterprise',
            'project_month' => 'nullable|date_format:Y-m-d',
            'country_id' => 'exists:countries,id',
            'sales_person_id' => 'exists:users,id',
            'department_id' => 'exists:departments,id',
            'client_name' => 'string',
            'client_email' => 'nullable|email',
            'client_contact_time' => 'nullable|string',
            'client_other_info' => 'nullable|string',
            'client_behaviour' => 'nullable|string',
            'communication_details' => 'nullable|string',
            'client_target_keyword' => 'nullable|string',
            'commitment_for_results' => 'nullable|string',
            'website_speed_included' => 'in:Yes,No',
            'website_dev_commitment' => 'nullable|string',
            'internal_explainer_video' => 'nullable|string',
            'content_commitment' => 'nullable|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        $saleTeamProject = SaleTeamProject::findOrFail($request->project_id);
    
        $data = $request->only([
            'hired_from_portal',
            'hired_from_profile_id',
            'name_or_url',
            'description',
            'price_usd',
            'time_to_contact',
            'specific_keywords',
            'result_commitment',
            'project_type',
            'client_type',
            'business_type',
            'project_month',
            'country_id',
            'sales_person_id',
            'department_id',
            'client_name',
            'client_email',
            'client_contact_time',
            'client_other_info',
            'client_behaviour',
            'communication_details',
            'client_target_keyword',
            'commitment_for_results',
            'website_speed_included',
            'website_dev_commitment',
            'internal_explainer_video',
            'content_commitment',
        ]);
    
        $saleTeamProject->update($data);
    
        return response()->json(['message' => 'Project updated successfully', 'project' => $saleTeamProject]);
    }
    

    public function destroy(SaleTeamProject $saleTeamProject)
    {
        $saleTeamProject->delete();
        return response()->json(['message' => 'Project deleted successfully']);
    }
}

