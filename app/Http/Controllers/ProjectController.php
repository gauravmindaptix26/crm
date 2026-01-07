<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectPayment;
use App\Models\AssignedProject;



use App\Models\Country;
use App\Models\User;
use App\Models\TaskPhase;
use App\Models\Dsr;
use App\Models\HiredFrom;

use App\Models\SaleTeamProject; // Add this at the top if not already imported


use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProjectAssignedMail;
use App\Mail\ProjectStatusUpdated;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;





class ProjectController extends Controller {


    public function index(Request $request)
    {
       // ini_set('memory_limit', '512M'); // Increase memory limit for this request
        $loggedInUser = auth()->user();
    
        if ($loggedInUser->hasRole('Employee')) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
        }
    
        // Log request input for debugging
        Log::debug('Request Input for User ID: ' . $loggedInUser->id, $request->all());
    
        // Log user roles for debugging
        Log::debug('User Roles for User ID: ' . $loggedInUser->id, ['roles' => $loggedInUser->roles->pluck('name')->toArray()]);
    
        // Base query for projects
        $query = Project::with([
            'projectManager', 'salesPerson', 'department', 'country',
            'projectCategory', 'projectSubCategory', 'upsellEmployee',
            'contentManager', 'projectPayments', 'saleTeamAttachments'
        ]);
    
        // Log base query results
        $baseProjects = $query->get();
        Log::debug('Base Query Projects for User ID: ' . $loggedInUser->id, ['count' => $baseProjects->count(), 'projects' => $baseProjects->pluck('id')->toArray()]);
    
        // Define project_year early for use in queries
        $projectYear = $request->input('project_year', '');
    
        // Filters by role
        if (!$loggedInUser->hasAnyRole(['Admin', 'HR'])) {
            if ($loggedInUser->hasAnyRole(['Sales Team', 'Sales Team Manager'])) {
                $query->where(function ($q) use ($loggedInUser) {
                    $q->where('created_by', $loggedInUser->id)
                      ->orWhere('sales_person_id', $loggedInUser->id);
                });
            } 
            
            // elseif ($loggedInUser->hasRole('Team Lead')) {
            //     $query->where('team_lead_id', $loggedInUser->id);
            // }
            
            
            
            elseif ($loggedInUser->hasRole('Team Lead')) {
                $query->where(function ($q) use ($loggedInUser) {
                    $q->where('team_lead_id', $loggedInUser->id)
                      ->orWhere('project_manager_id', $loggedInUser->id)
                      ->orWhere('assign_main_employee_id', $loggedInUser->id)
                      ->orWhereRaw('JSON_CONTAINS(additional_employees, ?)', ['["'.$loggedInUser->id.'"]'])
            
                      // Fixed: Properly grouped subquery for assigned_projects
                      ->orWhere(function ($subQuery) use ($loggedInUser) {
                          $subQuery->whereIn('projects.id', function ($assigned) use ($loggedInUser) {
                              $assigned->select('project_id')
                                       ->from('assigned_projects')
                                       ->where(function ($aq) use ($loggedInUser) {
                                           $aq->where('team_lead_id', $loggedInUser->id)
                                              ->orWhere('project_manager_id', $loggedInUser->id)
                                              ->orWhere('assigned_employee_id', $loggedInUser->id);
                                       });
                              });
                      });
                });
            
                // Optional: Debug after Team Lead filter
                Log::debug('Team Lead Filtered Projects Count: ' . $query->count());
                Log::debug('Team Lead Filtered Project IDs: ', $query->pluck('projects.id')->toArray());
            }
            elseif ($loggedInUser->hasRole('Project Manager')) {
                $query->where(function ($q) use ($loggedInUser) {
                    $q->where('project_manager_id', $loggedInUser->id)
                      ->orWhere('assign_main_employee_id', $loggedInUser->id)
                      ->orWhereIn('projects.id', function ($sub) use ($loggedInUser) {
                          $sub->select('project_id')
                              ->from('assigned_projects')
                              ->where('project_manager_id', $loggedInUser->id);
                      })
                      ->orWhereRaw('additional_employees LIKE ?', ['%"' . $loggedInUser->id . '"%']);
                });
            } else {
                $query->where('created_by', $loggedInUser->id);
            }
            // Log projects after role filters
            $roleFilteredProjects = $query->get();
            Log::debug('Projects after Role Filters for User ID: ' . $loggedInUser->id, ['count' => $roleFilteredProjects->count(), 'projects' => $roleFilteredProjects->pluck('id')->toArray()]);
        }
    
        // Search query
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name_or_url', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%");
            });
            // Log projects after search filter
            $searchFilteredProjects = $query->get();
            Log::debug('Projects after Search Filter for User ID: ' . $loggedInUser->id, ['count' => $searchFilteredProjects->count(), 'projects' => $searchFilteredProjects->pluck('id')->toArray()]);
        }
    
        // Existing filters
        if ($request->filled('project_manager_id')) {
            $query->where('project_manager_id', $request->project_manager_id);
        }
        if ($request->filled('sales_person_id')) {
            $query->where('sales_person_id', $request->sales_person_id);
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('assign_main_employee_id')) {
            $query->where('assign_main_employee_id', $request->assign_main_employee_id);
        }
        if ($request->filled('project_status')) {
            if ($request->project_status === 'Rehire') {
                $query->where('can_client_rehire', 'Yes')->whereNotNull('rehire_date');
            } elseif ($request->project_status === 'New') {
                // For 'New', filter by current month (September 2025) or selected month
                $month = $request->filled('project_month') ? $request->project_month : Carbon::now()->month;
                $year = $request->filled('project_year') ? $request->project_year : 2025;
                $query->whereYear('projects.created_at', $year)
                      ->whereMonth('projects.created_at', $month);
            } else {
                $query->where('project_status', $request->project_status);
            }
            // Log projects after project_status filter
            $statusFilteredProjects = $query->get();
            Log::debug('Projects after Status Filter for User ID: ' . $loggedInUser->id, ['status' => $request->project_status, 'count' => $statusFilteredProjects->count(), 'projects' => $statusFilteredProjects->pluck('id')->toArray()]);
        }
        if ($request->filled('client_type')) {
            $query->where('client_type', $request->client_type);
        }
        if ($request->filled('project_grade')) {
            $query->where('project_grade', $request->project_grade);
        }
        if ($request->filled('business_type')) {
            $query->where('business_type', $request->business_type);
        }
        if ($request->filled('select_status')) {
            $query->where('project_status', $request->select_status);
        }
        if ($request->filled('project_month')) {
            $query->whereMonth('projects.created_at', $request->project_month);
        }
        // Apply project_year filter based on rehire_date for Rehire filter, otherwise use created_at
        if ($request->filled('project_year')) {
            if ($request->filled('project_status') && $request->project_status === 'Rehire') {
                $query->whereYear('projects.rehire_date', $projectYear);
            } else {
                $query->whereYear('projects.created_at', $projectYear);
            }
            // Log projects after year filter
            $yearFilteredProjects = $query->get();
            Log::debug('Projects after Year Filter for User ID: ' . $loggedInUser->id, ['year' => $projectYear, 'count' => $yearFilteredProjects->count(), 'projects' => $yearFilteredProjects->pluck('id')->toArray()]);
        }
        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }
        if ($request->filled('project_category_id')) {
            $query->where('project_category_id', $request->project_category_id);
        }
        if ($request->filled('project_subcategory_id')) {
            $query->where('project_subcategory_id', $request->project_subcategory_id);
        }
    
        // Clone query for stats (activeProjects)
        $statsQuery = clone $query;
    
        // Clone query for prediction amount (includes all filters except pending_payment)
        $predictionQuery = clone $query;
    
        // Log prediction query results for debugging
        $predictionProjects = $predictionQuery->get();
        Log::debug('Prediction Query Projects for User ID: ' . $loggedInUser->id, [
            'count' => $predictionProjects->count(),
            'projects' => $predictionProjects->pluck('id')->toArray(),
            'prediction_amount' => $predictionQuery->sum(DB::raw('COALESCE(price, 0)'))
        ]);
    
        // Clone query for amountReceived to include pending_payment filter
        $amountReceivedQuery = clone $query;
    
        // Apply pending_payment filter
