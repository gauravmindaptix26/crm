<?php
namespace App\Http\Controllers;

use App\Models\LeavePolicy;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LeaveController extends Controller {
    
    public function create() {
        $user = Auth::user();
        $policies = LeavePolicy::all();
        
        $eligiblePolicies = $policies->filter(function ($policy) use ($user) {
            return $user->isEligibleForPolicy($policy);
        });
        
        // Ensure Unpaid Leave is always included
        $unpaidPolicy = $policies->firstWhere('name', 'Unpaid Leave');
        if ($unpaidPolicy && !$eligiblePolicies->contains($unpaidPolicy)) {
            $eligiblePolicies->push($unpaidPolicy);
        }
        
        $unpaidPolicyId = $unpaidPolicy?->id;
        
        Log::info('Leave create', [
            'user_id' => $user->id,
            'eligible_policies' => $eligiblePolicies->pluck('name')->toArray(),
            'unpaid_policy_id' => $unpaidPolicyId
        ]);
        
        return view('leaves.apply', compact('eligiblePolicies', 'user', 'unpaidPolicyId'));
    }

    public function store(Request $request) {
        Log::info('Leave store attempt', $request->all());
        
        try {
            $user = Auth::user();
            $requestType = $request->input('request_type', 'full');

            $validated = $requestType === 'partial' 
                ? $this->validatePartialRequest($request)
                : $this->validateFullRequest($request);

            Log::info('Validation passed', ['validated' => $validated]);

            $policy = LeavePolicy::findOrFail($validated['leave_policy_id']);
            
            if ($requestType !== 'partial' && !$user->isEligibleForPolicy($policy)) {
                return back()->withErrors(['leave_policy_id' => 'Not eligible during probation period.']);
            }

            $duration = $this->calculateDuration($validated, $requestType === 'partial', $request);
            
            // Check balance for non-unlimited, non-partial requests
            if ($requestType !== 'partial' && $policy->name !== 'Unpaid Leave') {
                $balance = $user->getLeaveBalance($policy->name);
                if (is_numeric($balance['available']) && $balance['available'] < $duration) {
                    return back()->withErrors(['duration' => 'Insufficient leave balance. Available: ' . $balance['available'] . ' days']);
                }
            }

            $leaveData = array_merge($validated, [
                'user_id' => $user->id,
                'duration' => $duration,
                'status' => 'pending'
            ]);

            Log::info('Creating leave request', $leaveData);

            $leaveRequest = LeaveRequest::create($leaveData);
            
            Log::info('Leave request created successfully', ['id' => $leaveRequest->id]);

            return redirect()->route('leaves.history')->with('success', 'Leave request submitted successfully.');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Leave store error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return back()->withErrors(['error' => 'An error occurred while submitting your request. Please try again.'])->withInput();
        }
    }

    private function validateFullRequest(Request $request) {
        $rules = [
            'leave_policy_id' => 'required|exists:leave_policies,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'note' => 'nullable|string|max:1000',
        ];

        // Only validate half day option if is_custom is 1 and it's a single day
        $isCustom = $request->input('is_custom') == '1';
        if ($isCustom) {
            $rules['is_custom'] = 'required|in:1';
            $rules['half_day_option'] = 'required|in:morning,afternoon,full';
        } else {
            $rules['is_custom'] = 'required|in:0';
        }

        $validated = $request->validate($rules);
        
        // Ensure end_date is set to start_date if not provided for single day
        if (!$validated['end_date']) {
            $validated['end_date'] = $validated['start_date'];
        }
        
        return $validated;
    }

    private function validatePartialRequest(Request $request) {
        $unpaidPolicy = LeavePolicy::where('name', 'Unpaid Leave')->first();
        if (!$unpaidPolicy) {
            throw new \Exception('Unpaid Leave policy not found');
        }

        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'partial_type' => 'required|in:late_arrival,leaving_early',
            'partial_minutes' => 'required|integer|min:1|max:480',
            'note' => 'required|string|max:1000',
            'leave_policy_id' => 'required|in:' . $unpaidPolicy->id,
        ]);

        $validated['end_date'] = $validated['start_date']; // Partial is single day
        return $validated;
    }

    private function calculateDuration($validated, $isPartial, $request) {
        if ($isPartial) {
            return $validated['partial_minutes'] / 480; // Convert minutes to days
        }
    
        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);
        $days = $start->diffInDays($end) + 1;
    
        $isCustom = $request->input('is_custom') == '1';
        
        if ($isCustom && $days === 1) {
            $halfDayOption = $request->input('half_day_option');
            return match($halfDayOption) {
                'morning', 'afternoon' => 0.5,
                'full' => 1.0,
                default => 1.0
            };
        }
    
        return $days;
    }

    public function history() {
        $requests = Auth::user()->leaveRequests()
            ->with('leavePolicy')
            ->orderBy('start_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    
        // Ensure dates are properly cast as Carbon instances
        $requests->getCollection()->transform(function ($request) {
            $request->start_date = $request->start_date ? Carbon::parse($request->start_date) : null;
            $request->end_date = $request->end_date ? Carbon::parse($request->end_date) : null;
            $request->created_at = Carbon::parse($request->created_at);
            return $request;
        });
    
        return view('leaves.history', compact('requests'));
    }

    public function pending() {
        $user = Auth::user();
        
        if (!in_array($user->user_role, ['HR', 'Admin', 'Project Manager'])) {
            abort(403);
        }

        $query = LeaveRequest::with(['user', 'leavePolicy'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc');

        if ($user->user_role === 'Project Manager') {
            $teamIds = $user->teamMembers()->pluck('id');
            $query->whereIn('user_id', $teamIds);
        }

        $requests = $query->paginate(10);
        return view('leaves.pending', compact('requests'));
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        Log::info('Approve attempt', [
            'user_id' => Auth::id(),
            'user_email' => Auth::user()->email,
            'user_roles' => Auth::user()->roles->pluck('name')->toArray(),
            'leave_request_id' => $leaveRequest->id,
            'leave_user_id' => $leaveRequest->user_id,
            'team_ids' => Auth::user()->team()->pluck('id')->toArray()
        ]);

        if (!Auth::user()->canApproveLeave($leaveRequest)) {
            Log::error('User cannot approve leave', [
                'user_id' => Auth::id(),
                'user_roles' => Auth::user()->roles->pluck('name')->toArray(),
                'team_ids' => Auth::user()->team()->pluck('id')->toArray(),
                'leave_user_id' => $leaveRequest->user_id
            ]);
            abort(403, 'You do not have permission to approve this request.');
        }

        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'reason' => $request->input('reason') ?? null
        ]);

        Log::info('Leave approved successfully', ['id' => $leaveRequest->id]);
        return back()->with('success', 'Leave approved successfully.');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        Log::info('Reject attempt', [
            'user_id' => Auth::id(),
            'leave_request_id' => $leaveRequest->id
        ]);

        if (!Auth::user()->canApproveLeave($leaveRequest)) {
            Log::error('User cannot reject leave', [
                'user_id' => Auth::id(),
                'user_roles' => Auth::user()->roles->pluck('name')->toArray(),
                'team_ids' => Auth::user()->team()->pluck('id')->toArray(),
                'leave_user_id' => $leaveRequest->user_id
            ]);
            abort(403, 'You do not have permission to reject this request.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $leaveRequest->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'reason' => $validated['reason']
        ]);

        Log::info('Leave rejected successfully', ['id' => $leaveRequest->id]);
        return back()->with('success', 'Leave rejected successfully.');
    }
    public function teamHistory(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has permission to view team history
        if (!$user->hasRole(['HR', 'Project Manager', 'Team Lead', 'Admin'])) {
            abort(403, 'You do not have permission to view team leave history.');
        }

        $query = LeaveRequest::with(['user', 'leavePolicy', 'approver'])
            ->orderBy('start_date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($user->hasRole('HR') || $user->hasRole('Admin')) {
            // HR and Admin can see all requests
            $requests = $query->paginate(15);
        } elseif ($user->hasRole(['Project Manager', 'Team Lead'])) {
            // Project Manager and Team Lead see their team's requests
            $teamUserIds = $user->team()->pluck('id')->toArray();
            $requests = $query->whereIn('user_id', $teamUserIds)->paginate(15);
        } else {
            abort(403, 'Unauthorized access to team history.');
        }

        return view('leaves.team-history', compact('requests', 'user'));
    }
    private function canApproveRequest(LeaveRequest $request)
{
    $user = Auth::user();
    
    // Admin and HR can approve any request
    if ($user->hasAnyRole(['Admin', 'HR'])) {
        return true;
    }
    
    // Project Manager/Team Lead can approve their team's requests
    if ($user->hasAnyRole(['Project Manager', 'Team Lead'])) {
        $teamUserIds = $user->team()->pluck('id')->toArray();
        return in_array($request->user_id, $teamUserIds);
    }
    
    return false;
}
public function dashboard()
{
    $user = Auth::user();
    
    // Fetch balances
    $policies = LeavePolicy::all();
    $balances = [];
    foreach ($policies as $policy) {
        $balances[$policy->name] = $user->getLeaveBalance($policy->name);
    }

    // Fetch history
    $requests = $user->leaveRequests()
        ->with('leavePolicy', 'approver')
        ->orderBy('start_date', 'desc')
        ->paginate(10);

    return view('attendance.dashboard', compact('balances', 'requests'));
}
}