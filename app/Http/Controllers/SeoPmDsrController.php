<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SeoPmDsr;
use App\Models\Project;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SeoPmDsrController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware(function ($request, $next) {
            if (!auth()->user()?->hasRole('Project Manager')) {
                abort(403, 'Only Project Managers can access this page.');
            }
            return $next($request);
        });
    }

    

public function create()
{
    $userId = auth()->id();
    $today = today()->format('Y-m-d');

    // Daily: submitted today?
    $dailySubmitted = SeoPmDsr::where('pm_id', $userId)
        ->where('type', 'daily')
        ->where('report_date', $today)
        ->exists();

    // Weekly: submitted this week?
    $weeklySubmitted = SeoPmDsr::where('pm_id', $userId)
        ->where('type', 'weekly')
        ->whereRaw('YEARWEEK(report_date, 1) = YEARWEEK(CURDATE(), 1)')
        ->exists();

    // Monthly: submitted this month?
    $monthlySubmitted = SeoPmDsr::where('pm_id', $userId)
        ->where('type', 'monthly')
        ->whereRaw('MONTH(report_date) = MONTH(CURDATE())')
        ->whereRaw('YEAR(report_date) = YEAR(CURDATE())')
        ->exists();

    return view('seo_pm_dsr.form', compact(
        'today',
        'dailySubmitted',
        'weeklySubmitted',
        'monthlySubmitted' 
    ));
}

