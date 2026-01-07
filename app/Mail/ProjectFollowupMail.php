<?php

namespace App\Mail;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjectFollowupMail extends Mailable
{
    use Queueable, SerializesModels;

    public $project;
    public $subjectText;
    public $bodyMessage;

    public function __construct(Project $project, string $subjectText, string $message)
    {
        $this->project = $project;
        $this->subjectText = $subjectText;
        $this->bodyMessage = $message; // ✅ assign correctly
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectText,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.project_followup',
            with: [  // ✅ pass variables to blade
                'project' => $this->project,
                'subjectText' => $this->subjectText,
                'bodyMessage' => $this->bodyMessage,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
