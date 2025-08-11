<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DepositInValid extends Mailable
{
    use Queueable, SerializesModels;

    private $user;
    private $deposit;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $deposit)
    {
        $this->subject(config('app.name').' - '.__('emails.deposits.invalid.subject'));
        $this->from(env('MAIL_FROM_ADDRESS'), config('app.name'));
        $this->user = $user;
        $this->deposit = $deposit;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.deposit.invalid', [
            'firstname' => $this->user->firstname,
            'reference' => $this->deposit->auction->id,
            'path' => url('/subastas'),
            'title' => $this->deposit->auction->title,
        ]);
    }
}