public function showDailyForm()
{
    $user = auth()->user();
    $userId = $user->id;

    $today = Carbon::today()->format('Y-m-d');

    $alreadySubmitted = SeoPmDsr::where('pm_id', $userId)
        ->where('report_date', $today)
        ->where('type', 'daily')
        ->exists();

    // === GET ASSIGNED PROJECTS ===
    $projectsQuery = Project::query();

    if ($user->hasRole('Team Lead') || $user->hasRole('Project Manager')) {
        $projectsQuery->where(function ($q) use ($userId) {
            $q->where('team_lead_id', $userId)
              ->orWhere('project_manager_id', $userId)
              ->orWhere('assign_main_employee_id', $userId)
              ->orWhereJsonContains('additional_employees', $userId)
              ->orWhereIn('id', function ($sub) use ($userId) {
                  $sub->select('project_id')
                      ->from('assigned_projects')
                      ->where('team_lead_id', $userId)
                      ->orWhere('project_manager_id', $userId)
                      ->orWhere('assigned_employee_id', $userId);
              });
        });
    }

    if ($user->hasRole('Employee')) {
        $projectsQuery->where(function ($q) use ($userId) {
            $q->where('assign_main_employee_id', $userId)
              ->orWhereJsonContains('additional_employees', $userId);
        });
    }

    // FIXED: Use 'name_or_url' instead of 'name' for ordering
    $assignedProjects = $projectsQuery
        ->with(['projectCategory', 'projectSubCategory', 'country'])
        ->orderBy('name_or_url')  // ← This column exists in your table
        ->get();

    return view('seo_pm_dsr.daily', compact('alreadySubmitted', 'today', 'assignedProjects'));
}

    // Store Daily Report

    public function storeDaily(Request $request)
    {
        $today = today()->format('Y-m-d');
    
        // Prevent double submission
        if (SeoPmDsr::where('pm_id', auth()->id())
            ->where('report_date', $today)
            ->where('type', 'daily')
            ->exists()) {
            return redirect()->route('seo.pm.dsr.dashboard')
                ->with('info', 'You have already submitted your Daily DSR today!');
        }
    
        $proofFields = [
            'follow_paused_clients',
            'follow_closed_clients',
            'upsell_clients',
            'referral_client',
            'checked_teammate_dsr',
            'audited_project'
        ];
    
        // === Restore files from session (so they don't disappear) ===
        foreach ($proofFields as $field) {
            if ($request->hasFile("{$field}_proof")) {
                $tempPath = $request->file("{$field}_proof")->store("temp-dsr", "public");
                session([
                    "temp_{$field}_proof" => $tempPath,
                    "temp_{$field}_proof_name" => $request->file("{$field}_proof")->getClientOriginalName()
                ]);
            }
    
            if ($request->input($field) === 'yes'
                && !$request->hasFile("{$field}_proof")
                && session()->has("temp_{$field}_proof")) {
    
                $tempFile = new \Illuminate\Http\File(storage_path('app/public/' . session("temp_{$field}_proof")));
                $uploadedFile = new \Illuminate\Http\UploadedFile(
                    $tempFile->getPathname(),
                    session("temp_{$field}_proof_name"),
                    null, null, true
                );
                $request->files->set("{$field}_proof", $uploadedFile);
            }
        }
    
        // === Validation – Proof is OPTIONAL ===
        $rules = [];
        foreach ($proofFields as $field) {
            $rules[$field] = 'required|in:yes,no';
            if ($request->input($field) === 'no') {
                $rules["{$field}_reason"] = 'required|string|max:1000';
            }
        }
    
        $rules += [
            'invoices_sent'       => 'required|integer|min:0',
            'invoices_pending'    => 'required|integer|min:0',
            'payment_followups'   => 'required|integer|min:0',
            'paused_today'        => 'required|integer|min:0',
            'restarted_today'     => 'required|integer|min:0',
            'closed_today'        => 'required|integer|min:0',
            'meetings_completed'  => 'required|integer|min:0',
            'production_projects' => 'nullable|array',  // Validate projects array
            'production_projects.*' => 'exists:projects,id'  // Each ID must exist in projects table
        ];
    
        $rules['payment_screenshots'] = 'nullable|array|max:10';
        $rules['payment_screenshots.*'] = 'file|mimes:jpg,jpeg,png,pdf|max:5120';
    
        $request->validate($rules);
    
        // === FINAL 10-POINT RATING SYSTEM ===
        $rating = 0.0;
    
        // 1. 6 main tasks – full point only if Yes + Proof
        $tasks = [
            'follow_paused_clients','follow_closed_clients','upsell_clients','referral_client',
            'checked_teammate_dsr','audited_project'
        ];
    
        foreach ($tasks as $t) {
            $answer = $request->input($t);
    
            if ($answer === 'yes') {
                if ($request->hasFile("{$t}_proof")) {
                    $rating += 1.0;     // Yes + Proof = full 1 point
                } else {
                    $rating += 0.5;     // Yes but no proof = half point
                }
            }
            elseif ($answer === 'no' && $request->filled("{$t}_reason")) {
                $rating += 0.5;         // No + Valid Reason = half point
            }
            // No reason = 0 points
        }
    
        // 2. Happy Things – 2 points if user filled ANYTHING in this section
        if ($request->filled('paused_today') || $request->filled('restarted_today') || $request->filled('closed_today')) {
            $rating += 2;
        }
    
        // 3. Invoices & Payments – 1 point if user filled ANYTHING
        if ($request->filled('invoices_sent') || $request->filled('invoices_pending') || $request->filled('payment_followups')) {
            $rating += 1;
        }
    
        // 4. Production Work – 1 point if user filled ANYTHING
        if ($request->filled('meetings_completed') || $request->filled('client_queries_resolved') || $request->filled('additional_tasks')) {
            $rating += 1;
        }
    
        $finalRating = min(10, round($rating, 1)); // Max 10, clean number like 8.5
    
        // === Save Proofs ===
        $proofs = [];
        foreach ($proofFields as $field) {
            $answer = $request->input($field);
    
            if ($answer === 'yes' && $request->hasFile("{$field}_proof")) {
                $file = $request->file("{$field}_proof");
                $path = $file->store('dsr-proofs', 'public');
                $proofs[$field] = [
                    'answer' => 'yes',
                    'proof'  => [
                        'type' => 'file',
                        'url'  => asset('storage/' . $path),
                        'name' => $file->getClientOriginalName()
                    ]
                ];
            } elseif ($answer === 'no') {
                $proofs[$field] = [
                    'answer' => 'no',
                    'proof'  => [
                        'type' => 'text',
                        'description' => $request->input("{$field}_reason", 'No reason given')
                    ]
                ];
            }
        }
    
        // === Handle Payment Screenshots ===
        $paymentScreenshots = [];
        if ($request->hasFile('payment_screenshots')) {
            foreach ($request->file('payment_screenshots') as $file) {
                $path = $file->store('dsr-payments', 'public');
                $paymentScreenshots[] = [
                    'url'  => asset('storage/' . $path),
                    'name' => $file->getClientOriginalName(),
                    'size' => round($file->getSize() / 1024, 2) . ' KB'
                ];
            }
        }
    
        // === Save Everything ===
        $data = $request->only([
            'invoices_sent', 'invoices_pending', 'payment_followups',
            'paused_today', 'restarted_today', 'closed_today',
            'meetings_completed', 'client_queries_resolved', 'additional_tasks'
        ]);
    
        foreach ($proofFields as $field) {
            $data[$field] = $request->input($field) === 'yes' ? 1 : 0;
        }
    
        // SAVE PRODUCTION PROJECTS AS JSON
        $data['production_projects'] = $request->has('production_projects') 
            ? json_encode($request->input('production_projects')) 
            : null;
    
        $data['task_hours'] = $request->input('hours', []);
        $data['pm_id'] = auth()->id();
        $data['report_date'] = $today;
        $data['type'] = 'daily';
        $data['proofs'] = !empty($proofs) ? json_encode($proofs) : null;
        $data['payment_screenshots'] = !empty($paymentScreenshots) ? json_encode($paymentScreenshots) : null;
        $data['rating'] = $finalRating; // RATING SAVED HERE
    
        SeoPmDsr::create($data);
    
        // Clean temp files
        foreach ($proofFields as $field) {
            if (session()->has("temp_{$field}_proof")) {
                \Storage::disk('public')->delete(session("temp_{$field}_proof"));
                session()->forget(["temp_{$field}_proof", "temp_{$field}_proof_name"]);
            }
        }
    
        return redirect()->route('seo.pm.dsr.dashboard')
            ->with('success', "Daily DSR submitted! Your rating: {$finalRating}/10");
    }
