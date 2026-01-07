<?php
namespace App\Http\Controllers;

use App\Models\LeavePolicy;
use Illuminate\Http\Request;

class LeavePolicyController extends Controller {
    public function __construct() {
        // $this->middleware('auth');
        // $this->middleware(function ($request, $next) {
        //     if (!auth()->user()->hasRole('Admin')) {
        //         abort(403, 'Unauthorized');
        //     }
        //     return $next($request);
        // });
    }

    public function index() {
        $policies = LeavePolicy::paginate(10);
        return view('leave_policies.index', compact('policies'));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:leave_policies,name',
            'days_per_quarter' => 'required|integer|min:1',
            'probation_months' => 'nullable|integer|min:0',
        ]);

        $validated['probation_months'] = $validated['probation_months'] ?? 0;

        LeavePolicy::create($validated);

        return response()->json(['success' => 'Leave type created successfully.']);
    }

    public function edit(LeavePolicy $leavePolicy) {
        return response()->json($leavePolicy);
    }

    public function update(Request $request, LeavePolicy $leavePolicy) {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:leave_policies,name,' . $leavePolicy->id,
            'days_per_quarter' => 'required|integer|min:1',
            'probation_months' => 'nullable|integer|min:0',
        ]);

        $validated['probation_months'] = $validated['probation_months'] ?? 0;

        $leavePolicy->update($validated);

        return response()->json(['success' => 'Leave type updated successfully.']);
    }

    public function destroy(LeavePolicy $leavePolicy) {
        $leavePolicy->delete();
        return response()->json(['success' => 'Leave type deleted successfully.']);
    }
}