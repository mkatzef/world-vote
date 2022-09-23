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
        $auth_code = strval(base_convert(random_int(0, 1e7), 10, 36));
        $this->prompt->update(['auth_code' => $auth_code]);
        return $this->view('emails.new_prompt_notification', [
          'prompt' => $this->prompt,
          'auth_code' => $auth_code
        ]);
    }
}
