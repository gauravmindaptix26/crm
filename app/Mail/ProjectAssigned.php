<?php

namespace App\Mail;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProjectAssigned extends Mailable
{
    use Queueable, SerializesModels;

    public $project;
    public $recipient;

    /**
     * Create a new message instance.
     *
     * @param Project $project
     * @param User $recipient
     * @return void
     */
    public function __construct(Project $project, User $recipient)
    {
        $this->project = $project;
        $this->recipient = $recipient;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('A New Project Has Been Assigned To You - ' . $this->project->name_or_url)
                    ->view('emails.project_assigned')
                    ->with([
                        'project' => $this->project,
                        'recipientName' => $this->recipient->name,
                    ]);
    }
}
?>