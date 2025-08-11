<?php

namespace App\Mail;

use App\Models\Assets;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WinBid extends Mailable
{
    use Queueable, SerializesModels;

    private $user;
    private $bid;
    private $auction;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $bid, $auction)
    {
        $this->subject(config('app.name').' - '.__('emails.bid.win.subject'));
        $this->from(env('MAIL_FROM_ADDRESS'), config('app.name'));
        $this->user = $user;
        $this->bid = $bid;
        $this->auction = $auction;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.bid.win', [
            'firstname' => $this->user->firstname,
            'title' => $this->auction->title,
            'lastBid' => $this->auction->lastBid,
            'commission' => $this->auction->commission,
            'bid' => $this->bid,
            'path' => $this->auction->path,
            'product' => $this->auction,
        ]);
    }
}
