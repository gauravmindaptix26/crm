<?php

namespace App\Http\Controllers;

use App\Models\WebDevPmDsr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WebDevPmDsrController extends Controller
{
    public function showDailyForm()
    {
        $userId = Auth::id();
        $today = Carbon::today()->format('Y-m-d');

        $alreadySubmitted = WebDevPmDsr::where('pm_id', $userId)
            ->where('report_date', $today)
            ->where('type', 'daily')
            ->exists();

        return view('web_dev_pm_dsr.daily', compact('alreadySubmitted', 'today'));
    }

    public function storeDaily(Request $request)
    {
        $today = Carbon::today()->format('Y-m-d');

        // Prevent double submission
        if (WebDevPmDsr::where('pm_id', Auth::id())
            ->where('report_date', $today)
            ->where('type', 'daily')
            ->exists()) {
            return redirect()->route('web.dev.pm.dsr.dashboard')
                ->with('info', 'You have already submitted your Daily DSR today!');
        }

        // === Validation Rules - ALL FIELDS REQUIRED EXCEPT FILES ===
        $rules = [
            'upwork_bids'                 => 'required|integer|min:0',
            'pph_bids'                    => 'required|integer|min:0',
            'fiverr_maintain'             => 'required|string|max:2000',
            'dribbble_jobs'               => 'required|string|max:2000',
            'online_jobs_apply'           => 'required|string|max:3000',
            'old_clients_design'          => 'required|string|max:3000',
            'old_leads_ask_work'          => 'required|string|max:2000',
            'client_communication'        => 'required|string|max:3000',
            'current_client_more_work'    => 'required|string|max:3000',
            'project_completion_on_time'  => 'required|string|max:2000',
            'meet_pm_more_work'           => 'required|string|max:2000',

            // Files are OPTIONAL but validated if uploaded
            'marketplace_files'           => 'nullable|array|max:10',
            'marketplace_files.*'         => 'file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB

            // Hours - required (at least 0)
            'hours.marketplace_job'               => 'required|numeric|min:0|max:24',
            'hours.old_client_management'         => 'required|numeric|min:0|max:24',
            'hours.project_coordinator_job'       => 'required|numeric|min:0|max:24',
        ];

        $messages = [
            'upwork_bids.required' => 'Upwork bids is required.',
            'pph_bids.required' => 'PPH bids is required.',
            'fiverr_maintain.required' => 'Fiverr account maintain details are required.',
            'dribbble_jobs.required' => 'Dribbble job applications are required.',
            'online_jobs_apply.required' => 'Online job applications are required.',
            'old_clients_design.required' => 'Old clients design work details are required.',
            'old_leads_ask_work.required' => 'Old leads follow-up is required.',
            'client_communication.required' => 'Client communication details are required.',
            'current_client_more_work.required' => 'Current client follow-up for more work is required.',
            'project_completion_on_time.required' => 'Project completion status is required.',
            'meet_pm_more_work.required' => 'Meeting with PM details are required.',

            'hours.marketplace_job.required' => 'Hours for Marketplace Job is required.',
            'hours.old_client_management.required' => 'Hours for Old Client Management is required.',
            'hours.project_coordinator_job.required' => 'Hours for Project Coordinator Job is required.',

            'marketplace_files.*.mimes' => 'Only JPG, JPEG, PNG, and PDF files are allowed.',
            'marketplace_files.*.max'   => 'Each file must not exceed 5MB.',
            'marketplace_files.max'     => 'Maximum 10 files can be uploaded.',
        ];

        $request->validate($rules, $messages);

        

        // === Handle Multiple File Uploads Safely ===
        $marketplaceFiles = [];

        if ($request->hasFile('marketplace_files')) {
            foreach ($request->file('marketplace_files') as $file) {
                if ($file->isValid()) {
                    $path = $file->store('dsr-files/web-dev', 'public');
                    $marketplaceFiles[] = [
                        'url'  => asset('storage/' . $path),
                        'name' => $file->getClientOriginalName(),
                        'size' => round($file->getSize() / 1024, 2) . ' KB',
                    ];
                }
            }
        }

        $marketplaceFilesJson = !empty($marketplaceFiles) ? json_encode($marketplaceFiles) : null;

        // === Calculate Rating (1 point per required field filled) ===
        $rating = 11; // Total required fields: 11 text/number + 3 hours = 14 → but cap at 10
        $rating = min(10, $rating);

        // === Save Data ===
        $data = $request->only([
            'upwork_bids', 'pph_bids', 'fiverr_maintain', 'dribbble_jobs', 'online_jobs_apply',
            'old_clients_design', 'old_leads_ask_work', 'client_communication',
            'current_client_more_work', 'project_completion_on_time', 'meet_pm_more_work'
        ]);

        $data['pm_id'] = Auth::id();
        $data['report_date'] = $today;
        $data['type'] = 'daily';
        $data['marketplace_files'] = $marketplaceFilesJson;
        $data['task_hours'] = json_encode($request->input('hours', []));
        $data['rating'] = $rating;

        WebDevPmDsr::create($data);

        return redirect()
            ->route('web.dev.pm.dsr.dashboard')
            ->with('success', "Daily DSR submitted successfully! Your rating: {$rating}/10");
    }
}