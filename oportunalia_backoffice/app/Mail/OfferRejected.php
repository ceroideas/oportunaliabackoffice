<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfferRejected extends Mailable
{
    use Queueable, SerializesModels;

    private $user;
    private $offer;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $offer)
    {
        $this->subject(config('app.name').' - '.__('emails.offers.rejected.subject'));
        $this->from(env('MAIL_FROM_ADDRESS'), config('app.name'));
        $this->user = $user;
        $this->offer = $offer;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.offers.rejected', [
            'firstname' => $this->user->firstname,
            'reference' => $this->offer->auction->id,
            'path' => $this->offer->auction->path,
            'title' => $this->offer->auction->title,
        ]);
    }
}
