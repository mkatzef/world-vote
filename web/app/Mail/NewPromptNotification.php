<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewPromptNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $prompt;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($prompt)
    {
        $this->prompt = $prompt;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.new_prompt_notification', ['prompt' => $this->prompt]);
    }
}
