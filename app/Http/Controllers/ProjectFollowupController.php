<?php

namespace App\Http\Controllers;

use App\Mail\ProjectFollowupMail;
use App\Models\Project;
use App\Models\ProjectFollowup;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;


class ProjectFollowupController extends Controller {
    public function send(Request $request, Project $project) {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Admin', 'Project Manager']) || ($user->hasRole('Project Manager') && $project->project_manager_id !== $user->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // Send email
        Log::info('Sending followup mail to: ' . $project->client_email);


        Mail::to($project->client_email)->send(new ProjectFollowupMail($project, $request->subject, $request->message));

        // Record follow-up
        ProjectFollowup::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'subject' => $request->subject,
            'message' => $request->message,
            'client_email' => $project->client_email,
            'sent_at' => Carbon::now(),
        ]);

        // Update last follow-up date
        $project->update(['last_followup_at' => Carbon::now()]);

        return response()->json(['success' => 'Follow-up sent successfully.']);
    }
}