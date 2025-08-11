<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FavEnd extends Mailable
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
        //$this->subject(config('app.name').' - '.__('emails.favs.end.subject'));
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

        $this->auction->auction_type_id == 1 ? $venta = 'subasta' : ($this->auction->auction_type_id == 2 ? $venta = 'venta directa' : $venta = 'cesión de remate');

        $this->subject(config('app.name').' - '.ucfirst($venta).__('emails.favs.end.subject'));

        $view = $this->auction->lastBidder ? 'emails.favs.end-win' : 'emails.favs.end';

        return $this->markdown($view, [
            'firstname' => $this->user->firstname,
            'date' => date('d/m/Y', strtotime($this->auction->start_date)),
            'time' => date('H:i', strtotime($this->auction->start_date)),
            'title' => $this->auction->title,
            'lastBid' => $this->auction->lastBid,
            'lastBidder' => $this->auction->lastBidder,
            'path' => $this->auction->path,
            'product' => $this->auction,
            'venta'=>$venta,
        ]);
    }
}
