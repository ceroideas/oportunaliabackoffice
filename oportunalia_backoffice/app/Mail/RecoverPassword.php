<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RecoverPassword extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var User
     */
    private $user;
    /**
     * @var string
     */
    private $url;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param string $url
     */
    public function __construct(User $user, string $url)
    {
        $this->subject(config('app.name').' - '.__('emails.recover.subject'));
        $this->from(env('MAIL_FROM_ADDRESS'), config('app.name'));
        $this->user = $user;
        $this->url = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.recover_password', [
            'firstname' => $this->user->firstname,
            'path' => $this->url,
        ]);
    }
}
