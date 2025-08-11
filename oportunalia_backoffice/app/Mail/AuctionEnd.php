<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AuctionEnd extends Mailable
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
        $this->subject(config('app.name').' - '.__('emails.favs.end.subject'));
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
        $view = $this->auction->lastBidder ? 'emails.auctions.end-win' : 'emails.auctions.end';

        return $this->markdown($view, [
            'firstname' => $this->user->firstname,
            'date' => date('d/m/Y', strtotime($this->auction->start_date)),
            'time' => date('H:i', strtotime($this->auction->start_date)),
            'title' => $this->auction->title,
            'lastBid' => $this->auction->lastBid,
            'lastBidder' => $this->auction->lastBidder,
            'path' => $this->auction->path,
            'product' => $this->auction,
        ]);
    }
}