public function showWeeklyForm()
{
    $alreadySubmitted = auth()->user()->weeklyDsrs()
        ->whereYear('report_date', now()->year)
        ->whereRaw('WEEK(report_date, 1) = WEEK(NOW(), 1)')
        ->exists();

    $weekStart = now()->startOfWeek(Carbon::MONDAY)->format('d M');
    $weekEnd   = now()->endOfWeek(Carbon::SUNDAY)->format('d M Y');
    $weekRange = "$weekStart - $weekEnd";

    return view('seo_pm_dsr.weekly', compact('alreadySubmitted', 'weekRange'));
}
    // Store Weekly Report
    public function storeWeekly(Request $request)
    {
        // Prevent double submission in the same week
        $alreadySubmitted = auth()->user()->weeklyDsrs()
            ->whereYear('report_date', now()->year)
            ->whereRaw('WEEK(report_date, 1) = WEEK(NOW(), 1)')
            ->exists();
    
        if ($alreadySubmitted) {
            return back()->with('error', 'You have already submitted Weekly DSR for this week!');
        }
    
        // === Dynamic Proof Fields (same logic as Daily) ===
        $proofFields = [
            'updated_case_study',
            'collected_review',
            'seo_discovery_post',
            'weekly_team_session',
            'seo_video_shared'
        ];
    
        // Restore file from temp if user didn't re-upload (same as Daily)
        foreach ($proofFields as $field) {
            if ($request->hasFile("{$field}_proof")) {
                $tempPath = $request->file("{$field}_proof")->store("temp-dsr", "public");
                session([
                    "temp_{$field}_proof" => $tempPath,
                    "temp_{$field}_proof_name" => $request->file("{$field}_proof")->getClientOriginalName()
                ]);
            }
    
            if ($request->input($field) === 'yes'
                && !$request->hasFile("{$field}_proof")
                && session()->has("temp_{$field}_proof")) {
    
                $tempFile = new \Illuminate\Http\File(storage_path('app/public/' . session("temp_{$field}_proof")));
                $uploadedFile = new \Illuminate\Http\UploadedFile(
                    $tempFile->getPathname(),
                    session("temp_{$field}_proof_name"),
                    null, null, true
                );
                $request->files->set("{$field}_proof", $uploadedFile);
            }
        }
    
        // === Dynamic Validation Rules (same as Daily) ===
        $rules = [];
        $messages = [];
    
        foreach ($proofFields as $field) {
            $rules[$field] = 'required|in:yes,no';
            $messages["{$field}.required"] = "Please select Yes or No for " . ucwords(str_replace('_', ' ', $field));
    
            if ($request->input($field) === 'yes') {
                $rules["{$field}_proof"] = 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120';
                $messages["{$field}_proof.required"] = "Please upload proof for " . ucwords(str_replace('_', ' ', $field));
            } elseif ($request->input($field) === 'no') {
                $rules["{$field}_reason"] = 'required|string|max:1000';
                $messages["{$field}_reason.required"] = "Please explain why not for " . ucwords(str_replace('_', ' ', $field));
            }
        }
    
        // Optional description fields
        $rules += [
            'case_study_description'    => 'nullable|string|max:2000',
            'seo_discovery_description' => 'nullable|string|max:2000',
        ];
    
        $request->validate($rules, $messages);
    
        // === Save Proofs to JSON (exactly like Daily) ===
        $proofs = [];
        $rating = 0;
    foreach ($proofFields as $task) {
        if ($request->input($task) === 'yes' && $request->hasFile("{$task}_proof")) {
            $rating += 2;  // 2 points per task with proof
        }
    }
    $finalRating = min(10, $rating);
    
        foreach ($proofFields as $field) {
            $answer = $request->input($field);
    
            if ($answer === 'yes' && $request->hasFile("{$field}_proof")) {
                $file = $request->file("{$field}_proof");
                $path = $file->store('dsr-proofs/weekly', 'public');
                $proofs[$field] = [
                    'answer' => 'yes',
                    'proof'  => [
                        'type' => 'file',
                        'url'  => asset('storage/' . $path),
                        'name' => $file->getClientOriginalName()
                    ]
                ];
            } elseif ($answer === 'no') {
                $proofs[$field] = [
                    'answer' => 'no',
                    'proof'  => [
                        'type' => 'text',
                        'description' => $request->input("{$field}_reason")
                    ]
                ];
            }
        }
    
        // === Save to Database ===
        $data = $request->only([
            'case_study_description',
            'seo_discovery_description'
        ]);
    
        foreach ($proofFields as $field) {
            $data[$field] = $request->input($field) === 'yes' ? 1 : 0;
        }
    
        $data['pm_id'] = auth()->id();
        $data['report_date'] = now();
        $data['type'] = 'weekly';
        $data['proofs'] = !empty($proofs) ? json_encode($proofs) : null;
        $data['rating'] = $finalRating; // RATING SAVED
    
        SeoPmDsr::create($data);
    
        // Clean temp files
        foreach ($proofFields as $field) {
            if (session()->has("temp_{$field}_proof")) {
                Storage::disk('public')->delete(session("temp_{$field}_proof"));
                session()->forget(["temp_{$field}_proof", "temp_{$field}_proof_name"]);
            }
        }
    
        return back()->with('success', 'Weekly DSR submitted successfully with proof!');
    }
    public function showMonthlyForm()
{
    $alreadySubmitted = auth()->user()->monthlyDsrs()
        ->whereMonth('report_date', now()->month)
        ->whereYear('report_date', now()->year)
        ->exists();

    $monthName = now()->format('F Y'); // e.g., "December 2025"

    return view('seo_pm_dsr.monthly', compact('alreadySubmitted', 'monthName'));
}