if ($request->filled('pending_payment') && $request->pending_payment == '1') {

    $query->select(
            'projects.*',
            DB::raw('(
                SELECT COALESCE(SUM(pp.payment_amount), 0)
                FROM project_payments pp
                WHERE pp.project_id = projects.id
            ) AS total_paid')
        )
        ->whereNotNull('projects.price')
        ->where('projects.price', '>', 0)
        ->whereRaw(
            'COALESCE(projects.price, 0) >
             (
                SELECT COALESCE(SUM(pp.payment_amount), 0)
                FROM project_payments pp
                WHERE pp.project_id = projects.id
             )'
        );

    // Apply SAME logic to amountReceivedQuery
    $amountReceivedQuery
        ->select('projects.id')
        ->whereNotNull('projects.price')
        ->where('projects.price', '>', 0)
        ->whereRaw(
            'COALESCE(projects.price, 0) >
             (
                SELECT COALESCE(SUM(pp.payment_amount), 0)
                FROM project_payments pp
                WHERE pp.project_id = projects.id
             )'
        );

    // Debug
    $pendingProjects = $query->get();
    Log::debug(
        'Pending Payment Projects for User ID: ' . $loggedInUser->id,
        ['count' => $pendingProjects->count(), 'projects' => $pendingProjects->pluck('id')->toArray()]
    );

} else {
    $query->select('projects.*');
    $amountReceivedQuery->select('projects.id');
}

    
        // Log the query for debugging
        $querySql = $query->toSql();
        $queryBindings = $query->getBindings();
        Log::debug('Final Project Query SQL for User ID: ' . $loggedInUser->id, ['sql' => $querySql, 'bindings' => $queryBindings]);
    
        // Fetch and paginate
        $perPage = $request->input('entries_per_page', 10);
        $paginatedProjects = $query->orderBy('projects.created_at', 'desc')->paginate($perPage)->appends($request->query());
    
        // Log project count
        Log::debug('Projects Fetched for User ID: ' . $loggedInUser->id, ['count' => $paginatedProjects->total(), 'projects' => $paginatedProjects->pluck('id')->toArray()]);
    
        // Attach is_sale_team and is_assigned_by_user dynamically
        $paginatedProjects->getCollection()->transform(function ($project) use ($loggedInUser) {
            $project->is_sale_team = AssignedProject::where('project_id', $project->id)->exists();
            $project->content_price = 0;
    
            if (!empty($project->content_details)) {
                $contentDetails = is_string($project->content_details)
                    ? json_decode($project->content_details, true)
                    : $project->content_details;
    
                if (is_array($contentDetails)) {
                    foreach ($contentDetails as $content) {
                        $type = strtolower($content['type'] ?? '');
                        $quantity = (int) ($content['quantity'] ?? 0);
    
                        switch ($type) {
                            case 'blog':
                                $project->content_price += $quantity * 10;
                                break;
                            case 'article':
                                $project->content_price += $quantity * 15;
                                break;
                            case 'post':
                                $project->content_price += $quantity * 20;
                                break;
                        }
                    }
                }
            }
    
            $additionalEmployees = [];
            if (isset($project->additional_employees)) {
                if (is_string($project->additional_employees)) {
                    $decoded = json_decode($project->additional_employees, true);
                    $additionalEmployees = is_array($decoded) ? $decoded : [];
                } elseif (is_array($project->additional_employees)) {
                    $additionalEmployees = $project->additional_employees;
                }
            }
    
            $project->is_assigned_by_user = AssignedProject::where('project_id', $project->id)
                ->where('project_manager_id', $loggedInUser->id)
                ->exists() || in_array($loggedInUser->id, $additionalEmployees);
    
            return $project;
        });
    
        // Stats
        $totalProjects = $paginatedProjects->total();
        $activeProjects = $statsQuery->where('project_status', 'Working')->count();
        $predictionAmount = $predictionQuery->sum(DB::raw('COALESCE(price, 0)'));
    
        // Initialize $salesTeamProjectCount to avoid undefined variable error
        $salesTeamProjectCount = 0;
        if ($loggedInUser->hasAnyRole(['Sales Team', 'Sales Team Manager'])) {
            $salesTeamProjectCount = Project::where(function ($q) use ($loggedInUser) {
                $q->where('created_by', $loggedInUser->id)
                  ->orWhere('sales_person_id', $loggedInUser->id);
            })
                ->whereYear('created_at', now()->year)
                ->count();
            Log::debug('Sales Team Project Count for User ID: ' . $loggedInUser->id, ['count' => $salesTeamProjectCount]);
        } elseif ($loggedInUser->hasAnyRole(['Admin', 'HR'])) {
            $salesTeamProjectCount = Project::whereYear('created_at', now()->year)
                ->count();
            Log::debug('Admin/HR Project Count for User ID: ' . $loggedInUser->id, ['count' => $salesTeamProjectCount]);
        }
    
        // Amount Received for the selected year
        if ($request->filled('pending_payment') && $request->pending_payment == '1') {
            $amountReceived = 0;
        } else {
            if ($loggedInUser->hasAnyRole(['Admin', 'HR'])) {
                $amountReceived = DB::table('project_payments')
                    ->join('projects', 'project_payments.project_id', '=', 'projects.id')
                    ->whereIn('projects.id', $amountReceivedQuery->pluck('id'))
                    ->whereYear('project_payments.created_at', $projectYear ?: now()->year)
                    ->sum('project_payments.payment_amount');
            } else {
                $filteredProjectIds = $amountReceivedQuery->pluck('id');
                $amountReceived = DB::table('project_payments')
                    ->whereIn('project_id', $filteredProjectIds)
                    ->whereYear('project_payments.created_at', $projectYear ?: now()->year)
                    ->sum('payment_amount');
            }
        }
    
        // Admin/HR: Total amounts for all projects (across all years)
        $totalAmountReceived = $loggedInUser->hasAnyRole(['Admin', 'HR'])
            ? DB::table('project_payments')->sum('payment_amount')
            : 0;
        $totalPredictionAmount = $loggedInUser->hasAnyRole(['Admin', 'HR'])
            ? Project::sum('price')
            : 0;
    
        // Define $selectedYear for the view
        $selectedYear = $projectYear ?: now()->year;
    
        // Dropdowns
        $mainCategories = ProjectCategory::with('subcategories')->whereNull('parent_id')->get();
        $countries = Country::all();
        $taskPhases = TaskPhase::all();
        $employees = User::orderBy('name', 'asc')->get();
        $departments = Department::all();
        $projectManagers = User::whereHas('roles', fn($q) => $q->where('name','Project Manager'))->orderBy('name')->get(); 

        $salesPersons = User::whereHas('roles', function ($q) {$q->whereIn('name', ['Sales Team', 'Sales Team Manager']);})->orderBy('name')->get();        

        $teamLeads = User::role('Team Lead')->orderBy('name', 'asc')->get();
       $users = User::with('roles', 'department')->orderBy('name', 'desc')->get();        $contentManagers = collect();
        if ($contentDept = Department::where('name', 'Content Department')->first()) {
            $contentManagers = User::whereHas('roles', fn($q) => $q->where('name', 'Project Manager'))
                                   ->where('department_id', $contentDept->id)->get();
        }
    
        return view('projects.index', compact(
            'paginatedProjects',
            'mainCategories',
            'countries',
            'projectManagers',
            'taskPhases',
            'employees',
            'salesPersons',
            'departments',
            'totalProjects',
            'activeProjects',
            'predictionAmount',
            'amountReceived',
            'teamLeads',
            'users',
            'contentManagers',
            'loggedInUser',
            'totalAmountReceived',
            'totalPredictionAmount',
            'selectedYear',
            'salesTeamProjectCount'
        ));
    }
    

    
    //old code
    // public function index(Request $request)
    // {
    //     ini_set('memory_limit', '512M'); // Increase memory limit for this request
    //     $loggedInUser = auth()->user();
    
    //     if ($loggedInUser->hasRole('Employee')) {
    //         return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
    //     }
    
    //     // Log request input for debugging
    //     Log::debug('Request Input for User ID: ' . $loggedInUser->id, $request->all());
    
    //     // Log user roles for debugging
    //     Log::debug('User Roles for User ID: ' . $loggedInUser->id, ['roles' => $loggedInUser->roles->pluck('name')->toArray()]);
    
    //     // Base query for projects
    //     $query = Project::with([
    //         'projectManager', 'salesPerson', 'department', 'country',
    //         'projectCategory', 'projectSubCategory', 'upsellEmployee',
    //         'contentManager', 'projectPayments', 'saleTeamAttachments'
    //     ]);
    
    //     // Log base query results
    //     $baseProjects = $query->get();
    //     Log::debug('Base Query Projects for User ID: ' . $loggedInUser->id, ['count' => $baseProjects->count(), 'projects' => $baseProjects->pluck('id')->toArray()]);
    
    //     // Define project_year early for use in queries
    //     $projectYear = $request->input('project_year', '');
    
    //     // Filters by role
    //     if (!$loggedInUser->hasAnyRole(['Admin', 'HR'])) {
    //         if ($loggedInUser->hasAnyRole(['Sales Team', 'Sales Team Manager'])) {
    //             $query->where(function ($q) use ($loggedInUser) {
    //                 $q->where('created_by', $loggedInUser->id)
    //                   ->orWhere('sales_person_id', $loggedInUser->id);
    //             });
    //         } elseif ($loggedInUser->hasRole('Team Lead')) {
    //             $query->where(function ($q) use ($loggedInUser) {
    //                 $q->where('team_lead_id', $loggedInUser->id)
    //                   ->orWhere('project_manager_id', $loggedInUser->id)
    //                   ->orWhere('assign_main_employee_id', $loggedInUser->id)
    //                   ->orWhereRaw('JSON_CONTAINS(additional_employees, ?)', ['["'.$loggedInUser->id.'"]'])
            
    //                   // Fixed: Properly grouped subquery for assigned_projects
    //                   ->orWhere(function ($subQuery) use ($loggedInUser) {
    //                       $subQuery->whereIn('projects.id', function ($assigned) use ($loggedInUser) {
    //                           $assigned->select('project_id')
    //                                    ->from('assigned_projects')
    //                                    ->where(function ($aq) use ($loggedInUser) {
    //                                        $aq->where('team_lead_id', $loggedInUser->id)
    //                                           ->orWhere('project_manager_id', $loggedInUser->id)
    //                                           ->orWhere('assigned_employee_id', $loggedInUser->id);
    //                                    });
    //                           });
    //                   });
    //             });
            
    //             // Optional: Debug after Team Lead filter
    //             Log::debug('Team Lead Filtered Projects Count: ' . $query->count());
    //             Log::debug('Team Lead Filtered Project IDs: ', $query->pluck('projects.id')->toArray());
    //         }

            
            
    //          elseif ($loggedInUser->hasRole('Project Manager')) {
    //             $query->where(function ($q) use ($loggedInUser) {
    //                 $q->where('project_manager_id', $loggedInUser->id)
    //                   ->orWhere('assign_main_employee_id', $loggedInUser->id)
    //                   ->orWhereIn('projects.id', function ($sub) use ($loggedInUser) {
    //                       $sub->select('project_id')
    //                           ->from('assigned_projects')
    //                           ->where('project_manager_id', $loggedInUser->id);
    //                   })
    //                   ->orWhereRaw('additional_employees LIKE ?', ['%"' . $loggedInUser->id . '"%']);
    //             });
    //         } else {
    //             $query->where('created_by', $loggedInUser->id);
    //         }
    //         // Log projects after role filters
    //         $roleFilteredProjects = $query->get();
    //         Log::debug('Projects after Role Filters for User ID: ' . $loggedInUser->id, ['count' => $roleFilteredProjects->count(), 'projects' => $roleFilteredProjects->pluck('id')->toArray()]);
    //     }
    
    //     // Search query
    //     if ($request->filled('search')) {
    //         $search = $request->input('search');
    //         $query->where(function ($q) use ($search) {
    //             $q->where('name_or_url', 'like', "%{$search}%")
    //               ->orWhere('client_name', 'like', "%{$search}%");
    //         });
    //         // Log projects after search filter
    //         $searchFilteredProjects = $query->get();
    //         Log::debug('Projects after Search Filter for User ID: ' . $loggedInUser->id, ['count' => $searchFilteredProjects->count(), 'projects' => $searchFilteredProjects->pluck('id')->toArray()]);
    //     }
    
    //     // Existing filters
    //     if ($request->filled('project_manager_id')) {
    //         $query->where('project_manager_id', $request->project_manager_id);
    //     }
    //     if ($request->filled('sales_person_id')) {
    //         $query->where('sales_person_id', $request->sales_person_id);
    //     }
    //     if ($request->filled('department_id')) {
    //         $query->where('department_id', $request->department_id);
    //     }
    //     if ($request->filled('assign_main_employee_id')) {
    //         $query->where('assign_main_employee_id', $request->assign_main_employee_id);
    //     }
    //     if ($request->filled('project_status')) {
    //         if ($request->project_status === 'Rehire') {
    //             $query->where('can_client_rehire', 'Yes')->whereNotNull('rehire_date');
    //         } elseif ($request->project_status === 'New') {
    //             // For 'New', filter by current month (September 2025) or selected month
    //             $month = $request->filled('project_month') ? $request->project_month : Carbon::now()->month;
    //             $year = $request->filled('project_year') ? $request->project_year : 2025;
    //             $query->whereYear('projects.created_at', $year)
    //                   ->whereMonth('projects.created_at', $month);
    //         } else {
    //             $query->where('project_status', $request->project_status);
    //         }
    //         // Log projects after project_status filter
    //         $statusFilteredProjects = $query->get();
    //         Log::debug('Projects after Status Filter for User ID: ' . $loggedInUser->id, ['status' => $request->project_status, 'count' => $statusFilteredProjects->count(), 'projects' => $statusFilteredProjects->pluck('id')->toArray()]);
    //     }
    //     if ($request->filled('client_type')) {
    //         $query->where('client_type', $request->client_type);
    //     }
    //     if ($request->filled('project_grade')) {
    //         $query->where('project_grade', $request->project_grade);
    //     }
    //     if ($request->filled('business_type')) {
    //         $query->where('business_type', $request->business_type);
    //     }
    //     if ($request->filled('select_status')) {
    //         $query->where('project_status', $request->select_status);
    //     }
    //     if ($request->filled('project_month')) {
    //         $query->whereMonth('projects.created_at', $request->project_month);
    //     }
    //     // Apply project_year filter based on rehire_date for Rehire filter, otherwise use created_at
    //     if ($request->filled('project_year')) {
    //         if ($request->filled('project_status') && $request->project_status === 'Rehire') {
    //             $query->whereYear('projects.rehire_date', $projectYear);
    //         } else {
    //             $query->whereYear('projects.created_at', $projectYear);
    //         }
    //         // Log projects after year filter
    //         $yearFilteredProjects = $query->get();
    //         Log::debug('Projects after Year Filter for User ID: ' . $loggedInUser->id, ['year' => $projectYear, 'count' => $yearFilteredProjects->count(), 'projects' => $yearFilteredProjects->pluck('id')->toArray()]);
    //     }
    //     if ($request->filled('country_id')) {
    //         $query->where('country_id', $request->country_id);
    //     }
    //     if ($request->filled('project_category_id')) {
    //         $query->where('project_category_id', $request->project_category_id);
    //     }
    //     if ($request->filled('project_subcategory_id')) {
    //         $query->where('project_subcategory_id', $request->project_subcategory_id);
    //     }
    
    //     // Clone query for stats (activeProjects)
    //     $statsQuery = clone $query;
    
    //     // Clone query for prediction amount (includes all filters except pending_payment)
    //     $predictionQuery = clone $query;
    
    //     // Log prediction query results for debugging
    //     $predictionProjects = $predictionQuery->get();
    //     Log::debug('Prediction Query Projects for User ID: ' . $loggedInUser->id, [
    //         'count' => $predictionProjects->count(),
    //         'projects' => $predictionProjects->pluck('id')->toArray(),
    //         'prediction_amount' => $predictionQuery->sum(DB::raw('COALESCE(price, 0)'))
    //     ]);
    
    //     // Clone query for amountReceived to include pending_payment filter
    //     $amountReceivedQuery = clone $query;
    
    //     // Apply pending_payment filter
    //     if ($request->filled('pending_payment') && $request->pending_payment == '1') {
    //         $query->leftJoin('project_payments', 'projects.id', '=', 'project_payments.project_id')
    //               ->select('projects.*', DB::raw('COALESCE(SUM(project_payments.payment_amount), 0) as total_paid'))
    //               ->whereNotNull('projects.price')
    //               ->where('projects.price', '>', 0)
    //               ->groupBy('projects.id')
    //               ->havingRaw('COALESCE(projects.price, 0) > COALESCE(total_paid, 0)');
    
    //         // Apply same filter to amountReceivedQuery
    //         $amountReceivedQuery->leftJoin('project_payments', 'projects.id', '=', 'project_payments.project_id')
    //                             ->select('projects.id')
    //                             ->whereNotNull('projects.price')
    //                             ->where('projects.price', '>', 0)
    //                             ->groupBy('projects.id')
    //                             ->havingRaw('COALESCE(projects.price, 0) > COALESCE(total_paid, 0)');
    
    //         // Debug: Log projects before pagination
    //         $pendingProjects = $query->get();
    //         Log::debug('Pending Payment Projects for User ID: ' . $loggedInUser->id, ['count' => $pendingProjects->count(), 'projects' => $pendingProjects->pluck('id')->toArray()]);
    //     } else {
    //         $query->select('projects.*');
    //         $amountReceivedQuery->select('projects.id');
    //     }
    
    //     // Log the query for debugging
    //     $querySql = $query->toSql();
    //     $queryBindings = $query->getBindings();
    //     Log::debug('Final Project Query SQL for User ID: ' . $loggedInUser->id, ['sql' => $querySql, 'bindings' => $queryBindings]);
    
    //     // Fetch and paginate
    //     $perPage = $request->input('entries_per_page', 10);
    //     $paginatedProjects = $query->orderBy('projects.created_at', 'desc')->paginate($perPage)->appends($request->query());
    
    //     // Log project count
    //     Log::debug('Projects Fetched for User ID: ' . $loggedInUser->id, ['count' => $paginatedProjects->total(), 'projects' => $paginatedProjects->pluck('id')->toArray()]);
    
    //     // Attach is_sale_team and is_assigned_by_user dynamically
    //     $paginatedProjects->getCollection()->transform(function ($project) use ($loggedInUser) {
    //         $project->is_sale_team = AssignedProject::where('project_id', $project->id)->exists();
    //         $project->content_price = 0;
    
    //         if (!empty($project->content_details)) {
    //             $contentDetails = is_string($project->content_details)
    //                 ? json_decode($project->content_details, true)
    //                 : $project->content_details;
    
    //             if (is_array($contentDetails)) {
    //                 foreach ($contentDetails as $content) {
    //                     $type = strtolower($content['type'] ?? '');
    //                     $quantity = (int) ($content['quantity'] ?? 0);
    
    //                     switch ($type) {
    //                         case 'blog':
    //                             $project->content_price += $quantity * 10;
    //                             break;
    //                         case 'article':
    //                             $project->content_price += $quantity * 15;
    //                             break;
    //                         case 'post':
    //                             $project->content_price += $quantity * 20;
    //                             break;
    //                     }
    //                 }
    //             }
    //         }
    
    //         $additionalEmployees = [];
    //         if (isset($project->additional_employees)) {
    //             if (is_string($project->additional_employees)) {
    //                 $decoded = json_decode($project->additional_employees, true);
    //                 $additionalEmployees = is_array($decoded) ? $decoded : [];
    //             } elseif (is_array($project->additional_employees)) {
    //                 $additionalEmployees = $project->additional_employees;
    //             }
    //         }
    
    //         $project->is_assigned_by_user = AssignedProject::where('project_id', $project->id)
    //             ->where('project_manager_id', $loggedInUser->id)
    //             ->exists() || in_array($loggedInUser->id, $additionalEmployees);
    
    //         return $project;
    //     });
    
    //     // Stats
    //     $totalProjects = $paginatedProjects->total();
    //     $activeProjects = $statsQuery->where('project_status', 'Working')->count();
    //     $predictionAmount = $predictionQuery->sum(DB::raw('COALESCE(price, 0)'));
    
    //     // Initialize $salesTeamProjectCount to avoid undefined variable error
    //     $salesTeamProjectCount = 0;
    //     if ($loggedInUser->hasAnyRole(['Sales Team', 'Sales Team Manager'])) {
    //         $salesTeamProjectCount = Project::where(function ($q) use ($loggedInUser) {
    //             $q->where('created_by', $loggedInUser->id)
    //               ->orWhere('sales_person_id', $loggedInUser->id);
    //         })
    //             ->whereYear('created_at', now()->year)
    //             ->count();
    //         Log::debug('Sales Team Project Count for User ID: ' . $loggedInUser->id, ['count' => $salesTeamProjectCount]);
    //     } elseif ($loggedInUser->hasAnyRole(['Admin', 'HR'])) {
    //         $salesTeamProjectCount = Project::whereYear('created_at', now()->year)
    //             ->count();
    //         Log::debug('Admin/HR Project Count for User ID: ' . $loggedInUser->id, ['count' => $salesTeamProjectCount]);
    //     }
    
    //     // Amount Received for the selected year
    //     if ($request->filled('pending_payment') && $request->pending_payment == '1') {
    //         $amountReceived = 0;
    //     } else {
    //         if ($loggedInUser->hasAnyRole(['Admin', 'HR'])) {
    //             $amountReceived = DB::table('project_payments')
    //                 ->join('projects', 'project_payments.project_id', '=', 'projects.id')
    //                 ->whereIn('projects.id', $amountReceivedQuery->pluck('id'))
    //                 ->whereYear('project_payments.created_at', $projectYear ?: now()->year)
    //                 ->sum('project_payments.payment_amount');
    //         } else {
    //             $filteredProjectIds = $amountReceivedQuery->pluck('id');
    //             $amountReceived = DB::table('project_payments')
    //                 ->whereIn('project_id', $filteredProjectIds)
    //                 ->whereYear('project_payments.created_at', $projectYear ?: now()->year)
    //                 ->sum('payment_amount');
    //         }
    //     }
    
    //     // Admin/HR: Total amounts for all projects (across all years)
    //     $totalAmountReceived = $loggedInUser->hasAnyRole(['Admin', 'HR'])
    //         ? DB::table('project_payments')->sum('payment_amount')
    //         : 0;
    //     $totalPredictionAmount = $loggedInUser->hasAnyRole(['Admin', 'HR'])
    //         ? Project::sum('price')
    //         : 0;
    
    //     // Define $selectedYear for the view
    //     $selectedYear = $projectYear ?: now()->year;
    
    //     // Dropdowns
    //     $mainCategories = ProjectCategory::with('subcategories')->whereNull('parent_id')->get();
    //     $countries = Country::all();
    //     $taskPhases = TaskPhase::all();
    //     $employees = User::orderBy('name', 'asc')->get();
    //     $departments = Department::all();
    //     $projectManagers = User::whereHas('roles', fn($q) => $q->where('name', 'Project Manager'))->orderBy('name')->get();
    //     $salesPersons = User::whereHas('roles', function ($q) {$q->whereIn('name', ['Sales Team', 'Sales Team Manager']);})->orderBy('name')->get();        
    //     $teamLeads = User::role('Team Lead')->orderBy('name', 'asc')->get();
    //     $users = User::with('roles', 'department')->get();
    //     $contentManagers = collect();
    //     if ($contentDept = Department::where('name', 'Content Department')->first()) {
    //         $contentManagers = User::whereHas('roles', fn($q) => $q->where('name', 'Project Manager'))
    //                                ->where('department_id', $contentDept->id)->get();
    //     }
    
    //     return view('projects.index', compact(
    //         'paginatedProjects',
    //         'mainCategories',
    //         'countries',
    //         'projectManagers',
    //         'taskPhases',
    //         'employees',
    //         'salesPersons',
    //         'departments',
    //         'totalProjects',
    //         'activeProjects',
    //         'predictionAmount',
    //         'amountReceived',
    //         'teamLeads',
    //         'users',
    //         'contentManagers',
    //         'loggedInUser',
    //         'totalAmountReceived',
    //         'totalPredictionAmount',
    //         'selectedYear',
    //         'salesTeamProjectCount'
    //     ));
    // }









    
    public function getSubcategories($parent_id) {
        $subcategories = ProjectCategory::where('parent_id', $parent_id)->get();
        return response()->json($subcategories);
    }    
    public function store(Request $request)
{
    
    $validated = $request->validate([
        'name_or_url' => 'required|string|max:255',
        'dashboard_url' => 'nullable|url',
        'description' => 'nullable|string',
        'project_grade' => 'nullable|in:A,AA,AAA',
        'business_type' => 'nullable|in:Startup,Small,Mid-level,Enterprise',
        'project_category_id' => 'nullable|exists:project_categories,id',
        'project_subcategory_id' => 'nullable|exists:project_categories,id',
        'country_id' => 'required|exists:countries,id',
        'task_phases' => 'nullable|array',
        'project_manager_id' => 'required|exists:users,id',
        'team_lead_id' => 'nullable|exists:users,id',
        'assign_main_employee_id' => 'nullable|exists:users,id',
        'price' => 'nullable|numeric',
        'estimated_hours' => 'nullable|integer',
        'project_type' => 'nullable|in:Ongoing,One-time',
        'upwork_project_type' => 'nullable|in:Hourly,Fixed',
        'client_type' => 'nullable|in:New Client,Old Client',
        'report_type' => 'nullable|in:Weekly,Bi-Weekly,Monthly',
        'project_month' => 'nullable|date',
        'sales_person_id' => 'nullable|exists:users,id',
        'department_id' => 'required|exists:departments,id',
        'client_name' => 'nullable|string|max:255',
        'client_email' => 'nullable|email|max:255',
        'client_other_info' => 'nullable|string',
        'upsell_employee_id' => 'nullable|exists:users,id',
        'content_manager_id' => 'nullable|exists:users,id',
        'additional_employees' => 'nullable|array',
    ]);

    $validated['project_status'] = 'Working';
    $validated['created_by'] = Auth::id();
    $validated['source_type'] = 'internal';

    // Handle content details (for SEO/Content projects)
    $contentDetails = [];
    if ($request->has('content_type') && $request->has('content_quantity')) {
        $types = $request->input('content_type');
        $quantities = $request->input('content_quantity');

        foreach ($types as $index => $type) {
            $quantity = $quantities[$index] ?? 0;
            $contentDetails[] = [
                'type' => $type,
                'quantity' => (int)$quantity,
            ];
        }
    }

    if (!empty($contentDetails)) {
        $validated['content_details'] = json_encode($contentDetails);
    }

    if (!empty($validated['additional_employees']) && is_array($validated['additional_employees'])) {
        $validated['additional_employees'] = array_map('intval', $validated['additional_employees']);
    }
    
    
    // Save project
    $project = Project::create($validated);

    // ✅ Send mail to main assigned employee
    // if ($project->assign_main_employee_id) {
    //     $employee = User::find($project->assign_main_employee_id);

    //     if ($employee && $employee->email) {
    //         $project->employee = $employee;
    //         Mail::to($employee->email)->send(new ProjectAssignedMail($project));
    //     }
    // }

    // // ✅ Send mail to additional employees
    // if (!empty($project->additional_employees)) {
    //     // Decode only if stored as string
    //     $additionalEmployeeIds = is_string($project->additional_employees)
    //         ? json_decode($project->additional_employees, true)
    //         : $project->additional_employees;

    //     if (is_array($additionalEmployeeIds)) {
    //         foreach ($additionalEmployeeIds as $employeeId) {
    //             $additionalEmployee = User::find($employeeId);

    //             if ($additionalEmployee && $additionalEmployee->email) {
    //                 $project->employee = $additionalEmployee;
    //                 Mail::to($additionalEmployee->email)->send(new ProjectAssignedMail($project));
    //             }
    //         }
    //     }
    // }

    return response()->json(['success' => 'Project created successfully.']);
}

    public function edit(Project $project) {
        return response()->json($project);
    }

    public function update(Request $request, $id)
    {
        $project = Project::find($id);
        $saleProject = SaleTeamProject::find($id);
    
        // Step 1: Check authorization
        if (!$project && $saleProject) {
            $assigned = AssignedProject::where('project_id', $id)
                ->where('project_manager_id', auth()->id())
                ->first();
            if (!$assigned) {
                return redirect()->route('projects.index')->with('error', 'Unauthorized to update this project.');
            }
        } elseif (!$project && !$saleProject) {
            return redirect()->route('projects.index')->with('error', 'Project not found.');
        }
    
        // Step 2: Validate
        $validated = $request->validate([
            'name_or_url' => 'required|string|max:255',
            'dashboard_url' => 'nullable|url',
            'description' => 'nullable|string',
            'project_grade' => 'nullable|in:A,AA,AAA',
            'business_type' => 'nullable|in:Startup,Small,Mid-level,Enterprise',
            'project_category_id' => 'nullable|exists:project_categories,id',
            'project_subcategory_id' => 'nullable|exists:project_categories,id',
            'country_id' => 'required|exists:countries,id',
            'task_phases' => 'nullable|array',
            'project_manager_id' => 'required|exists:users,id',
            'assign_main_employee_id' => 'nullable|exists:users,id',
            'team_lead_id' => 'nullable|exists:users,id',
            'price' => 'nullable|numeric',
            'estimated_hours' => 'nullable|integer',
            'project_type' => 'nullable|in:Ongoing,One-time',
            'upwork_project_type' => 'nullable|in:Hourly,Fixed',
            'client_type' => 'nullable|in:New Client,Old Client,new client,old client',
            'report_type' => 'nullable|in:Weekly,Bi-Weekly,Monthly',
            'project_month' => 'nullable|date',
            'sales_person_id' => 'nullable|exists:users,id',
            'department_id' => 'required|exists:departments,id',
            'client_name' => 'nullable|string|max:255',
            'client_email' => 'nullable|email|max:255',
            'client_other_info' => 'nullable|string',
            'additional_employees' => 'nullable|array',
        'additional_employees.*' => 'exists:users,id',
            'upsell_employee_id' => 'nullable|exists:users,id',
            'content_manager_id' => 'nullable|exists:users,id',
            'content_type' => 'nullable|array',
            'content_quantity' => 'nullable|array',
        ]);
    
        if (!empty($validated['client_type'])) {
            $validated['client_type'] = ucwords(strtolower($validated['client_type']));
        }
    
        // Process additional_employees to match store method
    if (!empty($validated['additional_employees']) && is_array($validated['additional_employees'])) {
        $validated['additional_employees'] = array_map('intval', $validated['additional_employees']);
    } else {
        $validated['additional_employees'] = [];
    }
    // Log the input data (fix array to string conversion)
    \Log::info('Updating project ID ' . $id . ' with additional_employees: ', ['additional_employees' => $validated['additional_employees']]);
        // Step 3: Update logic
        if ($project) {
            $project->update(array_merge($validated, ['project_status' => 'Working']));
        } elseif ($saleProject) {
            // Update SaleTeamProject fields, including additional_employees
            $saleProject->update([
                'name_or_url' => $validated['name_or_url'],
                'dashboard_url' => $validated['dashboard_url'] ?? null,
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'] ?? null,
                'project_type' => $validated['project_type'] ?? null,
                'client_type' => $validated['client_type'] ?? null,
                'business_type' => $validated['business_type'] ?? null,
                'project_month' => $validated['project_month'] ?? null,
                'country_id' => $validated['country_id'],
                'client_name' => $validated['client_name'] ?? null,
                'sales_person_id' => $validated['sales_person_id'],
                'department_id' => $validated['department_id'],
                'client_email' => $validated['client_email'] ?? null,
                'client_other_info' => $validated['client_other_info'] ?? null,
                'additional_employees' => $validated['additional_employees'], // Add this
            ]);
    
            $existingProject = Project::where('id', $saleProject->id)->first();
            if (!$existingProject) {
                if (empty($validated['assign_main_employee_id'])) {
                    return redirect()->back()->withInput()->withErrors([
                        'assign_main_employee_id' => 'Main employee assignment is required on first assignment.'
                    ]);
                }
                $project = Project::create(array_merge(
                    [
                        'id' => $saleProject->id,
                        'created_by' => $saleProject->created_by ?? auth()->id(),
                        'project_status' => 'Working',
                        'source_type' => 'sale_team',
                    ],
                    $validated
                ));
            } else {
                $existingProject->update(array_merge($validated, [
                    'project_status' => 'Working',
                    'price_usd' => $existingProject->price_usd ?? $saleProject->price,
                    'price' => $validated['price'],
                ]));
                $project = $existingProject;
            }
        }
    
        // Step 4: Save content details JSON
        $contentDetails = [];
        foreach ($request->input('content_type', []) as $i => $type) {
            $quantity = $request->input('content_quantity')[$i] ?? 0;
            if (!empty($type)) {
                $contentDetails[] = ['type' => $type, 'quantity' => (int) $quantity];
            }
        }
    
        if (!empty($contentDetails)) {
            $project->update([
                'content_details' => json_encode($contentDetails),
            ]);
        }
    
        // Step 5: Send emails to main and additional employees
        // if ($project->assign_main_employee_id) {
        //     $employee = User::find($project->assign_main_employee_id);
        //     if ($employee && $employee->email) {
        //         $project->employee = $employee;
        //         Mail::to($employee->email)->send(new ProjectAssignedMail($project));
        //     }
        // }
    
        // if (!empty($project->additional_employees)) {
        //     $additionalEmployeeIds = is_string($project->additional_employees)
        //         ? json_decode($project->additional_employees, true)
        //         : $project->additional_employees;
        //     if (is_array($additionalEmployeeIds)) {
        //         foreach ($additionalEmployeeIds as $employeeId) {
        //             $additionalEmployee = User::find($employeeId);
        //             if ($additionalEmployee && $additionalEmployee->email) {
        //                 $project->employee = $additionalEmployee;
        //                 Mail::to($additionalEmployee->email)->send(new ProjectAssignedMail($project));
        //             }
        //         }
        //     }
        // }
    
        return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
    }
    // public function destroy(Project $project) 
    // {
    //     $project->delete();
    //     return response()->json(['success' => 'Project deleted successfully.']);
    // }
    
    public function editStatus($id)
{
    $project = Project::findOrFail($id);
    return view('projects.status', compact('project'));
}
public function duplicate(Project $project)
{
    $newProject = $project->replicate();
    $newProject->name_or_url = $project->name_or_url . ' (Copy)';
    $newProject->created_by = auth()->id();
    $newProject->project_status = 'Working';
    $newProject->created_at = now();
    $newProject->updated_at = now();

    if ($project->content_details) {
        $newProject->content_details = $project->content_details;
    }

    $newProject->save();

    if ($project->additional_employees && is_array($project->additional_employees)) {
        $newProject->additional_employees = $project->additional_employees;
        $newProject->save();
    }

    return redirect()->route('projects.index')->with('success', 'Project duplicated successfully.');
}



