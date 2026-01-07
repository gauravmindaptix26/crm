<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectFollowupReminder extends Notification
{
    use Queueable;

    public $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Reminder: Follow-Up Due for Paused Project')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A follow-up is overdue for the paused project: ' . $this->project->name_or_url . '.')
            ->line('The last follow-up was on ' . ($this->project->last_followup_at ? $this->project->last_followup_at->format('d M Y') : 'never') . '.')
            ->action('View Project', url('/laravelcrm/crm/public/paused-projects'))
            ->line('Please send a follow-up to the client.');
    }

    public function toArray($notifiable)
    {
        return [
            'project_id' => $this->project->id,
            'project_name' => $this->project->name_or_url,
            'client_email' => $this->project->client_email,
            'message' => 'Follow-up overdue for project: ' . $this->project->name_or_url,
            'url' => '/laravelcrm/crm/public/paused-projects',
            'last_followup_at' => $this->project->last_followup_at ? $this->project->last_followup_at->toDateTimeString() : null,
        ];
    }
}