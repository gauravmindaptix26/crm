<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Candidate;
use App\Models\User;

use App\Models\Department;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;



class CandidateController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->hasAnyRole(['HR', 'Admin'])) {
            return redirect()->route('dashboard');
        }
    
        $query = Candidate::query();
    
        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('phone_number', 'like', "%{$searchTerm}%");
            });
        }
    
        // Apply department filter
        if ($request->filled('filter_department')) {
            $query->where('department_id', $request->input('filter_department'));
        }
    
        // Apply status filter
        if ($request->filled('filter_status')) {
            $query->where('status', $request->input('filter_status'));
        }
    
        // Apply added_by filter
        if ($request->filled('filter_added_by')) {
            $query->where('added_by', $request->input('filter_added_by'));
        }
    
        // Dynamic pagination based on entries_per_page
        $perPage = filter_var($request->input('entries_per_page', 10), FILTER_VALIDATE_INT, [
            'options' => ['default' => 10, 'min_range' => 1]
        ]);
    
        // Log query for debugging
        \Log::info('Candidates Query', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'page' => $request->input('page', 1),
            'perPage' => $perPage,
            'total' => $query->count(),
            'request_params' => $request->all()
        ]);
    
        // Fetch candidates with pagination, preserving query parameters
        $candidates = $query->with(['department', 'addedBy'])->paginate($perPage)->appends($request->query());
        $candidates->setPath(route('candidates.index'));
    
        // Log paginated results
        \Log::info('Paginated Results', [
            'current_page' => $candidates->currentPage(),
            'total' => $candidates->total(),
            'per_page' => $candidates->perPage(),
            'data_count' => count($candidates->items())
        ]);
    
        // Fetch departments and users
        $departments = Department::all();
        $users = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['HR', 'Admin']);
        })->get();
    
        // Handle AJAX requests
        if ($request->ajax()) {
            return response()->view('candidates.index', compact('candidates', 'departments', 'users'));
        }
    
        return view('candidates.index', compact('candidates', 'departments', 'users'));
    }
    

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:candidates,email',
           'phone_number' => 'required|digits:10',
            'experience' => 'required|string|max:255',
            'current_salary' => 'nullable|string|min:0|max:99999999.99',
            'expected_salary' => 'nullable|string|min:0|max:99999999.99',
            'offered_salary' => 'nullable|string|min:0|max:99999999.99',
            'date_of_joining' => 'nullable|date',
            'comments' => 'nullable|string|max:1000',
            'resume' => 'nullable|file',
            'department_id' => 'required',
            'status' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        $data = $request->except(['resume']);
        
        if ($request->hasFile('resume')) {
            $data['resume'] = $request->file('resume')->store('resumes', 'public');
        }
    
        $data['added_by'] = auth()->id();
    
        Candidate::create($data);
    
        return response()->json(['message' => 'Candidate added successfully!'], 201);
    }


    public function edit($id)
    {
        $candidate = Candidate::findOrFail($id);
        return response()->json($candidate);
    }

    public function update(Request $request, $id)
    {
        $candidate = Candidate::findOrFail($id);
    
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:candidates,email,' . $id,
            'phone_number' => 'nullable|digits:10',
            'experience' => 'nullable',
            'current_salary' => 'nullable',
            'expected_salary' => 'nullable',
            'offered_salary' => 'nullable',
            'date_of_joining' => 'nullable|date',
            'department_id' => 'nullable',
            'status' => 'required|string',
            'comments' => 'nullable|string',
            'resume' => 'nullable|file',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        $data = $request->except(['resume']);
    
        if ($request->hasFile('resume')) {
            $data['resume'] = $request->file('resume')->store('resumes', 'public');
        }
    
        $updated = $candidate->update($data);
    
        if ($updated) {
            return response()->json(['message' => 'Candidate updated successfully!']);
        } else {
            return response()->json(['error' => 'Failed to update candidate'], 500);
        }
    }

    public function destroy(Candidate $candidate)
{
    if ($candidate->resume) {
        Storage::delete($candidate->resume); // Deletes the stored file
    }

    $candidate->delete();

    return response()->json(['success' => 'Candidate deleted successfully']);
}
public function show(Candidate $candidate)
{
    return response()->json($candidate);
}

 
}    