public function updateStatus(Request $request, $id)
{
    $project = Project::findOrFail($id);

    $validated = $request->validate([
        'project_status' => 'string',
        'status_date' => 'nullable|date',
        'reason_description' => 'nullable|string',
        'can_client_rehire' => 'nullable|string|in:Yes,No',
        'rehire_date' => 'nullable|date',
    ]);

    $validated['closed_by'] = auth()->id();
    $project->update($validated);

    // 🔔 Step 5: Send Mail Notification
    // $emails = [
    //     'deeksha.seodiscovery12@gmail.com', // CEO
    //     'rajbirseo@gmail.com',   // CTO
    // ];

    // if ($project->salesPerson && $project->salesPerson->email) {
    //     $emails[] = $project->salesPerson->email;
    // }

    // foreach ($emails as $email) {
    //     Mail::to($email)->send(new ProjectStatusUpdated($project));
    // }

    return redirect()->route('projects.status', $project->id)
        ->with('success', 'Project status updated and notifications sent.');
}

public function closed(Request $request)
{
    $user = auth()->user();
    
    // Authentication check
    if (!$user) {
        return redirect()->route('login')->with('error', 'Please log in to access this page.');
    }

    // Authorization
    if (!$user->hasAnyRole(['Admin', 'HR', 'Project Manager']) && !$user->hasRole('SpecialRole')) {
        return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
    }

    // Base query
    $query = Project::with(['projectManager', 'salesPerson', 'department', 'country', 'closedByUser', 'payments'])
        ->whereIn('project_status', ['Complete', 'Closed']);

    // Role-based filter for Project Managers
    if ($user->hasRole('Project Manager')) {
        $query->where('project_manager_id', $user->id);
    }

    // Search filter
    if ($request->filled('search')) {
        $search = trim($request->search);
        Log::info('Search term applied: ' . $search);
        $query->where(function ($q) use ($search) {
            $q->where('name_or_url', 'like', "%$search%")
              ->orWhere('client_name', 'like', "%$search%")
              ->orWhere('client_email', 'like', "%$search%")
              ->orWhere('client_other_info', 'like', "%$search%")
              ->orWhere('reason_description', 'like', "%$search%");
        });
    }

    // Filters
    if ($request->filled('report_year')) {
        $query->whereYear('status_date', $request->report_year);
    }

    if ($request->filled('project_month') && $request->project_month !== 'ALL') {
        try {
            $month = Carbon::parse($request->project_month)->format('Y-m');
            $query->where('status_date', 'like', "$month%");
        } catch (\Exception $e) {
            Log::error('Invalid project_month: ' . $request->project_month);
        }
    }

    if ($request->filled('project_type') && $request->project_type !== 'ALL') {
        $query->where('project_type', $request->project_type);
    }

    // Summary metrics
    $filteredProjects = (clone $query)->get();
    $totalClosed = $filteredProjects->count();
    $totalAmount = $filteredProjects->sum('price');
    $totalReceived = $filteredProjects->sum(fn ($proj) => $proj->payments->sum('payment_amount'));
    $avgRating = $filteredProjects->avg('rating') ?? 0;

    // Paginate results
    $perPage = $request->per_page ?? 10;
    $projects = $query->orderByDesc('status_date')->paginate($perPage);

    // Log results
    Log::info('Projects found: ' . $projects->total());

    return view('projects.closed', compact(
        'projects',
        'totalClosed',
        'totalAmount',
        'totalReceived',
        'avgRating'
    ));
}

