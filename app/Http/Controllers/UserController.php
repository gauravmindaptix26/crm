<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\Dsr;
use Illuminate\Validation\Rule;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Traits\HasRoles; // Ensure this is in your User model
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;



class UserController extends Controller
{
    public function index(Request $request)
    {
        $loggedInUser = Auth::user();
        if (!$loggedInUser->hasAnyRole(['Admin', 'HR', 'Project Manager'])) {
            return redirect()->route('dashboard');
        }
    
        // Initialize query
        $query = User::with('roles', 'department', 'reportingPerson');
    
        // Apply role-based filtering
        if ($loggedInUser->hasAnyRole(['Admin', 'HR','Sales Team Manager'])) {
            // Admin and HR can see all users
        } else {
            // Project Managers see only their direct reports
            $query->where('reporting_person', $loggedInUser->id);
        }
    
        // Search query
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }
    
        // Filter by role
        if ($request->filled('filter_role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->input('filter_role'));
            });
        }
    
        // Filter by department
        if ($request->filled('filter_department')) {
            $query->where('department_id', $request->input('filter_department'));
        }
    
        // Filter by reporting person
        if ($request->filled('filter_reporting_person')) {
            $query->where('reporting_person', $request->input('filter_reporting_person'));
        }
    
        // Entries per page
        $perPage = $request->input('entries_per_page', 10);
        $users = $query->paginate($perPage)->appends($request->query());
    
        $allUsersForDropdown = User::all();
        $departments = Department::all();
    
        return view('users.index', compact('users', 'departments', 'allUsersForDropdown'));
    }
    

    public function generateEmployeeCode()
    {
        $latestUser = User::latest()->first();
        $nextNumber = $latestUser ? ((int) substr($latestUser->employee_code, -3)) + 1 : 1;
        $generatedCode = 'SEO-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    
        return response()->json(['code' => $generatedCode]);
    }
    


    public function store(Request $request)
    {
        // Define validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'nullable|digits:10',
            'monthly_salary' => 'nullable|numeric|min:0',
            'employee_code' => [
                $request->role !== 'Admin' ? 'required' : 'nullable',
                'string',
                'max:20',
                Rule::unique('users', 'employee_code'),
            ],
            'image' => 'nullable|image|mimes:jpg,png,jpeg,gif|max:2048',
            'role' => 'required|string|exists:roles,name',
            'password' => 'nullable|string|min:8|confirmed', // Added password validation with confirmation
            'monthly_target' => 'nullable|numeric|min:0',
            'upsell_incentive' => 'nullable|numeric',
            'department' => 'required|exists:departments,id',
            'reporting_person' => 'nullable|exists:users,id',
           'experience' => 'nullable|string|max:255',
            'qualification' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'date_of_joining' => 'nullable|date',
            'allow_all_projects' => 'boolean',
            'disable_login' => 'boolean',
        ];
        
    
        $validator = Validator::make($request->all(), $rules);
    
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }
    
        // Generate employee code if not provided
        $generatedCode = $request->filled('employee_code') 
            ? $request->employee_code 
            : 'EMP-' . str_pad(
                (User::latest()->first()?->employee_code 
                    ? (int) substr(User::latest()->first()->employee_code, -3) + 1 
                    : 1), 
                3, '0', STR_PAD_LEFT
            );
    
        // Remove commas from monthly_target
        $monthlyTarget = $request->monthly_target 
            ? str_replace(',', '', $request->monthly_target) 
            : null;
    
        // Handle image upload
        $imagePath = $request->file('image') 
            ? $request->file('image')->store('images', 'public') 
            : null;
    
        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'monthly_target' => $monthlyTarget,
            'upsell_incentive' => $request->upsell_incentive,
            'department_id' => $request->department,
            'reporting_person' => $request->reporting_person,
            'allow_all_projects' => $request->boolean('allow_all_projects'),
            'disable_login' => $request->boolean('disable_login'),
            'experience' => $request->experience,
            'qualification' => $request->qualification,
            'specialization' => $request->specialization,
            'date_of_joining' => $request->date_of_joining,
            'password' => $request->password ? Hash::make($request->password) : null,
            'employee_code' => $generatedCode,
            'image' => $imagePath,
            'monthly_salary' => $request->monthly_salary,
        ]);
    
        // Assign role
        if ($request->role) {
            $user->assignRole($request->role);
        }
    
        return response()->json(['success' => 'User created successfully'], 201);
    }
    public function edit(User $user)
    {
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'monthly_target' => $user->monthly_target,
            'upsell_incentive' => $user->upsell_incentive,
            'reporting_person' => $user->reporting_person,
            'department' => $user->department_id, // Ensure ID is returned, not object
            'allow_view_all_projects' => $user->allow_all_projects,
            'disable_login' => $user->disable_login,
            'experience' => $user->experience,
            'qualification' => $user->qualification,
            'specialization' => $user->specialization,
            'date_of_joining' => $user->date_of_joining,
            'employee_code' => $user->employee_code,
            'monthly_salary' => $user->monthly_salary,

            'image' => $user->image ? asset('storage/' . $user->image) : null,
            'role' => $user->roles->pluck('name')->first(), // Get the first assigned role
        ]);
    }
    
    
    public function update(Request $request, User $user)
{
    $rules = [
        'name' => 'required|string|max:255',
        'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
        'phone_number' => 'nullable|string|max:20',
        'monthly_salary' => 'nullable|numeric|min:0',
       'employee_code' => [
    'nullable',
    'string',
    'max:20',
    Rule::unique('users', 'employee_code')->ignore($user->id),
],

        'image' => 'nullable|image|mimes:jpg,png,jpeg,gif|max:2048',
        'role' => 'required|string|exists:roles,name',
        'password' => 'nullable|string|min:8|confirmed',
        'monthly_target' => 'nullable|numeric|min:0',
        'upsell_incentive' => 'nullable|numeric',
        'department' => 'nullable|exists:departments,id',
        'reporting_person' => 'nullable|exists:users,id',
       'experience' => 'nullable|string|max:255',
        'qualification' => 'nullable|string|max:255',
        'specialization' => 'nullable|string|max:255',
        'date_of_joining' => 'nullable|date',
        'allow_all_projects' => 'boolean',
        'disable_login' => 'boolean',
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
        return response()->json([
            'errors' => $validator->errors(),
        ], 422);
    }

    // Handle image upload
    $imagePath = $request->file('image') 
        ? $request->file('image')->store('images', 'public') 
        : $user->image;

    // Remove commas from monthly_target
    $monthlyTarget = $request->monthly_target 
        ? str_replace(',', '', $request->monthly_target) 
        : $user->monthly_target;

    // Update user
    $user->update([
        'name' => $request->name,
        'email' => $request->email,
        'phone_number' => $request->phone_number,
        'monthly_target' => $monthlyTarget,
        'upsell_incentive' => $request->upsell_incentive,
        'department_id' => $request->department,
        'reporting_person' => $request->reporting_person,
        'allow_all_projects' => $request->boolean('allow_all_projects'),
        'disable_login' => $request->boolean('disable_login'),
        'experience' => $request->experience,
        'qualification' => $request->qualification,
        'specialization' => $request->specialization,
        'date_of_joining' => $request->date_of_joining,
        'password' => $request->password ? Hash::make($request->password) : $user->password,
        'employee_code' => $request->employee_code ?? $user->employee_code,
        'image' => $imagePath,
        'monthly_salary' => $request->monthly_salary,
    ]);

    // Sync roles
    if ($request->role) {
        $user->syncRoles($request->role);
    }

    return response()->json(['success' => 'User updated successfully'], 200);
}
    

    public function destroy(User $user)
    {
        if ($user->image) {
            Storage::delete($user->image);
        }
        $user->delete();

        return response()->json(['success' => 'User deleted successfully']);
    }
    public function filterUsers(Request $request)
    {
        $loggedInUser = Auth::user();
   
        // Initialize query
        $usersQuery = User::with('roles', 'department', 'reportingPerson');
   
        // Apply role-based filtering
        if (!$loggedInUser->hasAnyRole(['Admin', 'HR', 'Sales Team Manager'])) {
            $usersQuery->where('reporting_person', $loggedInUser->id);
        }
   
        // Apply general search filter
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $usersQuery->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('phone_number', 'like', "%{$searchTerm}%")
                  ->orWhere('employee_code', 'like', "%{$searchTerm}%");
            });
        }
   
        // Filter by role
        if ($request->filled('filter_role')) {
            $usersQuery->whereHas('roles', function ($query) use ($request) {
                $query->where('name', $request->input('filter_role'));
            });
        }
   
        // Filter by department
        if ($request->filled('filter_department')) {
            $usersQuery->where('department_id', $request->input('filter_department'));
        }
   
        // Filter by reporting person
        if ($request->filled('filter_reporting_person')) {
            $usersQuery->where('reporting_person', $request->input('filter_reporting_person'));
        }
   
        // Entries per page
        $perPage = $request->input('entries_per_page', 10);
   
        // Validate page parameter
        $page = filter_var($request->input('page', 1), FILTER_VALIDATE_INT, [
            'options' => ['default' => 1, 'min_range' => 1]
        ]);
   
        // Debug the query
        \Log::info('FilterUsers Query', [
            'sql' => $usersQuery->toSql(),
            'bindings' => $usersQuery->getBindings(),
            'page' => $page,
            'perPage' => $perPage,
            'total' => $usersQuery->count()
        ]);
   
        $users = $usersQuery->paginate($perPage, ['*'], 'page', $page)->appends($request->query());
   
        // Debug paginated results
        \Log::info('Paginated Results', [
            'current_page' => $users->currentPage(),
            'total' => $users->total(),
            'per_page' => $users->perPage(),
            'data_count' => count($users->items())
        ]);
   
        if ($request->ajax()) {
            return response()->json([
                'html' => view('users.users_rows', compact('users'))->render(),
                'pagination' => $users->links()->toHtml(),
            ]);
        }
   
        // For non-AJAX requests, return the index view
        $allUsersForDropdown = User::select('id', 'name')->get();
        $departments = Department::all();
        return view('users.index', compact('users', 'departments', 'allUsersForDropdown'));
    }

    
    
    
    public function show($id)
    {
        return response()->json(['message' => 'Show method not implemented'], 404);
    }
     
    public function shows($id)
    {
        $user = User::with([
            'roles', 'projects', 'userNotes.addedBy', 'hrNotes.addedBy'
        ])->findOrFail($id);
    
        $role = $user->roles->pluck('name')->first();
    
        $dsrs = Dsr::with('project', 'helper')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();
    
        // Calculate Ratings and Counts
        $avgUserRating = $user->userNotes->whereNotNull('rating')->avg('rating') ?? 0;
        $avgHrRating = $user->hrNotes->whereNotNull('rating')->avg('rating') ?? 0;
    
        $fineCount = $user->hrNotes->where('note_type', 'No of Fine')->count();
        $appreciationCount = $user->hrNotes->where('note_type', 'Appreciation')->count();
    
        if ($role === 'Employee') {
            $projects = $user->projects;
    
            $stats = [
                'all' => $projects->count(),
                'working' => $projects->where('project_status', 'Working')->count(),
                'complete' => $projects->where('project_status', 'Complete')->count(),
                'pause' => $projects->where('project_status', 'Paused')->count(),
                'issue' => $projects->where('project_status', 'Issues')->count(),
                'temp_hold' => $projects->where('project_status', 'Temp Hold')->count(),
            ];
    
            return view('users.show', compact('user', 'role', 'stats', 'dsrs', 'avgUserRating', 'avgHrRating', 'fineCount', 'appreciationCount'));
        }
    
        return view('users.show', compact('user', 'role', 'dsrs', 'avgUserRating', 'avgHrRating', 'fineCount', 'appreciationCount'));
    }
    
}
