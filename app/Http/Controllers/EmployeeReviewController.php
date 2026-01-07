<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreEmployeeReviewRequest;
use App\Models\User;
use App\Models\EmployeeReview;
use App\Models\Department;
use Carbon\Carbon;

class EmployeeReviewController extends Controller
{
    // Show team members and current month reviews
    public function index(Request $request)
    {
        $user = auth()->user();

        // Role check: only Project Managers allowed
        if (!$user->hasRole('Project Manager')) {
            abort(403, 'Unauthorized access.');
        }

        $month = $request->input('month', now()->format('Y-m'));
        $firstOfMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        // Get the ID of "Left People" department
        $leftPeopleDept = Department::where('name', 'Left People')->first();

        // team members (reporting_person column holds PM id) excluding "Left People" and only active
        $teamQuery = User::where('reporting_person', $user->id);
                         

        if ($leftPeopleDept) {
            $teamQuery->where('department_id', '!=', $leftPeopleDept->id);
        }

        $team = $teamQuery->get();

        // fetch existing reviews for employees for that month
        $reviews = EmployeeReview::whereIn('employee_id', $team->pluck('id'))
            ->where('review_month', $firstOfMonth->toDateString())
            ->get()
            ->keyBy('employee_id');

        return view('reviews.index', compact('team', 'reviews', 'month'));
    }

    // Store or update single review
    public function store(StoreEmployeeReviewRequest $request)
    {
        $user = auth()->user();

        // Role check
        if (!$user->hasRole('Project Manager')) {
            abort(403, 'Unauthorized access.');
        }

        $data = $request->validated();

        // Get "Left People" department ID
        $leftPeopleDept = Department::where('name', 'Left People')->first();

        // ensure PM can review this employee (active, not in Left People)
        $employeeQuery = User::where('id', $data['employee_id'])
                             ->where('reporting_person', $user->id);

        if ($leftPeopleDept) {
            $employeeQuery->where('department_id', '!=', $leftPeopleDept->id);
        }

        $employee = $employeeQuery->firstOrFail();

        $reviewMonth = Carbon::createFromFormat('Y-m', $data['review_month'])->startOfMonth()->toDateString();

        $scores = [
            $data['quality_of_work'],
            $data['communication'],
            $data['ownership'],
            $data['team_collaboration']
        ];

        $overall = EmployeeReview::computeOverall($scores);

        $payload = [
            'project_manager_id' => $user->id,
            'department_id' => $employee->department_id,
            'review_month' => $reviewMonth,
            'quality_of_work' => $data['quality_of_work'],
            'communication' => $data['communication'],
            'ownership' => $data['ownership'],
            'team_collaboration' => $data['team_collaboration'],
            'overall_rating' => $overall,
            'comments' => $data['comments'] ?? null,
        ];

        // upsert: create or update existing review
        EmployeeReview::updateOrCreate(
            ['employee_id' => $employee->id, 'review_month' => $reviewMonth],
            $payload
        );

        return redirect()->back()->with('success', 'Review saved.');
    }

    public function allReviews(Request $request)
    {
        $user = auth()->user();

        if (!in_array($user->roles->first()->name, ['Admin', 'HR'])) {
            abort(403, 'Unauthorized action.');
        }

        $departmentId = $request->input('department_id');
        $projectManagerId = $request->input('project_manager_id');
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');
        $month = $request->input('month', Carbon::now()->month); // Default to current month

        $reviews = EmployeeReview::with(['employee.department', 'reviewer'])
            ->when($departmentId, function ($query, $departmentId) {
                $query->whereHas('employee.department', function ($q) use ($departmentId) {
                    $q->where('id', $departmentId);
                });
            })
            ->when($projectManagerId, function ($query, $projectManagerId) {
                $query->whereHas('employee', function ($q) use ($projectManagerId) {
                    $q->where('reporting_person', $projectManagerId);
                });
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('employee', function ($q) use ($search) {
                        $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
                    })->orWhereHas('reviewer', function ($q) use ($search) {
                        $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
                    })->orWhereRaw('LOWER(comments) LIKE ?', ['%' . strtolower($search) . '%']);
                });
            })
            ->when($month, function ($query, $month) {
                $query->whereMonth('created_at', $month);
            })
            ->latest()
            ->paginate($perPage)
            ->appends([
                'department_id' => $departmentId,
                'project_manager_id' => $projectManagerId,
                'per_page' => $perPage,
                'search' => $search,
                'month' => $month
            ]);

        $departments = Department::all();

        $projectManagers = collect();
        if ($departmentId) {
            $projectManagers = User::whereHas('roles', function ($q) {
                $q->where('name', 'Project Manager');
            })
                ->where('department_id', $departmentId)
                ->get();
        }

        if ($request->ajax()) {
            return response()->json([
                'html' => view('reviews.all', compact('reviews', 'departments', 'departmentId', 'projectManagers', 'projectManagerId'))->renderSections()['table'],
                'pagination' => $reviews->links()->toHtml()
            ]);
        }

        return view('reviews.all', compact('reviews', 'departments', 'departmentId', 'projectManagers', 'projectManagerId', 'month'));
    }
}