public function paused(Request $request)
{
    $user = auth()->user();

    if (!$user->hasAnyRole(['Admin', 'HR', 'Project Manager'])) {
        return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
    }

    $query = Project::with(['projectManager', 'salesPerson', 'department', 'country', 'closedByUser', 'payments'])
        ->where('project_status', 'Paused');

    // Role-based filter
    if ($user->hasRole('Project Manager')) {
        $query->where(function ($q) use ($user) {
            $q->where('project_manager_id', $user->id)
              ->orWhere('department_id', $user->department_id);
        });
    }

    // Apply filters
    if ($request->filled('project_month')) {
        $month = \Carbon\Carbon::parse($request->project_month)->format('Y-m');
        $query->where('status_date', 'like', "$month%");
    }

    if ($request->filled('project_type')) {
        $query->where('project_type', $request->project_type);
    }

    // Add search functionality
    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
            $q->where('name_or_url', 'like', "%$search%")
              ->orWhere('business_type', 'like', "%$search%")
              ->orWhere('project_grade', 'like', "%$search%")
              ->orWhere('client_name', 'like', "%$search%")
              ->orWhere('client_email', 'like', "%$search%")
              ->orWhere('client_other_info', 'like', "%$search%")
              ->orWhereHas('projectManager', function ($q) use ($search) {
                  $q->where('name', 'like', "%$search%");
              })
              ->orWhereHas('salesPerson', function ($q) use ($search) {
                  $q->where('name', 'like', "%$search%");
              })
              ->orWhereHas('department', function ($q) use ($search) {
                  $q->where('name', 'like', "%$search%");
              })
              ->orWhereHas('closedByUser', function ($q) use ($search) {
                  $q->where('name', 'like', "%$search%");
              });
        });
    }

    // Summary Cards Data
    $filteredProjects = (clone $query)->get();
    $totalPaused = $filteredProjects->count();
    $totalAmount = $filteredProjects->sum('price');
    $totalReceived = $filteredProjects->sum(fn ($proj) => $proj->payments->sum('payment_amount'));

    // Handle entries per page
    $perPage = $request->input('entries', 20); // Default to 20 if not specified
    $projects = $query->orderByDesc('status_date')->paginate($perPage);
    // Identify overdue projects for Project Managers
    $overdueProjectIds = [];
    if ($user->hasRole('Project Manager')) {
        $overdueProjectIds = Project::where('project_status', 'Paused')
            ->where('project_manager_id', $user->id)
            ->where(function ($q) {
                $q->whereNull('last_followup_at')
                  ->orWhere('last_followup_at', '<=', Carbon::now()->subDays(15));
            })
            ->pluck('id')
            ->toArray();
    }

    return view('projects.paused', compact(
        'projects',
        'totalPaused',
        'totalAmount',
        'totalReceived',
        'overdueProjectIds'
    ));
}

