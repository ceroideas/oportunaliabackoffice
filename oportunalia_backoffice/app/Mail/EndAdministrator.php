<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EndAdministrator extends Mailable
{
    use Queueable, SerializesModels;

    private $user;
    private $auction;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $auction)
    {
        $this->subject(config('app.name').' - '.__('emails.direct_sale.end.subject'));
        $this->from(env('MAIL_FROM_ADDRESS'), config('app.name'));
        $this->user = $user;
        $this->auction = $auction;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.direct-sale.end', [
            'firstname' => $this->user->firstname,
            'date' => date('d/m/Y', strtotime($this->auction->end_date)),
            'time' => date('H:i', strtotime($this->auction->end_date)),
            'title' => $this->auction->title,
            'product' => $this->auction,
        ]);
    }
}
