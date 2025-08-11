<?php

namespace App\Mail;

use App\Models\Assets;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfferAccepted extends Mailable
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
        $this->subject(config('app.name').' - '.__('emails.offers.accepted.subject'));
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
        return $this->markdown('emails.offers.accepted', [
            'firstname' => $this->user->firstname,
            'title' => $this->offer->auction->title,
            'lastOffer' => $this->offer->auction->lastOffer,
            'commission' => $this->offer->auction->commission,
            'offer' => $this->offer,
            'path' => $this->offer->auction->path,
            'product' => $this->offer->auction,
        ]);
    }
}