public function show($id)
{
    $project = Project::with([
        'projectManager', 
        'salesPerson', 
        'department', 
        'country', 
        'projectCategory', 
        'projectSubCategory',
    ])->findOrFail($id);

    $dsrs = Dsr::with('user')
        ->where('project_id', $id)
        ->orderBy('created_at', 'desc')
        ->get();
        $receivedMoney = ProjectPayment::where('project_id', $project->id)->sum('payment_amount');
$spentHours = Dsr::where('project_id', $project->id)->sum('hours');

    return view('projects.show', compact('project','dsrs','receivedMoney','spentHours'));
}
public function editPage($id)
{
    $project = Project::find($id);

    // If not found in internal projects, check sale team assignment
    if (!$project) {
        $assigned = AssignedProject::where('project_id', $id)
            ->where('project_manager_id', auth()->id())
            ->first();

        if ($assigned) {
            $saleProject = SaleTeamProject::find($id);

            if ($saleProject) {
                // Create dummy project-like object from SaleTeamProject
                $project = (object)[
                    'id' => $saleProject->id,
                    'name_or_url' => $saleProject->name_or_url,
                    'dashboard_url' => $saleProject->dashboard_url,
                    'description' => $saleProject->description,
                    'project_manager_id' => auth()->id(),
                    'assign_main_employee_id' => null,
                    'created_by' => $saleProject->created_by ?? auth()->id(),
                    'sales_person_id' => $saleProject->sales_person_id,
                    'department_id' => $saleProject->department_id,
                    'country_id' => $saleProject->country_id,
                    'price' => $saleProject->price,
                    'price_usd' => $saleProject->price,
                    'project_status' => 'Working',

                    // Optional fields defaulted to null
                    'project_grade' => null,
                    'business_type' => null,
                    'project_category_id' => null,
                    'project_subcategory_id' => null,
                    'estimated_hours' => null,
                    'project_type' => null,
                    'upwork_project_type' => null,
                    'client_type' => null,
                    'report_type' => null,
                    'project_month' => null,
                    'client_name' => null,
                    'client_email' => null,
                    'client_other_info' => null,
                    'team_lead_id' => null,
                    'upsell_employee_id' => null,
                    'content_manager_id' => null,
                    'content_details' => null,
                    'additional_employees' => [], // required for edit form checkboxes
                ];
            } else {
                return redirect()->route('projects.index')->with('error', 'Sale project not found.');
            }
        } else {
            return redirect()->route('projects.index')->with('error', 'Unauthorized access.');
        }
    }

    // Decode additional_employees safely (for internal project)
    if (isset($project->additional_employees) && is_string($project->additional_employees)) {
        $decoded = json_decode($project->additional_employees, true);
        $project->additional_employees = is_array($decoded) ? $decoded : [];
    } elseif (!isset($project->additional_employees)) {
        $project->additional_employees = [];
    }

    return view('projects.edit', [
        'project' => $project,
        'mainCategories' => ProjectCategory::whereNull('parent_id')->get(),
        'subCategories' => ProjectCategory::whereNotNull('parent_id')->get(),
        'countries' => Country::all(),
        'taskPhases' => TaskPhase::all(),
        'teamLeads' => User::role('Team Lead')->get(),
        'users' => User::all(),
        'departments' => Department::all(),
        'projectManagers' => User::role('Project Manager')->get(),
        'employees' => User::all(),
        'salesPersons' => User::role('Sales Team')->get(),
    ]);
}
//old code function
// public function pendingInvoices(Request $request)
// {
//     $loggedInUser = auth()->user();

