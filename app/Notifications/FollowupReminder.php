<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class FollowupReminder extends Notification
{
    use Queueable;

    protected $project;
    protected $days;

    public function __construct($project, $days)
    {
        $this->project = $project;
        $this->days = $days;
    }

    public function via($notifiable)
    {
        return ['database']; // store in DB
    }

    public function toDatabase($notifiable)
    {
        return [
            'project_id'   => $this->project->id,
            'project_name' => $this->project->name_or_url, // Changed to name_or_url
            'days'         => $this->days,
            'message'      => "Follow-up overdue! Project '{$this->project->name_or_url}' is paused for {$this->days} days.",
        ];
    }
}