public function storeMonthly(Request $request)
{
    // Prevent double submission in same month
    $alreadySubmitted = auth()->user()->monthlyDsrs()
        ->whereMonth('report_date', now()->month)
        ->whereYear('report_date', now()->year)
        ->exists();

    if ($alreadySubmitted) {
        return back()->with('error', 'You have already submitted Monthly DSR for this month!');
    }

    $proofFields = [
        'pr_placements',
        'guest_post_backlinking',
        'website_redesign',
        'blog_writing_seo',
        'virtual_assistant',
        'full_web_development',
        'crm_setup',
        'google_ads',
        'social_ads',
        'logo_redesign',
        'podcast_outreach',
        'video_testimonial',
        'google_reviews_service'
    ];

    // Restore files from temp if needed (same as Daily/Weekly)
    foreach ($proofFields as $field) {
        if ($request->hasFile("{$field}_proof")) {
            $tempPath = $request->file("{$field}_proof")->store('temp-dsr', 'public');
            session(["temp_{$field}_proof" => $tempPath, "temp_{$field}_proof_name" => $request->file("{$field}_proof")->getClientOriginalName()]);
        }

        if ($request->input($field) === 'yes' && !$request->hasFile("{$field}_proof") && session("temp_{$field}_proof")) {
            $tempFile = new \Illuminate\Http\File(storage_path('app/public/' . session("temp_{$field}_proof")));
            $uploadedFile = new \Illuminate\Http\UploadedFile(
                $tempFile->getPathname(),
                session("temp_{$field}_proof_name"),
                null, null, true
            );
            $request->files->set("{$field}_proof", $uploadedFile);
        }
    }

    // Dynamic validation
    $rules = [];
    foreach ($proofFields as $field) {
        $rules[$field] = 'required|in:yes,no';
        if ($request->input($field) === 'yes') {
            $rules["{$field}_proof"] = 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120';
        } elseif ($request->input($field) === 'no') {
            $rules["{$field}_reason"] = 'required|string|max:1000';
        }
    }
    $rules['opportunity_description'] = 'nullable|string|max:3000';
    $request->validate($rules);

    $rating = 0;
    foreach ($proofFields as $task) {
        if ($request->input($task) === 'yes' && $request->hasFile("{$task}_proof")) {
            $rating += 0.7692; // 13 tasks × 0.7692 ≈ 10 points
        }
    }
    $finalRating = min(10, round($rating, 1));

    // Build proofs JSON
    $proofs = [];
    foreach ($proofFields as $field) {
        if ($request->input($field) === 'yes' && $request->hasFile("{$field}_proof")) {
            $file = $request->file("{$field}_proof");
            $path = $file->store('dsr-proofs/monthly', 'public');
            $proofs[$field] = [
                'answer' => 'yes',
                'proof'  => [
                    'type' => 'file',
                    'url'  => asset('storage/' . $path),
                    'name' => $file->getClientOriginalName()
                ]
            ];
        } elseif ($request->input($field) === 'no') {
            $proofs[$field] = [
                'answer' => 'no',
                'proof'  => [
                    'type' => 'text',
                    'description' => $request->input("{$field}_reason")
                ]
            ];
        }
    }

    // Save
    auth()->user()->monthlyDsrs()->create([
        'report_date' => now(),
        'type' => 'monthly',
        'opportunity_description' => $request->opportunity_description,
        'proofs' => !empty($proofs) ? json_encode($proofs) : null,
        'rating' => $finalRating, // RATING SAVED HERE
        ...collect($proofFields)->mapWithKeys(fn($f) => [$f => $request->input($f) === 'yes' ? 1 : 0])->toArray()
    ]);

    // Clean temp
    foreach ($proofFields as $field) {
        if (session("temp_{$field}_proof")) {
            \Storage::disk('public')->delete(session("temp_{$field}_proof"));
            session()->forget(["temp_{$field}_proof", "temp_{$field}_proof_name"]);
        }
    }

    return back()->with('success', 'Monthly DSR submitted successfully!');
}
    
}