//     if ($loggedInUser->hasRole('Employee')) {
//         return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
//     }

//     // Base query
//     $query = Project::with([
//         'projectManager',
//         'salesPerson',
//         'department',
//         'country',
//         'projectCategory',
//         'projectSubCategory',
//         'upsellEmployee',
//         'contentManager',
//     ])
//     ->leftJoin('project_payments', 'projects.id', '=', 'project_payments.project_id')
//     ->select('projects.*', \DB::raw('COALESCE(SUM(project_payments.payment_amount), 0) as total_paid'))
//     ->groupBy('projects.id')
//     ->havingRaw('projects.price > total_paid'); // unpaid OR partial payment

//     // 🔹 Role-based data restriction
//     if (! $loggedInUser->hasAnyRole(['Admin', 'HR'])) {
//         if ($loggedInUser->hasRole('Project Manager')) {
//             $query->where('projects.department_id', $loggedInUser->department_id);
//         } elseif ($loggedInUser->hasRole('Team Lead')) {
//             $query->where('projects.team_lead_id', $loggedInUser->id);
//         } else {
//             $query->where(function ($q) use ($loggedInUser) {
//                 $q->where('projects.created_by', $loggedInUser->id);
//                 if ($loggedInUser->hasRole('Sales Team')) {
//                     $q->orWhere('projects.sales_person_id', $loggedInUser->id);
//                 }
//             });
//         }
//     }

