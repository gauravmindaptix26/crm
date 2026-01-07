<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendBulkEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subjectLine;
    public $body;
    public $sender;

    /**
     * Create a new message instance.
     */
    public function __construct($subjectLine, $body, $sender)
    {
        $this->subjectLine = $subjectLine;
        $this->body = $body;
        $this->sender = $sender;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject($this->subjectLine)
                    ->view('emails.bulk-notification')
                    ->with([
                        'body' => $this->body,
                        'sender' => $this->sender,
                    ]);
    }
}
