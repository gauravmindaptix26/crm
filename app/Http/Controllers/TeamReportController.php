<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectPayment;
use App\Models\Dsr;
use App\Models\Department;
use Carbon\Carbon;

class TeamReportController extends Controller
{
    public function index(Request $request)
    {
        // Fetch departments, sorted alphabetically by name
        $departments = Department::orderBy('name', 'asc')->get();
        // Fetch project managers, sorted alphabetically by name
        $projectManagers = User::role('Project Manager')->orderBy('name', 'asc')->get();

        // Get filters with default values
        $departmentId = $request->input('department_id', 4); // Default to SEO department (ID = 4)
        $managerId = $request->input('manager_id');
        $month = $request->input('month', now()->format('m')); // Default to current month
        $year = $request->input('year', now()->format('Y')); // Default to current year

        // Get users based on department and (optional) manager, sorted alphabetically by name
        $users = User::where('department_id', $departmentId)
                    ->when($managerId, fn($q) => $q->where('reporting_person', $managerId))
                    ->where('disable_login', 0) // Exclude disabled users
                    ->whereHas('department', fn($q) => $q->where('name', '!=', 'LEFT People')) // Exclude LEFT People
                    ->orderBy('name', 'asc')
                    ->get();

        // Date range for the selected month
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = (clone $startDate)->endOfMonth();

        // Initialize totals
        $report = [
            'total_employees' => $users->count(),
            'total_experience' => 0,
            'total_assigned_hours' => 0,
            'total_worked_hours' => 0,
            'total_payment' => 0,
            'total_received' => 0,
            'total_upsell_amount' => 0,
        ];

        // Augment each user with calculated values for the table
        foreach ($users as $user) {
            // Parse experience safely
            $experience = floatval(preg_replace('/[^0-9.]/', '', $user->experience ?? 0));
            $report['total_experience'] += $experience;

            // Get projects assigned (as main or additional employee) for the selected year and month
            $assignedProjectsQuery = Project::where('department_id', $departmentId)
                ->where(function($q) use ($user) {
                    $q->where('assign_main_employee_id', $user->id)
                      ->orWhereJsonContains('additional_employees', (string) $user->id);
                })
                ->whereYear('created_at', $year);

            if ($month !== 'ALL') {
                $assignedProjectsQuery->whereMonth('created_at', $month);
            }

            $assignedProjects = $assignedProjectsQuery->get();
            $currentAssignedHours = $assignedProjects->sum('estimated_hours');

            // If no assigned hours for the current month, fetch the previous month's hours
            $assignedHours = $currentAssignedHours;
            if ($currentAssignedHours == 0 && $month !== 'ALL') {
                $previousMonth = date('m', strtotime("-1 month", strtotime("$year-$month-01")));
                $previousYear = date('Y', strtotime("-1 month", strtotime("$year-$month-01")));

                $previousAssignedProjectsQuery = Project::where('department_id', $departmentId)
                    ->where(function($q) use ($user) {
                        $q->where('assign_main_employee_id', $user->id)
                          ->orWhereJsonContains('additional_employees', (string) $user->id);
                    })
                    ->whereYear('created_at', $previousYear)
                    ->whereMonth('created_at', $previousMonth);

                $assignedHours = $previousAssignedProjectsQuery->get()->sum('estimated_hours');
            }

            // Update total assigned hours
            $report['total_assigned_hours'] += $assignedHours;

            // Payment
            $payment = $assignedProjects->sum('price');
            $report['total_payment'] += $payment;

            // Worked hours in DSR
            $workedHours = DSR::where('user_id', $user->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('hours');
            $report['total_worked_hours'] += $workedHours;

            // Received payment
            $received = ProjectPayment::whereIn('project_id', $assignedProjects->pluck('id'))
                ->whereMonth('payment_month', $month === 'ALL' ? '=' : '=', $month)
                ->whereYear('payment_month', $year)
                ->sum('payment_amount');
            $report['total_received'] += $received;

            // Upsell incentive
            $upsellPercent = floatval(preg_replace('/[^0-9.]/', '', $user->upsell_incentive ?? 0));
            $upsellAmount = $payment * $upsellPercent / 100;
            $report['total_upsell_amount'] += $upsellAmount;

            // Attach calculated fields to user (for table rendering)
            $user->assigned_hours = $assignedHours;
            $user->worked_hours = $workedHours;
            $user->total_payment = $payment;
            $user->received = $received;
            $user->upsell_percent = $upsellPercent;
            $user->upsell_amount = $upsellAmount;
        }

        return view('reports.index', compact(
            'report',
            'users',
            'departments',
            'projectManagers',
            'departmentId',
            'managerId',
            'month',
            'year'
        ));
    }
    
}