//     // 🔹 Filters
//     if ($request->filled('project_manager_id')) {
//         $query->where('projects.project_manager_id', $request->project_manager_id);
//     }

//     if ($request->filled('department_id')) {
//         $query->where('projects.department_id', $request->department_id);
//     }

//     // ✅ Fix for project month filter
//     if ($request->filled('project_month')) {
//         $month = \Carbon\Carbon::parse($request->project_month)->format('m');
//         $year  = \Carbon\Carbon::parse($request->project_month)->format('Y');
//         $query->whereMonth('projects.created_at', $month)
//               ->whereYear('projects.created_at', $year);
//     }

//     // 🔹 Clone for total pending calculation
//     $totalPendingAmount = (clone $query)->get()->sum(function ($project) {
//         return $project->price - $project->total_paid;
//     });

//     // 🔹 Pagination with filters preserved
//     $projects = $query->orderBy('projects.created_at', 'desc')
//                       ->paginate(10)
//                       ->appends($request->all()); // ✅ Keep filter params

//     // 🔹 Dropdown data
//     $mainCategories   = ProjectCategory::with('subcategories')->whereNull('parent_id')->get();
//     $countries        = Country::all();
//     $taskPhases       = TaskPhase::all();
//     $employees        = User::all();
//     $departments      = Department::all();
//     $projectManagers  = User::role('Project Manager')->get();
//     $salesPersons     = User::role('Sales Team')->get();
//     $teamLeads        = User::role('Team Lead')->get();
//     $users            = User::with('roles', 'department')->get();

