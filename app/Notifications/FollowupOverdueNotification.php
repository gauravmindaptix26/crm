<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FollowupOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function via(object $notifiable): array
    {
        return ['database']; // Store in database for dashboard display
        // return ['database', 'mail']; // Uncomment to enable email notifications
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Follow-Up Overdue for Project: ' . $this->project->name_or_url)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('The project "' . $this->project->name_or_url . '" is overdue for a follow-up.')
            ->line('Last follow-up was on ' . ($this->project->last_followup_at ? $this->project->last_followup_at->format('d M Y') : 'N/A') . '.')
            ->action('View Paused Projects', route('projects.paused'))
            ->line('Please send a follow-up soon.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'project_id' => $this->project->id,
            'project_name' => $this->project->name_or_url,
            'last_followup_at' => $this->project->last_followup_at ? $this->project->last_followup_at->format('d M Y') : 'N/A',
            'message' => 'Follow-up overdue for project: ' . $this->project->name_or_url . '. Last follow-up was on ' . ($this->project->last_followup_at ? $this->project->last_followup_at->format('d M Y') : 'N/A') . '.',
        ];
    }
}