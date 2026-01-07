<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\EmailNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendBulkEmail;
use Illuminate\Support\Facades\Log;


class NotificationController extends Controller
{
    public function showForm()
    {
        $loggedInUser = auth()->user();

    if (!$loggedInUser->hasRole('Admin') && !$loggedInUser->hasRole('HR')) {
        return redirect()->route('dashboard')->with('error', 'You are not authorized to access this page.');
    }
        return view('notifications.send-email');
    }

    public function sendEmail(Request $request)
    {

      // ✅ Only allow HR or Admin
     $request->validate([
            'email_subject' => 'required|string|max:255',
            'email_content' => 'required|string',
        ]);

        $users = User::whereNotNull('email')->pluck('email')->toArray();
        $senderName = auth()->user()->name;

        foreach ($users as $email) {
            Log::info("Sending email to $email");
            Mail::to($email)->send(new SendBulkEmail(
                $request->email_subject,
                $request->email_content,
                $senderName
            ));
        }

        EmailNotification::create([
            'subject' => $request->email_subject,
            'content' => $request->email_content,
            'sent_by' => $senderName,
            'recipients' => implode(', ', $users),
        ]);

        return back()->with('success', 'Email sent to all employees.');
    }
}