//     $contentDepartment = Department::where('name', 'Content Department')->first();
//     $contentManagers = $contentDepartment
//         ? User::role('Project Manager')->where('department_id', $contentDepartment->id)->get()
//         : collect();

//     return view('projects.pending-invoices', compact(
//         'projects',
//         'mainCategories',
//         'countries',
//         'projectManagers',
//         'taskPhases',
//         'employees',
//         'salesPersons',
//         'departments',
//         'teamLeads',
//         'users',
//         'contentManagers',
//         'loggedInUser',
//         'totalPendingAmount'
//     ));
// }



// new code
public function pendingInvoices(Request $request)
{
    $loggedInUser = auth()->user();

    if ($loggedInUser->hasRole('Employee')) {
        return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
    }

    // Base query (FIXED)
    $query = Project::with([
            'projectManager',
            'salesPerson',
            'department',
            'country',
            'projectCategory',
            'projectSubCategory',
            'upsellEmployee',
            'contentManager',
        ])
        ->leftJoinSub(
            \DB::table('project_payments')
                ->select('project_id', \DB::raw('SUM(payment_amount) as total_paid'))
                ->groupBy('project_id'),
            'payments',
            'projects.id',
            '=',
            'payments.project_id'
        )
        ->select(
            'projects.*',
            \DB::raw('COALESCE(payments.total_paid, 0) as total_paid')
        )
        ->whereRaw('projects.price > COALESCE(payments.total_paid, 0)'); // unpaid OR partial payment

    // 🔹 Role-based data restriction
    if (! $loggedInUser->hasAnyRole(['Admin', 'HR'])) {
        if ($loggedInUser->hasRole('Project Manager')) {
            $query->where('projects.department_id', $loggedInUser->department_id);
        } elseif ($loggedInUser->hasRole('Team Lead')) {
            $query->where('projects.team_lead_id', $loggedInUser->id);
        } else {
            $query->where(function ($q) use ($loggedInUser) {
                $q->where('projects.created_by', $loggedInUser->id);
                if ($loggedInUser->hasRole('Sales Team')) {
                    $q->orWhere('projects.sales_person_id', $loggedInUser->id);
                }
            });
        }
    }

    // 🔹 Filters
    if ($request->filled('project_manager_id')) {
        $query->where('projects.project_manager_id', $request->project_manager_id);
    }

    if ($request->filled('department_id')) {
        $query->where('projects.department_id', $request->department_id);
    }

    // ✅ Fix for project month filter
    if ($request->filled('project_month')) {
        $month = \Carbon\Carbon::parse($request->project_month)->format('m');
        $year  = \Carbon\Carbon::parse($request->project_month)->format('Y');
        $query->whereMonth('projects.created_at', $month)
              ->whereYear('projects.created_at', $year);
    }

    // 🔹 Clone for total pending calculation
    $totalPendingAmount = (clone $query)->get()->sum(function ($project) {
        return $project->price - $project->total_paid;
    });

    // 🔹 Pagination with filters preserved
    $projects = $query->orderBy('projects.created_at', 'desc')
                      ->paginate(10)
                      ->appends($request->all());

    // 🔹 Dropdown data
    $mainCategories   = ProjectCategory::with('subcategories')->whereNull('parent_id')->get();
    $countries        = Country::all();
    $taskPhases       = TaskPhase::all();
    $employees        = User::all();
    $departments      = Department::all();
    $projectManagers  = User::role('Project Manager')->get();
    $salesPersons     = User::role('Sales Team')->get();
    $teamLeads        = User::role('Team Lead')->get();
    $users            = User::with('roles', 'department')->get();

    $contentDepartment = Department::where('name', 'Content Department')->first();
    $contentManagers = $contentDepartment
        ? User::role('Project Manager')->where('department_id', $contentDepartment->id)->get()
        : collect();

    return view('projects.pending-invoices', compact(
        'projects',
        'mainCategories',
        'countries',
        'projectManagers',
        'taskPhases',
        'employees',
        'salesPersons',
        'departments',
        'teamLeads',
        'users',
        'contentManagers',
        'loggedInUser',
        'totalPendingAmount'
    ));
}
public function destroy(Project $project)
{
    $loggedInUser = auth()->user();

    // Simple authorization: Only Admin/HR or creator can delete
    if (!$loggedInUser->hasAnyRole(['Admin', 'HR']) && $project->created_by !== $loggedInUser->id) {
        return redirect()->back()->with('error', 'Unauthorized to delete this project.');
    }

    $project->delete(); // Simple delete - cascades handle related records automatically

    return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
}

}
